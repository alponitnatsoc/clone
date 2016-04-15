<?php

namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;

trait PayrollMethodsTrait
{

    use NoveltyTypeMethodsTrait,
        LiquidationMethodsTrait;

    /**
     * Obtener informacion de nomina
     * 
     * @param Employer $idEmployer ID del empleador
     * @return boolean
     */
    public function getInfoPayroll(Employer $employer, $payrollToPay = false)
    {
        /* @var $employerHasEmployees \Doctrine\Common\Collections\Collection */
        $employerHasEmployees = $employer->getEmployerHasEmployees();
        $employeesData = array();

        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployee = $employerHasEmployees->first();
        do {
            $employeesData = $this->getInfoEmployee($employerHasEmployee, $payrollToPay, $employeesData);
        } while ($employerHasEmployee = $employerHasEmployees->next());
        if (!empty($employeesData)) {
            return $employeesData;
        }
        return false;
    }

    /**
     * 
     * @param EmployerHasEmployee $employerHasEmployee
     */
    private function getInfoEmployee(EmployerHasEmployee $employerHasEmployee, $payrollToPay, $employeesData)
    {
        if ($employerHasEmployee->getState() === 3) {
            $contracts = $employerHasEmployee->getContracts();
            /* @var $contract Contract */
            foreach ($contracts as $contract) {
                if ($contract->getState() > 0) {
                    /* @var $payroll Payroll */
                    $payroll = $contract->getActivePayroll();
                    if (empty($payrollToPay)) {
                        $payrollToPay2 = array($payroll->getIdPayroll());
                    } else {
                        $payrollToPay2 = $payrollToPay;
                    }
                    if (in_array($payroll->getIdPayroll(), $payrollToPay2)) {
//PENDIENTE - validar que payroll corresponda al periodo y fecha actual, para no pagar facturas pasadas ni futuras
                        /* @var $purchaseOrdersDescription PersistentCollection */
                        //$purchaseOrdersDescription = $payroll->getPurchaseOrdersDescription();
                        //$purchaseOrdersStatus = $purchaseOrdersDescription->isEmpty();
                        $purchaseOrdersStatus = true;

                        if ($purchaseOrdersStatus) {
                            $employeesData[$payroll->getIdPayroll()] = array();
                            $employeesData[$payroll->getIdPayroll()]['idPayroll'] = $payroll->getIdPayroll();
                            $employeesData[$payroll->getIdPayroll()]['payroll'] = $payroll;
                            $employeesData[$payroll->getIdPayroll()]['employerHasEmployee'] = $employerHasEmployee;
                            $employeesData[$payroll->getIdPayroll()]['payMethod'] = $contract->getPayMethodPayMethod();

                            $detailNomina = $this->getInfoNominaSQL($employerHasEmployee);
                            dump('SQL');
                            dump($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['detailNomina'] = $detailNomina;

                            $totalLiquidation = $this->totalLiquidation($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['totalLiquidation'] = $totalLiquidation;

                            $salary = $this->getSalary($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['salary'] = $salary;

                            $totalAportes = $this->getTotalAportes($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['totalAportes'] = $totalAportes;


                            if ($payroll->getPeriod() == 4) {
                                $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA($employerHasEmployee);
                            } else {
                                $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA(false);
                                ;
                            }
                        }
                    }
                }
            }
        } else {
            dump('employerHasEmployee inactivo');
            dump($employerHasEmployee);
        }
        return $employeesData;
    }

    /**
     * 
     * @param int $idUser id del usuario a buscar
     * @return User|null
     */
    private function getUserById($idUser)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
                        array('id' => $idUser)
        );
    }

    /**
     * 
     * @param int $idEmployer id del usuario a buscar
     * @return User|null
     */
    private function getEmployerById($idEmployer)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\Employer')->findOneBy(
                        array('idEmployer' => $idEmployer)
        );
    }

    private function getSalary($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '1') {
                    return (int) ceil($value['NOMI_VALOR_LOCAL']);
                }
            }
        }
        return false;
    }

    private function getTotalAportes($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            $aporteSalud = $aportePension = 0;
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3010') {
                    $aporteSalud = $value['NOMI_VALOR_LOCAL'];
                } elseif (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3020') {
                    $aportePension = $value["NOMI_VALOR_LOCAL"];
                }
            }
            return array(
                'total' => ceil($aporteSalud + $aportePension),
                'salud' => ceil($aporteSalud),
                'pension' => ceil($aportePension)
            );
        }
        return false;
    }

    private function getTotalPILA($employerHasEmployee)
    {
        $total = $pension = $salud = $arl = $parafiscales = 0;
        if ($employerHasEmployee) {
            $pila = $this->getInfoPilaSQL($employerHasEmployee);
            dump('PILA');
            dump($pila);
            if ($pila) {
                foreach ($pila as $key => $value) {
                    $total += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                    $total +=isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    if ($value['TENT_CODIGO'] == 'AFP') {
                        $pension +=isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $pension +=isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'ARP') {
                        $arl +=isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $arl +=isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'EPS' || $value['TENT_CODIGO'] == 'ARS') {
                        $salud +=isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $salud +=isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'PARAFISCAL') {
                        $parafiscales +=isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $parafiscales +=isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    }
                }
            }
        }
        return array(
            'total' => (int) ceil($total),
            'pension' => (int) ceil($pension),
            'salud' => (int) ceil($salud),
            'arl' => (int) ceil($arl),
            'parafiscales' => (int) ceil($parafiscales)
        );
    }

    /**
     * Trae la informacion del empleado desde el ws de nomina de SQL
     * 
     * @param EmployerHasEmployee $employerHasEmployee
     * @return type
     */
    private function getInfoNominaSQL(EmployerHasEmployee $employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getIdEmployerHasEmployee();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollMethodRest:getGeneralPayrolls', array(
            'employeeId' => $employeeId,
            'period' => null,
            'month' => null,
            'year' => null
                ), array('_format' => 'json')
        );

        return json_decode($generalPayroll->getContent(), true);
    }

    /**
     * Trae la informacion del empleado desde el ws de nomina de SQL
     * 
     * @param EmployerHasEmployee $employerHasEmployee
     * @return type
     */
    private function getInfoPilaSQL(EmployerHasEmployee $employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getIdEmployerHasEmployee();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getExternalEntitiesLiquidation', array(
            'employeeId' => $employeeId
                ), array('_format' => 'json')
        );

        return json_decode($generalPayroll->getContent(), true);
    }

}
