<?php

namespace RocketSeller\TwoPickBundle\Traits;

use DateTime;
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
use RocketSeller\TwoPickBundle\Entity\ProcedureType;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Doctrine\ORM\EntityManager;

trait SubscriptionMethodsTrait
{

    use EmployeeMethodsTrait;

    protected function findProductByNumDays($productos, $days)
    {
        if ($days > 0 && $days <= 10) {
            $key = 'PS1';
        } elseif ($days >= 11 && $days <= 19) {
            $key = 'PS2';
        } elseif ($days >= 20) {
            $key = 'PS3';
        } else {
            $key = 'PS3';
        }
        foreach ($productos as $value) {
            if ($value->getSimpleName() == $key) {
                return $value;
            }
        }
    }

    /**
     *
     * @param User $user Empleador del cual se buscaran los empleados
     * @param boolean $activeEmployee buscar empleados solo activos = true, default=false para buscarlos todos
     * @return array
     */
    protected function getSubscriptionCost($user, $activeEmployee = false)
    {
        $idEmployer = $user->getPersonPerson()->getEmployer();
        $config = $this->getConfigData();
        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployee = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
            ->findByEmployerEmployer($idEmployer);

        $productos = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Product')
            ->findBy(array('simpleName' => array('PS1', 'PS2', 'PS3')));

        $employees = array();
        $total_sin_descuentos = $total_con_descuentos = $valor_descuento_3er = $valor_descuento_isRefered = $valor_descuento_haveRefered = $contInactivos = 0;
        $descuento_3er = isset($config['D3E']) ? $config['D3E'] : 0.1;
        $descuento_isRefered = isset($config['DIR']) ? $config['DIR'] : 0.2;
        $descuento_haveRefered = isset($config['DHR']) ? $config['DHR'] : 0.2;
        foreach ($employerHasEmployee as $keyEmployee => $employee) {

            if ($activeEmployee) {
                if ($employee->getState() == 0) {
                    continue;
                }
            }

            if ($employee->getState() == 0) {
                $contInactivos++;
            }

            $contracts = $employee->getContractByState(true);

            foreach ($contracts as $keyContract => $contract) {
                $employees[$keyEmployee]['contrato'] = $contract;
                $employees[$keyEmployee]['employee'] = $employee;
                $employees[$keyEmployee]['product']['object'] = $this->findProductByNumDays($productos, $contract->getWorkableDaysMonth());
                $tax = ($employees[$keyEmployee]['product']['object']->getTaxTax() != null) ? $employees[$keyEmployee]['product']['object']->getTaxTax()->getValue() : 0;
                $employees[$keyEmployee]['product']['price'] = ceil($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax));
                $employees[$keyEmployee]['product']['price_con_descuentos'] = ceil($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax));
                //$employees[$keyEmployee]['product']['neto'] = ($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax));
                //$employees[$keyEmployee]['product']['ceil'] = ceil($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax));
                //$employees[$keyEmployee]['product']['floor'] = floor($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax));
                //$employees[$keyEmployee]['product']['round_up'] = round($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax), 0, PHP_ROUND_HALF_UP);
                //$employees[$keyEmployee]['product']['round_down'] = round($employees[$keyEmployee]['product']['object']->getPrice() * (1 + $tax), 0, PHP_ROUND_HALF_DOWN);
                $total_sin_descuentos += $employee->getState() ? $employees[$keyEmployee]['product']['price'] : 0;
                break;
            }
        }
        if (count($employees) >= 3) {
            $valor_descuento_3er = ceil($total_sin_descuentos * $descuento_3er);
            $employees = $this->updateProductPrice($employees, $descuento_3er);
        }
        $userIsRefered = $this->userIsRefered($user);
        if ($userIsRefered) {
            $valor_descuento_isRefered = ceil($total_sin_descuentos * $descuento_isRefered);
            $employees = $this->updateProductPrice($employees, $descuento_isRefered);
        }
        $userHaveValidRefered = $this->userHaveValidRefered($user);
        if ($userHaveValidRefered) {
            $valor_descuento_haveRefered = ceil($total_sin_descuentos * (count($userHaveValidRefered) * $descuento_haveRefered));
            $employees = $this->updateProductPrice($employees, $descuento_haveRefered);
        }

        if (($valor_descuento_3er + $valor_descuento_isRefered + $valor_descuento_haveRefered) > $total_sin_descuentos) {
            $total_con_descuentos = 0;
        } else {
            $total_con_descuentos = $total_sin_descuentos - ($valor_descuento_3er + $valor_descuento_isRefered + $valor_descuento_haveRefered); //descuentos antes de iva
        }
        return array(
            'employees' => $employees,
            'productos' => $productos,
            'total_sin_descuentos' => $total_sin_descuentos,
            'total_con_descuentos' => $total_con_descuentos,
            'descuento_3er' => array('percent' => $descuento_3er, 'value' => $valor_descuento_3er),
            'descuento_isRefered' => array('percent' => $descuento_isRefered, 'value' => $valor_descuento_isRefered, 'object' => $userIsRefered),
            'descuento_haveRefered' => array('percent' => $descuento_haveRefered, 'value' => $valor_descuento_haveRefered, 'object' => $userHaveValidRefered)
        );
    }

    protected function updateProductPrice($employees, $descuentoPercent)
    {
        foreach ($employees as $key => $employe) {
            $employees[$key]['product']['price_con_descuentos'] = ceil($employe['product']['price_con_descuentos'] / (1 + $descuentoPercent));
        }
        return $employees;
    }

    /**
     * @param EmployerHasEmployee $eHE
     * @return bool
     */
    protected function addEmployeeToSQL(EmployerHasEmployee $eHE)
    {
        //Employee creation
        $request = $this->container->get('request');
        $employer = $eHE->getEmployerEmployer();
        $em = $this->getDoctrine()->getManager();
        if ($eHE->getState() == 2 && (!$eHE->getExistentSQL())) {
            $contracts = $eHE->getContracts();
            $actContract = null;
            /** @var Contract $c */
            foreach ($contracts as $c) {
                if ($c->getState() == 1) {
                    $actContract = $c;
                    break;
                }
            }
//                 $liquidationType=$actContract->getPayMethodPayMethod()->getFrequencyFrequency()->getPayrollCode();
            $liquidationType = $actContract->getFrequencyFrequency()->getPayrollCode();
            $endDate = $actContract->getEndDate();
            $employee = $eHE->getEmployeeEmployee();
            $employeePerson = $employee->getPersonPerson();
            if ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC") {
                $payroll_type = 4;
                $value = $actContract->getSalary();
                $wokableDaysWeek = 6;
            } else {
                $payroll_type = 6;
                $value = $actContract->getSalary() / $actContract->getWorkableDaysMonth();
                $wokableDaysWeek = $actContract->getWorkableDaysMonth() / 4;
            }
            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                "last_name" => $employeePerson->getLastName1(),
                "first_name" => $employeePerson->getNames(),
                "document_type" => $employeePerson->getDocumentType(),
                "document" => $employeePerson->getDocument(),
                "gender" => $employeePerson->getGender(),
                "birth_date" => $employeePerson->getBirthDate()->format("d-m-Y"),
                "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                "contract_number" => $actContract->getIdContract(),
                "worked_hours_day" => 8,
                "payment_method" => "EFE",
                "liquidation_type" => $liquidationType,
                "contract_type" => $actContract->getContractTypeContractType()->getPayrollCode(),
                "transport_aux" => $actContract->getTransportAid() == 1 ? "N" : "S",
                "worked_days_week" => $wokableDaysWeek,
                "society" => $employer->getIdSqlSociety(),
                "payroll_type" => $payroll_type,
            ));
            if ($endDate != null) {
                $request->request->add(array(
                    "last_contract_end_date" => $endDate->format("d-m-Y")
                ));
            }
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployee', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }
            $eHE->setExistentSQL(1);
            $em->persist($eHE);
            $em->flush();
            if ($actContract->getHolidayDebt() != null) {
                $request->setMethod("POST");
                $request->request->add(array(
                    "employee_id" => $eHE->getIdEmployerHasEmployee(),
                    "pending_days" => $actContract->getHolidayDebt(),
                ));
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddPendingVacationDays', array('_format' => 'json'));
                if ($insertionAnswer->getStatusCode() != 200) {
                    return false;
                }
                $startDate = $actContract->getStartDate();
                $nowDate = new DateTime();
                $monthsDiff = $startDate->diff($nowDate)->m + ($startDate->diff($nowDate)->y * 12);
                $dateToStart = new DateTime();
                if ($monthsDiff > 12) {
                    $monthsDiff = 12;
                    $dateToStart = new DateTime(date("Y-m-d", strtotime($nowDate->format("Y-m-d") . " -" . $monthsDiff . " months")));

                } else {
                    $dateToStart = new DateTime($startDate->format("Y-m-d"));
                }
                //solve this month
                if ($actContract->getFrequencyFrequency()->getPayrollCode() == "Q") {
                    if ($dateToStart->format("d") < 15) {
                        $dateToCheck = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-15");
                        if ($nowDate < $dateToCheck) {
                            $dateToStart = $dateToCheck;
                        } else {
                            $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
                            $salaryPerPeriod = $actContract->getSalary() / 2;
                            $request->setMethod("POST");
                            $request->request->add(array(
                                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                "units" => $unitsPerPeriod,
                                "value" => $salaryPerPeriod,
                                "year" => $dateToCheck->format("Y"),
                                "month" => $dateToCheck->format("m"),
                                "period" => "2",
                            ));
                            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                            if ($insertionAnswer->getStatusCode() != 200) {
                                return false;
                            }
                            $dateToCheck = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-" . $dateToStart->format("t"));
                            if ($nowDate < $dateToCheck) {
                                $dateToStart = $dateToCheck;
                            } else {
                                $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
                                $salaryPerPeriod = $actContract->getSalary() / 2;
                                $request->setMethod("POST");
                                $request->request->add(array(
                                    "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                    "units" => $unitsPerPeriod,
                                    "value" => $salaryPerPeriod,
                                    "year" => $dateToCheck->format("Y"),
                                    "month" => $dateToCheck->format("m"),
                                    "period" => "4",
                                ));
                                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                                if ($insertionAnswer->getStatusCode() != 200) {
                                    return false;
                                }
                                $dateToStart = new DateTime($dateToCheck->format("Y") . "-" . (intval($dateToCheck->format("m")) + 1) . "-1");
                            }

                        }
                    } else {
                        $dateToCheck = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-" . $dateToStart->format("t"));
                        if ($nowDate < $dateToCheck) {
                            $dateToStart = $dateToCheck;
                        } else {
                            $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
                            $salaryPerPeriod = $actContract->getSalary() / 2;
                            $request->setMethod("POST");
                            $request->request->add(array(
                                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                "units" => $unitsPerPeriod,
                                "value" => $salaryPerPeriod,
                                "year" => $dateToCheck->format("Y"),
                                "month" => $dateToCheck->format("m"),
                                "period" => "4",
                            ));
                            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                            if ($insertionAnswer->getStatusCode() != 200) {
                                return false;
                            }
                            $dateToStart = new DateTime($dateToCheck->format("Y") . "-" . (intval($dateToCheck->format("m")) + 1) . "-1");
                        }
                    }
                } else {
                    $dateToCheck = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-" . $dateToStart->format("t"));
                    if ($nowDate < $dateToCheck) {
                        $dateToStart = $dateToCheck;
                    } else {
                        $unitsPerPeriod = $actContract->getWorkableDaysMonth();
                        $salaryPerPeriod = $actContract->getSalary();
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "employee_id" => $eHE->getIdEmployerHasEmployee(),
                            "units" => $unitsPerPeriod,
                            "value" => $salaryPerPeriod,
                            "year" => $dateToCheck->format("Y"),
                            "month" => $dateToCheck->format("m"),
                            "period" => "4",
                        ));
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                        if ($insertionAnswer->getStatusCode() != 200) {
                            return false;
                        }
                        $dateToStart = new DateTime($dateToCheck->format("Y") . "-" . (intval($dateToCheck->format("m")) + 1) . "-1");
                    }
                }
                //get to today date
                while (true) {
                    if ($actContract->getFrequencyFrequency()->getPayrollCode() == "Q") {
                        $dateToSend = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-15");
                        if ($nowDate < $dateToSend) {
                            break;
                        }
                        $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
                        $salaryPerPeriod = $actContract->getSalary() / 2;
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "employee_id" => $eHE->getIdEmployerHasEmployee(),
                            "units" => $unitsPerPeriod,
                            "value" => $salaryPerPeriod,
                            "year" => $dateToSend->format("Y"),
                            "month" => $dateToSend->format("m"),
                            "period" => "2",
                        ));
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                        if ($insertionAnswer->getStatusCode() != 200) {
                            return false;
                        }
                    } else {
                        $unitsPerPeriod = $actContract->getWorkableDaysMonth();
                        $salaryPerPeriod = $actContract->getSalary();
                    }
                    $dateToSend = new DateTime($dateToStart->format("Y") . "-" . $dateToStart->format("m") . "-" . $dateToStart->format("t"));
                    if ($nowDate < $dateToSend) {
                        break;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "units" => $unitsPerPeriod,
                        "value" => $salaryPerPeriod,
                        "year" => $dateToSend->format("Y"),
                        "month" => $dateToSend->format("m"),
                        "period" => "4",
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                    $dateToStart = new DateTime($dateToSend->format("Y") . "-" . (intval($dateToSend->format("m"))) . "-1");
                    $dateToStart = new DateTime(date ( 'Y-m-d' , strtotime ( '+1 month' , strtotime ( $dateToStart->format("Y-m-d") ) ) ));
                }
            }
            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                "value" => $value,
                "date_change" => $actContract->getStartDate()->format("d-m-Y"),
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddFixedConcepts', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }
            //ADDING THE ENTITIES
            $emEntities = $employee->getEntities();
            /** @var EmployeeHasEntity $eEntity */
            foreach ($emEntities as $eEntity) {
                $entity = $eEntity->getEntityEntity();
                $eType = $entity->getEntityTypeEntityType();
                if ($eType->getPayrollCode() == "EPS" || $eType->getPayrollCode() == "ARS") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $eType->getPayrollCode() == "EPS" ? "2" : "1", //EPS ITS ALWAYS FAMILIAR SO NEVER CHANGE THIS
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
                if ($eType->getPayrollCode() == "AFP") {
                    if ($entity->getPayrollCode() == 0) {
                        $coverage = $entity->getName() == "Pensionado" ? 2 : 0; //2 si es pensionado o 0 si no amporta
                    } else {
                        $coverage = 1;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $coverage, //the relation coverage from SQL
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        echo "Cago insertar entidad AFP " . $eHE->getIdEmployerHasEmployee() . " SC" . $insertionAnswer->getStatusCode();
                        die();
                        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
                        return $view;
                    }
                }
                if ($eType->getPayrollCode() == "FCES") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => "FCES",
                        "coverage_code" => 1, //DONT change this is forever and ever
                        "entity_code" => intval($entity->getPayrollCode()),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
            }
            $emEntities = $employer->getEntities();
            $flag = false;
            /** @var EmployerHasEntity $eEntity */
            foreach ($emEntities as $eEntity) {
                $entity = $eEntity->getEntityEntity();
                $eType = $entity->getEntityTypeEntityType();
                if ($eType->getPayrollCode() == "ARP") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $actContract->getPositionPosition()->getPayrollCoverageCode(),
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
                if ($eType->getPayrollCode() == "PARAFISCAL") {
                    if (!$flag) {
                        $flag = true;
                    } else {
                        continue;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => "1", //Forever and ever don't change this
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
            }
            return true;

        }
        return false;

    }

    protected function addToSQL(User $user)
    {
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();
        //SQL Comsumpsion
        //Create Society
        $em = $this->getDoctrine()->getManager();
        $dateToday = new DateTime();
        $dateToday->setDate(1970, 01, 01); //TODO DO NOT ERASE THIS SHIT
        $request = $this->container->get('request');
        if ($employer->getIdSqlSociety() == null) {
            $request->setMethod("POST");
            $request->request->add(array(
                "society_nit" => $person->getDocument(),
                "society_name" => $person->getNames(),
                "society_start_date" => $dateToday->format("d-m-Y"),
                "society_mail" => $user->getEmail(),
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddSociety', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }
        }

        $request->setMethod("GET");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getSociety', array("societyNit" => $person->getDocument()), array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return false;
        }
        //$idSQL=$employer->getIdSqlSociety();
        $idSQL = json_decode($insertionAnswer->getContent(), true)["COD_SOCIEDAD"];
        $employer->setIdSqlSociety($idSQL);
        $em->persist($employer);
        $em->flush();
        //return $view->setStatusCode(201);

        return true;
    }


    protected function addToNovo(User $user)
    {
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();

        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "name" => $person->getNames(),
            "lastName" => $person->getLastName1() . " " . $person->getLastName2(),
            "year" => $person->getBirthDate()->format("Y"),
            "month" => $person->getBirthDate()->format("m"),
            "day" => $person->getBirthDate()->format("d"),
            "phone" => $person->getPhones()->get(0)->getPhoneNumber(),
            "email" => $user->getEmail()
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClient', array('_format' => 'json'));
        //dump($insertionAnswer);
        //echo "Status Code Employer: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode();

        if ($insertionAnswer->getStatusCode() == 406 || $insertionAnswer->getStatusCode() == 201) {
            $eHEes = $employer->getEmployerHasEmployees();
            //dump($eHEes);
            /** @var EmployerHasEmployee $employeeC */
            foreach ($eHEes as $employeeC) {
                //dump($employeeC);
                if ($employeeC->getState() > 0) {
                    //check if it exist

                    $contracts = $employeeC->getContracts();
                    /** @var Contract $cont */
                    $contract = null;
                    foreach ($contracts as $cont) {
                        if ($cont->getState() == 1) {
                            $contract = $cont;
                        }
                    }

                    /* @var $payMC PayMethod */
                    $payMC = $contract->getPayMethodPayMethod();

                    /* @var $payType PayType */
                    $payType = $payMC->getPayTypePayType();

                    if ($payType->getPayrollCode() != 'EFE') {
                        $paymentMethodId = $payMC->getAccountTypeAccountType();
                        if ($paymentMethodId) {
                            $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? 4 :
                                $payMC->getAccountTypeAccountType()->getName() == "Corriente" ? 5 : 6;
                        }
                        $paymentMethodAN = $payMC->getAccountNumber() == null ? $payMC->getCellPhone() : $payMC->getAccountNumber();
                        $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "documentType" => $employeePerson->getDocumentType(),
                            "beneficiaryId" => $employeePerson->getDocument(),
                            "documentNumber" => $person->getDocument(),
                            "name" => $employeePerson->getNames(),
                            "lastName" => $employeePerson->getLastName1() . " " . $employeePerson->getLastName2(),
                            "yearBirth" => $employeePerson->getBirthDate()->format("Y"),
                            "monthBirth" => $employeePerson->getBirthDate()->format("m"),
                            "dayBirth" => $employeePerson->getBirthDate()->format("d"),
                            "phone" => $employeePerson->getPhones()->get(0)->getPhoneNumber(),
                            "email" => $employeePerson->getEmail() == null ? $employeePerson->getDocumentType() . $person->getDocument() .
                                "@" . $employeePerson->getNames() . ".com" : $employeePerson->getEmail(),
                            "companyId" => $person->getDocument(), //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                            "companyBranch" => "0", //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                            "paymentMethodId" => $paymentMethodId,
                            "paymentAccountNumber" => $paymentMethodAN,
                            "paymentBankNumber" => $payMC->getBankBank()->getNovopaymentCode(),
                            "paymentType" => $payMC->getAccountTypeAccountType() ? $payMC->getAccountTypeAccountType()->getName() : null,
                        ));
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postBeneficiary', array('_format' => 'json'));
                        if (!($insertionAnswer->getStatusCode() == 201 || $insertionAnswer->getStatusCode() == 406)) {
                            $this->addFlash('error', $insertionAnswer->getContent());
                            return false;
                        }
                        //dump($insertionAnswer);
                        //echo "Status Code Employee: " . $employeePerson->getNames() . " -> " . $insertionAnswer->getStatusCode() . " content" . $insertionAnswer->getContent();
                    }
                }
            }
        } else {
            $this->addFlash('error', $insertionAnswer->getContent());
            return false;
        }
        return true;
    }

    protected function addToHighTech(User $user)
    {
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();
        if($employer->getIdHighTech()!=null){
            return true;
        }
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "name" => $person->getNames(),
            "firstLastName" => $person->getLastName1(),
            "secondLastName" => $person->getLastName2(),
            "documentExpeditionDate" => $person->getDocumentExpeditionDate() ? $person->getDocumentExpeditionDate()->format("Y-m-d") : "",
            "civilState" => $person->getCivilStatus(),
            "address" => $person->getMainAddress(),
            "phone" => $person->getPhones()->get(0)->getPhoneNumber(),
            "municipio" => $person->getCity()->getName(),
            "department" => $person->getDepartment()->getName(),
            "mail" => $user->getEmail()
        ));

        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterNaturalPerson', array('_format' => 'json'));
        //dump($insertionAnswer);
        //echo "Status Code Employer: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode();

        if ($insertionAnswer->getStatusCode() == 404 || $insertionAnswer->getStatusCode() == 200) {
            if ($insertionAnswer->getStatusCode() == 200) {
                $idHighTech = json_decode($insertionAnswer->getContent(), true)["cuentaGSC"];
                $employer->setIdHighTech($idHighTech);
                $em->persist($employer);
                $em->flush();
            }
            $eHEes = $employer->getEmployerHasEmployees();
            //dump($eHEes);
            /** @var EmployerHasEmployee $employeeC */
            foreach ($eHEes as $employeeC) {
                //dump($employeeC);
                if ($employeeC->getState() > 0) {
                    //check if it exist

                    $contracts = $employeeC->getContracts();
                    /** @var Contract $cont */
                    $contract = null;
                    foreach ($contracts as $cont) {
                        if ($cont->getState() == 1) {
                            $contract = $cont;
                        }
                    }

                    /* @var $payMC PayMethod */
                    $payMC = $contract->getPayMethodPayMethod();

                    /* @var $payType PayType */
                    $payType = $payMC->getPayTypePayType();

                    if ($payType->getPayrollCode() != 'EFE') {
                        $paymentMethodId = $payMC->getAccountTypeAccountType();
                        if ($paymentMethodId) {
                            if ($payType->getName() == "Daviplata") {
                                $paymentMethodId = "DP";
                            } else {
                                $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? "AH" : ($payMC->getAccountTypeAccountType()->getName() == "Corriente" ? "CC" : "EN");
                            }
                        }
                        $paymentMethodAN = $payMC->getAccountNumber() == null ? $payMC->getCellPhone() : $payMC->getAccountNumber();
                        $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "accountNumber" => $employer->getIdHighTech(),
                            "documentEmployer" => $employer->getPersonPerson()->getDocument(),
                            "documentTypeEmployer" => $employer->getPersonPerson()->getDocumentType(),
                            "documentTypeEmployee" => $employeePerson->getDocumentType(),
                            "documentEmployee" => $employeePerson->getDocument(),
                            "employeeName" => $employeePerson->getFullName(),
                            "employeeAddress" => $employeePerson->getMainAddress(),
                            "employeeCellphone" => $employeePerson->getPhones()->get(0)->getPhoneNumber(),
                            "employeeMail" => $employeePerson->getEmail() == null ? $employeePerson->getDocumentType() . $person->getDocument() .
                                "@" . $employeePerson->getNames() . ".com" : $employeePerson->getEmail(),
                            "employeeAccountType" => $paymentMethodId,
                            "employeeAccountNumber" => $paymentMethodAN,
                            "employeeBankCode" => $payMC->getBankBank()->getHightechCode() ?: 23,
                        ));
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterBeneficiary', array('_format' => 'json'));
                        if (!($insertionAnswer->getStatusCode() == 200)) {
                            $this->addFlash('error', $insertionAnswer->getContent());

                            return false;
                        }
                    }
                }
            }
        } else {
            $this->addFlash('error', $insertionAnswer->getContent());
            return false;
        }
        return true;
    }

    protected function redimirReferidos($data)
    {
        $em = $this->getDoctrine()->getManager();
        if ($data['descuento_isRefered']['object']) {
            /* @var $refered Referred */
            $refered = $data['descuento_isRefered']['object'];
            $refered->setStatus(1);
            $em->persist($refered);
        }
        if ($data['descuento_haveRefered']['object']) {
            /* @var $refered Referred */
            foreach ($data['descuento_haveRefered']['object'] as $key => $refered) {
                $refered->setStatus(1);
                $em->persist($refered);
            }
        }
        $em->flush();
    }

    protected function getDaysSince($sinceDate, $toDate)
    {
        $dDiff = true;
        if ($sinceDate !== null && $toDate !== null) {
            $dStart = new \DateTime(date_format($sinceDate, 'Y-m-d'));
            $dEnd = new \DateTime(date_format($toDate, 'Y-m-d'));
            $dDiff = $dStart->diff($dEnd);
        }
        return $dDiff;
    }

    protected function getDaysSinceCreated()
    {
        /* @var $user User */
        $user = $this->getUser();
        $dateCreated = $user->getDateCreated();
        $dStart = new \DateTime(date_format($dateCreated, 'Y-m-d'));
        $dEnd = new \DateTime(date('Y-m-d'));
        $dDiff = $dStart->diff($dEnd);
        return $dDiff->days;
    }

    protected function getDaysSinceLastPay()
    {
        /* @var $user User */
        $user = $this->getUser();
        $lastPayDate = $user->getLastPayDate();
        $dStart = new \DateTime(date_format($lastPayDate, 'Y-m-d'));
        $dEnd = new \DateTime(date('Y-m-d'));
        $dDiff = $dStart->diff($dEnd);
        return $dDiff->days;
    }

    protected function isFree()
    {
        /* @var $user User */
        $user = $this->getUser();

        $status = $user->getStatus();
        if ($status == 2 || $status == 3) {

            $em = $this->getDoctrine()->getManager();

            /* @var $person Person */
            $person = $user->getPersonPerson();

            /* @var $ehE EmployerHasEmployee */
            $ehE = $person->getEmployer()->getEmployerHasEmployees();

            /* @var $data EmployerHasEmployee */
            $data = $ehE->first();
            do {
                if ($data->getIsFree() == 0 || $data->getIsFree() != ($status - 1)) {
                    $data->setIsFree($status - 1);
                    $em->persist($data);
                }
            } while ($data = $ehE->next());
            $em->flush();
            return true;
        }
        return false;
    }

    /**
     * Buscar si el usuario fue referido por alguien
     * @param User $user
     * @return Referred
     */
    protected function userIsRefered(User $user = null)
    {
        /* @var $isRefered Referred */
        $isRefered = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Referred')
            ->findOneBy(array('referredUserId' => $user != null ? $user : $this->getUser(), 'status' => 0));

        if ($isRefered && $isRefered->getUserId()->getPaymentState() > 0) {
            return $isRefered;
        }
        return null;
    }

    /**
     * Buscar referidos que tiene el usuario y que ya tengan subscripcion
     * @param User $user
     * @return array
     */
    protected function userHaveValidRefered(User $user = null)
    {
        /* @var $haveRefered Referred */
        $haveRefered = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Referred')
            ->findBy(array('userId' => $user != null ? $user : $this->getUser(), 'status' => 0));
        $responce = array();
        foreach ($haveRefered as $key => $referedUser) {
            if ($referedUser->getReferredUserId()->getPaymentState() > 0) {
                array_push($responce, $referedUser);
            }
        }
        return $responce;
    }

    protected function getConfigData()
    {
        #$configRepo = new Config();
        $configRepo = $this->getdoctrine()->getRepository("RocketSellerTwoPickBundle:Config");
        $configDataTmp = $configRepo->findAll();
        $configData = array();
        if ($configDataTmp) {
            foreach ($configDataTmp as $key => $value) {
                $configData[$value->getName()] = $value->getValue();
            }
        }
        return $configData;
    }

    public function sendEmailPaySuccess($idUser, $idPurchaseOrder)
    {
        /* @var $user User */
        $user = $this->getUserById($idUser);
        $path = null;
        if ($user) {
            $response = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', array(
                'ref' => 'factura', 'id' => $idPurchaseOrder, 'type' => 'pdf', 'attach' => 1
            ));

            $response = json_decode($response->getContent(), true);

            if (isset($response['name-path'])) {
                $path = $this->get('kernel')->getRootDir() . "/../web/public/docs/tmp/invoices/" . $response['name-path'];
//D:\drive\Multiplica\symplifica\app/../web/public/docs/tmp/invoices/8061777-123.pdf
            } else {
                $path = null;
            }
            $toEmail = $user->getEmail();

            $fromEmail = "servicioalcliente@symplifica.com";

            $tsm = $this->get('symplifica.mailer.twig_swift');

            $response = $tsm->sendEmail($user, "RocketSellerTwoPickBundle:Subscription:paySuccess.txt.twig", $fromEmail, $toEmail, $path);
        }
    }

    /**
     *
     * @param int $idUser id del usuario a buscar
     * @return User|null
     */
    protected function getUserById($idUser)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
            array('id' => $idUser)
        );
    }

    protected function createPurchaceOrder(User $user, $paymethodId, $methodId = false)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$methodId) {
            $methodId = $this->getMethodId($user->getPersonPerson()->getDocument());
        }
        $data = $this->getSubscriptionCost($user, true);

        $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
        /** @var $purchaseOrdersStatus PurchaseOrdersStatus */
        $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('name' => 'Pendiente'));

        $purchaseOrder = new PurchaseOrders();
        $purchaseOrder->setIdUser($user);
        $purchaseOrder->setName('Pago Membresia');
        $total = ($user->getIsFree() > 0) ? 0 : $data['total_con_descuentos'];
        $purchaseOrder->setValue($total);
        $purchaseOrder->setPurchaseOrdersStatus($purchaseOrdersStatus);
        $purchaseOrder->setPayMethodId($methodId);
        $purchaseOrder->setProviderId($paymethodId == 'novo' ? 0 : 1);

        foreach ($data['employees'] as $key => $employee) {
            $purchaseOrderDescription = new PurchaseOrdersDescription();
            $purchaseOrderDescription->setDescription("Pago Membresia");
            $purchaseOrderDescription->setPurchaseOrders($purchaseOrder);
            $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);
            $purchaseOrderDescription->setValue($total == 0 ? 0 : $employee['product']['price_con_descuentos']);
            $purchaseOrderDescription->setProductProduct($employee['product']['object']);
            $purchaseOrder->addPurchaseOrderDescription($purchaseOrderDescription);
        }

        $em->persist($purchaseOrderDescription);
        $em->persist($purchaseOrder);
        $em->flush(); //para obtener el id que se debe enviar a novopay

        $responce = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array("idPurchaseOrder" => $purchaseOrder->getIdPurchaseOrders()), array('_format' => 'json'));
        //dump($responce);
        //die;
        $data2 = json_decode($responce->getContent(), true);
        if ($responce->getStatusCode() == Response::HTTP_OK) {
            $this->addFlash('success', $data2);

            $this->sendEmailPaySuccess($user->getId(), $purchaseOrder->getIdPurchaseOrders());

            $user->setStatus(2);
            $user->setPaymentState(1);
            $user->setDayToPay(date('d'));
            $user->setLastPayDate(date_create(date('Y-m-d H:m:s')));

            if ($user->getIsFree() > 0) {
                $date = new \DateTime();
                $date->add(new \DateInterval('P1M'));
                $startDate = $date->format('Y-m-d');
                $user->setIsFreeTo($date);
            }

            $em->persist($user);
            $em->flush();

            $this->redimirReferidos($data);
            return true;
        }
        $this->addFlash('error', $responce->getContent());
        return false;
    }

    protected function procesosLuegoPagoExitoso(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user->setStatus(2);
        $user->setPaymentState(1);
        $user->setDayToPay(date('d'));
        $user->setLastPayDate(date_create(date('Y-m-d H:m:s')));

        if ($user->getIsFree() > 0) {
            $date = new \DateTime();
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');
            $user->setIsFreeTo($date);
        }
        $this->crearTramites($user);
        $this->validateDocuments($user);
        $this->addToSQL($user);
        $em->persist($user);
        $em->flush();
        return true;
    }

    protected function crearTramites(User $user)
    {
        /* @var $ProcedureType ProcedureType */
        $ProcedureType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:ProcedureType')->findOneBy(array('name' => 'Registro empleador y empleados'));
        $procedure = $this->forward('RocketSellerTwoPickBundle:Procedure:procedure', array(
            'employerId' => $user->getPersonPerson()->getEmployer()->getIdEmployer(),
            'idProcedureType' => $ProcedureType->getIdProcedureType()
        ), array('_format' => 'json'));
        return $procedure;
    }

    protected function getMethodId($documentNumber)
    {
        $response = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
        $listPaymentMethods = json_decode($response->getContent(), true);
        return isset($listPaymentMethods['payment-methods'][0]['method-id']) ? ($listPaymentMethods['payment-methods'][0]['method-id']) : false;
    }

    public function procedureAction($employerId, $idProcedureType)
    {
        $em = $this->getDoctrine()->getManager();
        $em2 = $this->getDoctrine()->getManager();
        $employerSearch = $this->loadClassById($employerId, "Employer");
        $procedureType = $this->loadClassById($idProcedureType, "ProcedureType");
        $employerHasEmployees = $employerSearch->getEmployerHasEmployees();
        if ($procedureType->getName() == "Registro empleador y empleados") {
            $procedure = new RealProcedure();
            $procedure->setCreatedAt(new \DateTime());
            $procedure->setProcedureTypeProcedureType($procedureType);
            $procedure->setEmployerEmployer($employerSearch);
            $em2->persist($procedure);

            $action = new Action();
            $action->setStatus('Nuevo');
            $action->setRealProcedureRealProcedure($procedure);
            $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'Revisar registro'), "ActionType"));
            $action->setPersonPerson($employerSearch->getPersonPerson());
            $em->persist($action);
            $em->flush();

            $action = new Action();
            $action->setStatus('Nuevo');
            $action->setRealProcedureRealProcedure($procedure);
            $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'Llamar cliente'), "ActionType"));
            $action->setPersonPerson($employerSearch->getPersonPerson());
            $em->persist($action);
            $em->flush();
            foreach ($employerSearch->getEntities() as $entities) {

                $action = new Action();
                $action->setStatus('Nuevo');
                $action->setRealProcedureRealProcedure($procedure);
                $action->setEntityEntity($entities->getEntityEntity());
                $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'Llamar entidad'), "ActionType"));
                $action->setPersonPerson($employerSearch->getPersonPerson());
                $em->persist($action);
                $em->flush();

                $action = new Action();
                $action->setStatus('Nuevo');
                $action->setRealProcedureRealProcedure($procedure);
                $action->setEntityEntity($entities->getEntityEntity());
                $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'inscripcion'), "ActionType"));
                $action->setPersonPerson($employerSearch->getPersonPerson());
                $em->persist($action);
                $em->flush();
                $procedure->addAction($action);
            }
            foreach ($employerHasEmployees as $employerHasEmployee) {
                //agregar la validacion del estado del employee
                $action = new Action();
                $action->setStatus('Nuevo');
                $action->setRealProcedureRealProcedure($procedure);
                //$action->setEntityEntity($entities->getEntityEntity());
                $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'Revisar registro'), "ActionType"));
                $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
                $em->persist($action);
                $em->flush();
                $procedure->addAction($action);
                foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $EmployeeHasEntity) {
                    $action = new Action();
                    $action->setStatus('Nuevo');
                    $action->setRealProcedureRealProcedure($procedure);
                    $action->setEntityEntity($EmployeeHasEntity->getEntityEntity());
                    $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'Llamar entidad'), "ActionType"));
                    $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
                    $em->persist($action);
                    $em->flush();
                    $procedure->addAction($action);

                    $action = new Action();
                    $action->setStatus('Nuevo');
                    $action->setRealProcedureRealProcedure($procedure);
                    $action->setEntityEntity($EmployeeHasEntity->getEntityEntity());
                    $action->setActionTypeActionType($this->loadClassByArray(array('name' => 'inscripcion'), "ActionType"));
                    $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
                    $em->persist($action);
                    $em->flush();
                    $procedure->addAction($action);
                }
            }
            $em2->flush();
        } else {
            $em2->remove($procedure);
            $em2->flush();
        }
        return true;
    }

}
