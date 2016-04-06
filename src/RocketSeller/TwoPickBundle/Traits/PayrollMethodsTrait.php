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

                            $employeesData[$payroll->getIdPayroll()]['detailNomina'] = $detailNomina;

                            $totalLiquidation = $this->totalLiquidation($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['totalLiquidation'] = $totalLiquidation;

                            $salary = $this->getSalary($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['salary'] = $salary;

                            $totalAportes = $this->getTotalAportes($detailNomina);
                            $employeesData[$payroll->getIdPayroll()]['totalAportes'] = $totalAportes;

                            //$pila = $this->getInfoPilaSQL($employerHasEmployee);
                            //dump($pila);
                            //die;

                            if ($payroll->getPeriod() == 4) {
                                $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA($salary);
                            } else {
                                $employeesData[$payroll->getIdPayroll()]['PILA'] = $this->getTotalPILA(0);
                                ;
                            }
                        }
                    }
                }
            }
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

    private function getTotalPILA($salary)
    {
        return array(
            'total' => (int) ceil(($salary * 0.12) + ceil($salary * 0.085) + ceil($salary * 0.00348) + ceil($salary * 0.09)),
            'pension' => (int) ceil($salary * 0.12),
            'salud' => (int) ceil($salary * 0.085),
            'arl' => (int) ceil($salary * 0.00348),
            'parafiscales' => (int) ceil($salary * 0.09)
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

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
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
