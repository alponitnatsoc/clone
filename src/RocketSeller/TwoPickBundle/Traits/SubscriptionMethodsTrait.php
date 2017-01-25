<?php

namespace RocketSeller\TwoPickBundle\Traits;

use DateTime;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\CalculatorConstraints;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\ProcedureType;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\Entity\TransactionType;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

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
        $userEmployer = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')
                        ->findOneBy(array('personPerson' => $employer->getPersonPerson()));
        $em = $this->getDoctrine()->getManager();
        if ($eHE->getState() > 2 && (!$eHE->getExistentSQL())) {
            $minimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
            $contracts = $eHE->getContracts();
            $actContract = $eHE->getActiveContract();
//                 $liquidationType=$actContract->getPayMethodPayMethod()->getFrequencyFrequency()->getPayrollCode();
            $liquidationType = $actContract->getFrequencyFrequency()->getPayrollCode();
            $endDate = $actContract->getEndDate();
            $employee = $eHE->getEmployeeEmployee();
            $employeePerson = $employee->getPersonPerson();
            $calType=-1;
            if ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC") {
                $payroll_type = 4;
                $value = $actContract->getSalary();
                $worksSats = $actContract->getWorksSaturday();
                if($worksSats==1){
                    $wokableDaysWeek = 6;
                    $calType=2;
                }
                else{
                    $wokableDaysWeek = 5;
                    $calType=1;
                }
            } elseif($actContract->getTimeCommitmentTimeCommitment()->getCode() == "XD") {
                $payroll_type = 6;
                $value = $actContract->getSalary() / $actContract->getWorkableDaysMonth();
                $wokableDaysWeek = $actContract->getWorkableDaysMonth() / 4;
            } elseif($actContract->getTimeCommitmentTimeCommitment()->getCode() == "DS") {
                $payroll_type = 7;
                $value = $actContract->getSalary() / $actContract->getWorkableDaysMonth();
                $wokableDaysWeek = $actContract->getWorkableDaysMonth() / 4;
            }else{return false;}
            if($employeePerson->getDocumentType() == "PASAPORTE"){
            $employeeDocType = "PA";
            }else{
            $employeeDocType = $employeePerson->getDocumentType();
            }

            if($actContract->getTransportAid()==1){
                $transportAid = 'N';
            }elseif($actContract->getSalary() >= $minimumSalary*2){
                $transportAid = 'N';
            }else{
                $transportAid = 'S';
            }
            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                "last_name" => $employeePerson->getLastName1(),
                "first_name" => $employeePerson->getNames(),
                "document_type" => $employeeDocType,
                "document" => $employeePerson->getDocument(),
                "gender" => $employeePerson->getGender(),
                "birth_date" => $employeePerson->getBirthDate()->format("d-m-Y"),
                "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                "contract_number" => $actContract->getIdContract(),
                "worked_hours_day" => 8,
                "payment_method" => "EFE",
                "liquidation_type" => $liquidationType,
                "contract_type" => $actContract->getContractTypeContractType()->getPayrollCode(),
                "transport_aux" => $transportAid,
                "worked_days_week" => $wokableDaysWeek,
                "society" => $employer->getIdSqlSociety(),
                "payroll_type" => $payroll_type,
            ));

            if ($endDate != null) {
                $request->request->add(array(
                    "last_contract_end_date" => $endDate->format("d-m-Y")
                ));
            }
            if ($calType != -1) {
                $request->request->add(array(
                    "cal_type" => $calType
                ));
            }
            $today = new DateTime();
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployee', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                if($eHE->getDateTryToRegisterToSQL()!=null){
                    $log = new Log($this->getUser(),'EmployerHasEmployee','DateTryToRegisterToSQL',$eHE->getIdEmployerHasEmployee(),$eHE->getDateTryToRegisterToSQL()->format('d-m-Y H:i:s'),$today->format('d-m-Y H:i:s'),'Add to SQL fail');
                }else{
                    $log = new Log($this->getUser(),'EmployerHasEmployee','DateTryToRegisterToSQL',$eHE->getIdEmployerHasEmployee(),'',$today->format('d-m-Y H:i:s'),'Add to SQL fail');
                }
                $eHE->setDateTryToRegisterToSQL($today);
                $em->persist($eHE);
                $em->persist($log);
                $em->flush();
                return false;
            }
            $log = new Log($this->getUser(),'EmployerHasEmployee','DateRegisterToSQL',$eHE->getIdEmployerHasEmployee(),'',$today->format('d-m-Y H:i:s'),'Add to SQL success');
            $eHE->setDateRegisterToSQL($today);
            $eHE->setExistentSQL(1);
            $em->persist($eHE);
            $em->persist($log);
            $em->flush();

            //send push notification
            $message = "¡Bienvenido a Symplifica! Usa la plataforma";
            $title = "Symplifica";
            $longMessage = "¡Bienvenido a Symplifica! Ya puedes empezar a usar nuestra herramienta y la APP para gestionar el día a día de tus empleados.";

            $request = new Request();
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $userEmployer->getId(),
                "title" => $title,
                "message" => $message,
                "longMessage" => $longMessage
            ));
            $pushNotificationService = $this->get('app.symplifica_push_notification');
            $result = $pushNotificationService->postPushNotificationAction($request);
            // push notification sent

            if ($actContract->getHolidayDebt() != null) { //If the employee has no vacations remaining or is new, we send 0, otherwise the value
	            $request->setMethod("POST");
	            $request->request->add(array(
		            "employee_id" => $eHE->getIdEmployerHasEmployee(),
		            "pending_days" => $actContract->getHolidayDebt(),
	            ));
	            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddPendingVacationDays', array('request' => $request ),  array('_format' => 'json'));
	            if ($insertionAnswer->getStatusCode() != 200) {
		            return false;
	            }
            }

            $startDate = new DateTime($actContract->getStartDate()->format("Y")."-".$actContract->getStartDate()->format("m")."-".$actContract->getStartDate()->format("d"));
            $nowDate = new DateTime();

	          //First, check if the employee starts its contract on the actual period or in a next one.

	          if($actContract->getFrequencyFrequency()->getPayrollCode() == "Q"){
	          	if($nowDate->format("d") <= 15 ){
	          		$periodDate = new DateTime($nowDate->format("Y") . "-" . $nowDate->format("m") . "-01");
		          }
		          else{
			          $periodDate = new DateTime($nowDate->format("Y") . "-" . $nowDate->format("m") . "-16");
		          }
	          }
	          elseif ($actContract->getFrequencyFrequency()->getPayrollCode() == "M"){
	          	$periodDate = new DateTime($nowDate->format("Y") . "-" . $nowDate->format("m") . "-01");
	          }

		        //Special case, if the contract starts on a 31, the day is ignored
		        if($startDate->format("d") == 31){
			        $startDate->modify("+1 day");
		        }

	          if($startDate < $periodDate){ //Else there is no need for historical values
	          	//This means that the contract is old so it need historical values
		          $monthsDiff = $startDate->diff($nowDate)->m + ($startDate->diff($nowDate)->y * 12);

		          //If the contract started less than a year ago, then the initial month needs to have the correct value.
		          if($monthsDiff <= 12){
		          	$startMonthSpecialTreatment = true;
		          }
		          else{
			          $startMonthSpecialTreatment = false;
		          }

		          while($startDate < $periodDate ){ //As soon the startDate reach the period it means we are ready
		          	if($startMonthSpecialTreatment){
				          //Need to calculate how many days did the person work on this point
				          if($actContract->getFrequencyFrequency()->getPayrollCode() == "Q"){
                              if($actContract->getTimeCommitmentTimeCommitment()->getCode() == "XD" || $actContract->getTimeCommitmentTimeCommitment()->getCode() == "DS"){
											if($startDate->format('d') <= 15){
												$relativeWorkedDays = 15 - $startDate->format("d") + 1;
												$proportionalPeriod = $relativeWorkedDays / 15;
												$unitsPerPeriod = floor($proportionalPeriod * ($actContract->getWorkableDaysMonth() / 2));
												if($unitsPerPeriod == 0){ //If the contract starts that period it need to work at least one
													$unitsPerPeriod = 1;
												}
												$salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

                                                $request->setMethod("POST");
                                                $request->request->add(array(
                                                    "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                                    "units" => $unitsPerPeriod,
                                                    "value" => floor($salaryPerPeriod * $unitsPerPeriod),
                                                    "year" => $startDate->format("Y"),
                                                    "month" => $startDate->format("m"),
                                                    "period" => "2",
                                                ));

                                                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request), array('_format' => 'json'));
                                                if ($insertionAnswer->getStatusCode() != 200) {
                                                    return false;
                                                }

                                        //The first period of the month is done at this point, now into the second half of the month, if needed

                                        if($startDate->diff($periodDate)->m > 0 || $startDate->format("m") != $periodDate->format("m")) {
                                            $request->setMethod("POST");
                                            $request->request->add(array(
                                                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                                "units" => $actContract->getWorkableDaysMonth() / 2,
                                                "value" => floor($actContract->getSalary() / 2),
                                                "year" => $startDate->format("Y"),
                                                "month" => $startDate->format("m"),
                                                "period" => "4",
                                            ));

                                            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
                                            if ($insertionAnswer->getStatusCode() != 200) {
                                                return false;
                                            }
                                        }
                                            //The second period of the month is done at this point, now the date changes again
                                            $startDate->modify("+1 month");
                                    }
                                    else{ //startDate is 16 or more
                                        $relativeWorkedDays = 30 - $startDate->format("d") + 1;
                                        $proportionalPeriod = $relativeWorkedDays / 15;
                                        $unitsPerPeriod = floor($proportionalPeriod * ($actContract->getWorkableDaysMonth() / 2));
                                        if($unitsPerPeriod == 0){ //If the contract starts that period it need to work at least one
                                            $unitsPerPeriod = 1;
                                        }
                                        $salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

                                        $request->setMethod("POST");
                                        $request->request->add(array(
                                            "employee_id" => $eHE->getIdEmployerHasEmployee(),
                                            "units" => $unitsPerPeriod,
                                            "value" => floor($salaryPerPeriod * $unitsPerPeriod),
                                            "year" => $startDate->format("Y"),
                                            "month" => $startDate->format("m"),
                                            "period" => "4",
                                        ));

                                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
                                        if ($insertionAnswer->getStatusCode() != 200) {
                                            return false;
                                        }

                                        //The period of the month is done at this point, now the date changes again
                                        $startDate->modify("+1 month");
                                    }
					          }
					          elseif ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC"){
						          if($startDate->format('d') <= 15){
							          $relativeWorkedDays = 15 - $startDate->format("d") + 1;
							          $unitsPerPeriod = $relativeWorkedDays;

							          $salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

							          $request->setMethod("POST");
							          $request->request->add(array(
								          "employee_id" => $eHE->getIdEmployerHasEmployee(),
								          "units" => $unitsPerPeriod,
								          "value" => floor($salaryPerPeriod * $unitsPerPeriod),
								          "year" => $startDate->format("Y"),
								          "month" => $startDate->format("m"),
								          "period" => "2",
							          ));

							          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
							          if ($insertionAnswer->getStatusCode() != 200) {
								          return false;
							          }

							          //The first period of the month is done at this point, now into the second half of the month, if needed

							          if($startDate->diff($periodDate)->m > 0 || $startDate->format("m") != $periodDate->format("m")){
								          $request->setMethod("POST");
								          $request->request->add(array(
									          "employee_id" => $eHE->getIdEmployerHasEmployee(),
									          "units" => $actContract->getWorkableDaysMonth() / 2,
									          "value" => floor($actContract->getSalary() / 2),
									          "year" => $startDate->format("Y"),
									          "month" => $startDate->format("m"),
									          "period" => "4",
								          ));

								          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
								          if ($insertionAnswer->getStatusCode() != 200) {
									          return false;
								          }
								          //The second period of the month is done at this point, now the date changes again
							          }
							          $startDate->modify("+1 month");
						          }
						          else{ //startDate is 16 or more
							          $relativeWorkedDays = 30 - $startDate->format("d") + 1;
							          $unitsPerPeriod = $relativeWorkedDays;

							          $salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

							          $request->setMethod("POST");
							          $request->request->add(array(
								          "employee_id" => $eHE->getIdEmployerHasEmployee(),
								          "units" => $unitsPerPeriod,
								          "value" => floor($salaryPerPeriod * $unitsPerPeriod),
								          "year" => $startDate->format("Y"),
								          "month" => $startDate->format("m"),
								          "period" => "4",
							          ));

							          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
							          if ($insertionAnswer->getStatusCode() != 200) {
								          return false;
							          }

							          //The period of the month is done at this point, now the date changes again
							          $startDate->modify("+1 month");
						          }
					          }
				          }
				          elseif ($actContract->getFrequencyFrequency()->getPayrollCode() == "M"){
                              if($actContract->getTimeCommitmentTimeCommitment()->getCode() == "XD" || $actContract->getTimeCommitmentTimeCommitment()->getCode() == "DS"){
						          $relativeWorkedDays = 30 - $startDate->format("d") + 1;
						          $proportionalPeriod = $relativeWorkedDays / 30;
						          $unitsPerPeriod = floor($proportionalPeriod * ($actContract->getWorkableDaysMonth() ));
						          if($unitsPerPeriod == 0){ //If the contract starts that period it need to work at least one
							          $unitsPerPeriod = 1;
						          }
						          $salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod * $unitsPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "4",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The period of the month is done at this point, now the date changes again
						          $startDate->modify("+1 month");
					          }
					          elseif ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC"){
						          $relativeWorkedDays = 30 - $startDate->format("d") + 1;
						          $unitsPerPeriod = $relativeWorkedDays;

						          $salaryPerPeriod = $actContract->getSalary() / $actContract->getWorkableDaysMonth();

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod * $unitsPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "4",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The period of the month is done at this point, now the date changes again
						          $startDate->modify("+1 month");
					          }
				          }

		          		$startMonthSpecialTreatment = false;
			          } //End of special treatment, now into the full month historical
			          else{
				          if($actContract->getFrequencyFrequency()->getPayrollCode() == "Q"){
					          if($actContract->getTimeCommitmentTimeCommitment()->getCode() == "XD" || $actContract->getTimeCommitmentTimeCommitment()->getCode() == "DS"){

						          $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
						          $salaryPerPeriod = $actContract->getSalary() / 2;

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "2",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The first period of the month is done at this point, now into the second half of the month, if needed

						          if($startDate->diff($periodDate)->m > 0 || $startDate->format("m") != $periodDate->format("m")){
							          $request->setMethod("POST");
							          $request->request->add(array(
								          "employee_id" => $eHE->getIdEmployerHasEmployee(),
								          "units" => $unitsPerPeriod,
								          "value" => floor($salaryPerPeriod),
								          "year" => $startDate->format("Y"),
								          "month" => $startDate->format("m"),
								          "period" => "4",
							          ));

							          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
							          if ($insertionAnswer->getStatusCode() != 200) {
								          return false;
							          }
							          //The second period of the month is done at this point, now the date changes again
						          }
						          $startDate->modify("+1 month");
					          }
					          elseif ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC"){
						          $unitsPerPeriod = $actContract->getWorkableDaysMonth() / 2;
						          $salaryPerPeriod = $actContract->getSalary() / 2;

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "2",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The first period of the month is done at this point, now into the second half of the month, if needed

						          if($startDate->diff($periodDate)->m > 0 || $startDate->format("m") != $periodDate->format("m")){
							          $request->setMethod("POST");
							          $request->request->add(array(
								          "employee_id" => $eHE->getIdEmployerHasEmployee(),
								          "units" => $unitsPerPeriod,
								          "value" => floor($salaryPerPeriod),
								          "year" => $startDate->format("Y"),
								          "month" => $startDate->format("m"),
								          "period" => "4",
							          ));

							          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
							          if ($insertionAnswer->getStatusCode() != 200) {
								          return false;
							          }
							          //The second period of the month is done at this point, now the date changes again
						          }
						          $startDate->modify("+1 month");
					          }
				          }
				          elseif ($actContract->getFrequencyFrequency()->getPayrollCode() == "M"){
                              if($actContract->getTimeCommitmentTimeCommitment()->getCode() == "XD" || $actContract->getTimeCommitmentTimeCommitment()->getCode() == "DS"){
						          $unitsPerPeriod = $actContract->getWorkableDaysMonth();
						          $salaryPerPeriod = $actContract->getSalary();

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "4",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The period of the month is done at this point, now the date changes again
						          $startDate->modify("+1 month");
					          }
					          elseif ($actContract->getTimeCommitmentTimeCommitment()->getCode() == "TC"){
						          $unitsPerPeriod = $actContract->getWorkableDaysMonth();
						          $salaryPerPeriod = $actContract->getSalary();

						          $request->setMethod("POST");
						          $request->request->add(array(
							          "employee_id" => $eHE->getIdEmployerHasEmployee(),
							          "units" => $unitsPerPeriod,
							          "value" => floor($salaryPerPeriod),
							          "year" => $startDate->format("Y"),
							          "month" => $startDate->format("m"),
							          "period" => "4",
						          ));

						          $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddCumulatives', array('request' => $request ), array('_format' => 'json'));
						          if ($insertionAnswer->getStatusCode() != 200) {
							          return false;
						          }

						          //The period of the month is done at this point, now the date changes again
						          $startDate->modify("+1 month");
					          }
				          }
			          }
		          }
	          }
            //end of historical

            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id" => $eHE->getIdEmployerHasEmployee(),
                "value" => $value,
                "date_change" => $actContract->getStartDate()->format("d-m-Y"),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddFixedConcepts', array('request' => $request ), array('_format' => 'json'));
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
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('request' => $request ), array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
                if ($eType->getPayrollCode() == "AFP") {
                    if ($entity->getPayrollCode() == 0) {
                        $coverage = 2; //2 si es pensionado o  si no amporta
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
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('request' => $request ), array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
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
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('request' => $request ), array('_format' => 'json'));
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
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('request' => $request ), array('_format' => 'json'));
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
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('request' => $request ), array('_format' => 'json'));
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
        /** @var UtilsController $utils */
        $utils = $this->get("app.symplifica_utils");
        if($person->getDocumentType() == "PASAPORTE"){
            $docType="PA";
            $nitToAdd = "PA" . $person->getDocument();
        }
        else{
            $docType=$person->getDocumentType();
            $nitToAdd = $person->getDocumentType().$person->getDocument();
        }
        if ($employer->getIdSqlSociety() == null) {

            $request->setMethod("POST");
            $request->request->add(array(
                "society_nit" => $nitToAdd,
                "society_name" => $person->getNames(),
                "society_start_date" => $dateToday->format("d-m-Y"),
                "society_mail" => $utils->generateRandomEmail(),
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddSociety', array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return false;
            }
        }

        $request->setMethod("GET");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getSociety', array("societyNit" => $docType.$person->getDocument()), array('_format' => 'json'));
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
        if ($insertionAnswer->getStatusCode() == 406 || $insertionAnswer->getStatusCode() == 201) {
            /*$request->setMethod("POST");
            $request->request->add(array(
                "documentType" => "NIT",
                "beneficiaryId" => "900862831",
                "documentNumber" => $person->getDocument(),
                "name" => "Symplifica Account",
                "lastName" => "NovoPayment" ,
                "yearBirth" => "1970",
                "monthBirth" => "01",
                "dayBirth" => "01",
                "phone" => "3508330000",
                "email" => "backofficesymplifica@gmail.com",
                "companyId" => $person->getDocument(), //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                "companyBranch" => "0", //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                "paymentMethodId" => 5,//this is corriente for symplifica account
                "paymentAccountNumber" => "0560006269993173",//symplifica novo account number
                "paymentBankNumber" => "39",//davivienda bank code
                "paymentType" => "Corriente",
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postBeneficiary', array('_format' => 'json'));
            if (!($insertionAnswer->getStatusCode() == 201 || $insertionAnswer->getStatusCode() == 406)) {
                return false;
            }*/
        }else{
            return false;
        }
        $employer->setExistentNovo(1);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($employer);
        $em->flush();
        return $this->addToHighTech($user);


        //dump($insertionAnswer);
        //echo "Status Code Employer: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode();

//        if ($insertionAnswer->getStatusCode() == 406 || $insertionAnswer->getStatusCode() == 201) {
//            $eHEes = $employer->getEmployerHasEmployees();
//            //dump($eHEes);
//            /** @var EmployerHasEmployee $employeeC */
//            foreach ($eHEes as $employeeC) {
//                //dump($employeeC);
//                if ($employeeC->getState() > 0) {
//                    //check if it exist
//
//                    $contracts = $employeeC->getContracts();
//                    /** @var Contract $cont */
//                    $contract = null;
//                    foreach ($contracts as $cont) {
//                        if ($cont->getState() == 1) {
//                            $contract = $cont;
//                        }
//                    }
//
//                    /* @var $payMC PayMethod */
//                    $payMC = $contract->getPayMethodPayMethod();
//
//                    /* @var $payType PayType */
//                    $payType = $payMC->getPayTypePayType();
//
//                    if ($payType->getPayrollCode() != 'EFE') {
//                        $paymentMethodId = $payMC->getAccountTypeAccountType();
//                        if ($paymentMethodId) {
//                            $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? 4 :
//                                $payMC->getAccountTypeAccountType()->getName() == "Corriente" ? 5 : 6;
//                        }
//                        $paymentMethodAN = $payMC->getAccountNumber() == null ? $payMC->getCellPhone() : $payMC->getAccountNumber();
//                        $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
//                        $request->setMethod("POST");
//                        $request->request->add(array(
//                            "documentType" => $employeePerson->getDocumentType(),
//                            "beneficiaryId" => $employeePerson->getDocument(),
//                            "documentNumber" => $person->getDocument(),
//                            "name" => $employeePerson->getNames(),
//                            "lastName" => $employeePerson->getLastName1() . " " . $employeePerson->getLastName2(),
//                            "yearBirth" => $employeePerson->getBirthDate()->format("Y"),
//                            "monthBirth" => $employeePerson->getBirthDate()->format("m"),
//                            "dayBirth" => $employeePerson->getBirthDate()->format("d"),
//                            "phone" => $employeePerson->getPhones()->get(0)->getPhoneNumber(),
//                            "email" => $employeePerson->getEmail() == null ? $employeePerson->getDocumentType() . $person->getDocument() .
//                                "@" . $employeePerson->getNames() . ".com" : $employeePerson->getEmail(),
//                            "companyId" => $person->getDocument(), //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
//                            "companyBranch" => "0", //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
//                            "paymentMethodId" => $paymentMethodId,
//                            "paymentAccountNumber" => $paymentMethodAN,
//                            "paymentBankNumber" => $payMC->getBankBank()->getNovopaymentCode(),
//                            "paymentType" => $payMC->getAccountTypeAccountType() ? $payMC->getAccountTypeAccountType()->getName() : null,
//                        ));
//                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postBeneficiary', array('_format' => 'json'));
//                        if (!($insertionAnswer->getStatusCode() == 201 || $insertionAnswer->getStatusCode() == 406)) {
//                            $this->addFlash('error', $insertionAnswer->getContent());
//                            return false;
//                        }
//                        //dump($insertionAnswer);
//                        //echo "Status Code Employee: " . $employeePerson->getNames() . " -> " . $insertionAnswer->getStatusCode() . " content" . $insertionAnswer->getContent();
//                    }
//                }
//            }
//        } else {
//            $this->addFlash('error', $insertionAnswer->getContent());
//            return false;
//        }
        return true;
    }

    protected function addToHighTech(User $user)
    {
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();
        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();
        if ($employer->getIdHighTech() == null) {
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
        } else {
            $insertionAnswer = View::create();
            $insertionAnswer->setStatusCode(404);
        }


        if ($insertionAnswer->getStatusCode() == 404 || $insertionAnswer->getStatusCode() == 200) {
            if ($insertionAnswer->getStatusCode() == 200) {
                $idHighTech = json_decode($insertionAnswer->getContent(), true)["cuentaGSC"];
                $employer->setIdHighTech($idHighTech);
                $em->persist($employer);
                $em->flush();

            }

            //add employer to pila operator only if is already registered on Hightech
            if($employer->getExistentPila() == NULL && $employer->getIdHighTech() != NULL){

	            $request->setMethod("POST");
	            $request->request->add(array(
		            "GSCAccount" => $employer->getIdHighTech()
	            ));

	            $transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'IPil'));

	            $transaction = new Transaction();
	            $transaction->setTransactionType($transactionType);

	            $pilaRegistrationAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterEmployerToPilaOperator', array('_format' => 'json'));

	            if($pilaRegistrationAnswer->getStatusCode() == 200){
		            //Received succesfully
		            $radicatedNumber = json_decode($pilaRegistrationAnswer->getContent(), true)["numeroRadicado"];
		            $transaction->setRadicatedNumber($radicatedNumber);
		            $purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-InsEnv'));
	            }
	            else{
	            	//If some kind of error
		            $purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-ErrSer'));
	            }

	            $transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
	            $em->persist($transaction);
	            $em->flush();
	            $employer->setExistentPila($transaction->getIdTransaction());
	            $employer->addTransaction($transaction);

	            $em->persist($employer);
	            $em->flush();

            }
            $eHEes = $employer->getEmployerHasEmployees();
            //dump($eHEes);
            /** @var EmployerHasEmployee $employeeC */
            foreach ($eHEes as $employeeC) {
                //dump($employeeC);
                if ($employeeC->getState() > 1 && $employeeC->getExistentHighTec() != 1) {
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
                            if ($payType->getName() == "Daviplata*") {
                                $paymentMethodId = "DP";
                            } else {
                                $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? "AH" : ($payMC->getAccountTypeAccountType()->getName() == "Corriente" ? "CC" : "EN");
                            }
                        }
                        //TODO change this as the same of the dispersion
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
                        if ($insertionAnswer->getStatusCode() == 200) {
	                        $employeeC->setExistentHighTec(1);
                        }
                        $em->persist($employeeC);
                        $em->flush();
                    }
                }
            }
        } else {
            $this->addFlash('error', $insertionAnswer->getContent());
            return false;
        }
        return true;
    }

    protected function addEmployeeToHighTech(EmployerHasEmployee $employeeC)
    {
        /** @var User $user */
        $user = $this->getUser();
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();
        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();

        //dump($employeeC);
        if ($employeeC->getState() >= 3) {
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
                    if ($payType->getName() == "Daviplata*") {
                        $paymentMethodId = "DP";
                    } else {
                        $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? "AH" : ($payMC->getAccountTypeAccountType()->getName() == "Corriente" ? "CC" : "EN");
                    }
                }
                //TODO change this as the same of the dispersion
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
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterBeneficiary', array('request' => $request), array('_format' => 'json'));
                if (!($insertionAnswer->getStatusCode() == 200)) {
                    $this->addFlash('error', $insertionAnswer->getContent());

                    return false;
                }
                $employeeC->setExistentHighTec(1);
                $em->persist($employeeC);
                $em->flush();
                return true;
            }
        }
        return false;
    }

    protected function removeEmployeeToHighTech(EmployerHasEmployee $employeeC)
    {
        /** @var User $user */
        $user = $this->getUser();
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();
        $request = $this->container->get('request');
        $em = $this->getDoctrine()->getManager();
        $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
        $request->setMethod("POST");
        $request->request->add(array(
            "accountNumber" => $employer->getIdHighTech(),
            "documentEmployee" => $employeePerson->getDocument(),
            "documentTypeEmployee" => $employeePerson->getDocumentType(),
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:deleteRemoveBeneficiary', array('request' => $request), array('_format' => 'json'));
        if (!($insertionAnswer->getStatusCode() == 200)) {
            $this->addFlash('error', $insertionAnswer->getContent());
            return false;
        }
        $employeeC->setExistentHighTec(null);
        $em->persist($employeeC);
        $em->flush();
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
        /** @var EmployerHasEmployee $employerHasEmployee */
        foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee) {
            if ($employerHasEmployee->getState() > 0 and $employerHasEmployee->getEmployeeEmployee()->getRegisterState() == 100) {
                $employerHasEmployee->setState(3);
            }
        }
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
        $davPlataMail = false;
        /** @var EmployerHasEmployee $eHE */
        foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE) {
            if ($davPlataMail) break;
            /** @var Contract $contract */
            foreach ($eHE->getContracts() as $contract) {
                if ($contract->getState() == 1) {
                    if ($contract->getPayMethodPayMethod()->getPayTypePayType()->getImage() == "/img/icon_daviplata.png") {
                        $davPlataMail = true;
                        break;
                    }
                }
            }
        }
        if ($davPlataMail) {
            /** @var \RocketSeller\TwoPickBundle\Mailer\TwigSwiftMailer $smailer */
            $smailer=$this->get('symplifica.mailer.twig_swift');
            $smailer->sendEmailByTypeMessage(array('emailType'=>'daviplata','user'=>$user,'subject'=>'Información Daviplata','toEmail'=>$user->getEmail()));
        }

        //Email with info regarding the app
		    $smailer=$this->get('symplifica.mailer.twig_swift');
		    $smailer->sendEmailByTypeMessage(array('emailType'=>'appDownload','user'=>$user,'subject'=>'App Symplifica','toEmail'=>$user->getEmail()));

        $em->persist($user);
        $em->flush();
        return true;
    }

    protected function crearTramites(User $user)
    {
        $response = $this->forward('RocketSellerTwoPickBundle:Procedure:procedure',
            array('userId' => $user->getId()),
            array('_format' => 'json'));
        if($response->getContent()){
            return true;
        }
        return false;
    }

    protected function getMethodId($documentNumber)
    {
        $response = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
        $listPaymentMethods = json_decode($response->getContent(), true);
        return isset($listPaymentMethods['payment-methods'][0]['method-id']) ? ($listPaymentMethods['payment-methods'][0]['method-id']) : false;
    }

}
