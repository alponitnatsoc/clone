<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\TransactionType;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Traits\PayrollMethodsTrait;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;
//use GuzzleHttp\Psr7\Request;
//use Guzzle\Http\Client;
use GuzzleHttp\Client;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;

/**
 * Contains all the web services to call the payroll system.
 * Get methods can be call as any function.
 * If a post method is going to be call from within the application here is an
 * example:
 *   $request =  new Request();
 *   $request->request->set("employee_id", "123456");
 *   $request->request->set("concept_id", "1");
 *   $this->postFunctionAction($request);
 *
 */
class PayrollMethodRestController extends FOSRestController
{
    use PayrollMethodsTrait;

    public function getGeneralPayrollsAction($employeeId){
       $period = null; $month = null; $year = null;
       $em=$this->getDoctrine()->getManager();
       $ehERepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
       /** @var EmployerHasEmployee $realEHE */
       $realEHE=$ehERepo->find($employeeId);
       $contracts=$realEHE->getContracts();
       $actPayrroll=null;
       /** @var Contract $contract */
       foreach ($contracts as $contract) {
           if($contract->getState()==1){
               $actPayrroll=$contract->getActivePayroll();
               break;
           }
       }
       $request = $this->container->get('request');
       $actContract=$actPayrroll->getContractContract();
       if($actPayrroll->getDaysSent()==0&&$actContract->getTimeCommitmentTimeCommitment()->getCode()=="XD"){
           if($actContract->getFrequencyFrequency()->getPayrollCode()=="Q"&&$actPayrroll->getPeriod()==4){
               $dayStart="16";
           }else{
               $dayStart="1";
           }
           $dateStartPeriod= new DateTime($actPayrroll->getYear()."-".$actPayrroll->getMonth()."-".$dayStart);
           if($actContract->getStartDate()>$dateStartPeriod){
               $dateStartPeriod=$actContract->getStartDate();
           }
           $dayEnd=$actPayrroll->getPeriod()==2?"15":$dateStartPeriod->format("t");
           $dateEndPeriod= new DateTime($actPayrroll->getYear()."-".$actPayrroll->getMonth()."-".$dayEnd);
           $request->setMethod("GET");
           $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getValidVacationDaysContract',array('dateStart'=>$dateStartPeriod->format("Y-m-d"), 'dateEnd'=>$dateEndPeriod->format("Y-m-d"),'contractId'=>$actContract->getIdContract(),'payrollId'=>-1), array('_format' => 'json'));
           if($insertionAnswer->getStatusCode()!=200){
               return $insertionAnswer;
           }
           $workedDays= json_decode($insertionAnswer->getContent(),true)["days"];

           $salaryD=$actContract->getSalary()/$actContract->getWorkableDaysMonth();

           $request->setMethod("POST");
           $request->request->add(array(
               "employee_id"=>$employeeId,
               "novelty_concept_id"=>1,//1 means salary ever and always
               "unity_numbers"=>$workedDays,
               "novelty_start_date"=>$dateStartPeriod->format("d-m-Y"),
               "novelty_end_date"=> $dateEndPeriod->format("d-m-Y"),
               "novelty_base"=>$salaryD,
           ));
           $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddNoveltyEmployee',array('request'=>$request), array('_format' => 'json'));
           if($insertionAnswer->getStatusCode()!=200){
               return $insertionAnswer;
           }
           $actPayrroll->setDaysSent(1);
           $em->persist($actPayrroll);
           $em->flush();
       }
       $request->setMethod("GET");
       $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll',array(
           'employeeId'=>$employeeId,
           'period' => $period,
           'month' => $month,
           'year' => $year),
           array('_format' => 'json'));
       if($insertionAnswer->getStatusCode()!=200){
           return $insertionAnswer;
       }
       return $insertionAnswer;

   }

    /**
     * Process to liquidate the payroll at the end of the month
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Process to liquidate the payroll at the end of the month",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request
     * @return View
     */
    public function putAutoLiquidatePayrollAction(Request $request)
    {
	    $view = View::create();
        $em = $this->getDoctrine()->getManager();
        
        $format = array('_format' => 'json');
        $idPayroll=-1;
        $requ = $request->request->all();
        if(isset($requ['month'])){
            $month=$requ['month'];
            $year=$requ['year'];
            $period=$requ['period'];
            $day =  $requ['day'] ;
            $tokenBack =  $requ['token'] ;
            $idPayroll = isset($requ['idpayroll']) ? $requ['idpayroll'] : -1;
            /** @var User $backuser */
            $backuser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>'backofficesymplifica@gmail.com'));

            if($backuser->getSalt()!=$tokenBack){
                $view->setStatusCode(403);
                $view->setData(array());
                return $view;
            }
        }else{
            $month = date("m");
            $year = date("Y");
            $day = date("d");
        }
        $payrollEntity = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        $period =  4 ;
	    
        if ($day == 26) {
            $params = array(
                "month" => $month,
                "period"=>$period,
                "year"=>$year,
                "paid" => 0

            );
        } else  if ($day == 13) {
            $period = 2;
            $params = array(
                "month" => $month,
                "year"=>$year,
                "period" => $period,
                "paid" => 0
            );
        } else {
            $view->setStatusCode(200);

            $view->setData("No es dia para cerrar nominas automaticamente");
            return $view;
        }
        if($idPayroll!=-1){
            $params['idPayroll']=$idPayroll;
        }

        $payrolls = $payrollEntity->findBy($params);

//         $result = count($payrolls);
	
	      $podArray = array();
	      $employerArray = array();
	    $cont = 0;
        /** @var \RocketSeller\TwoPickBundle\Entity\Payroll $payroll */
        foreach ($payrolls as $payroll) {
	        if($payroll->getPaid() > 0 || $payroll->getContractContract() == null ||
	           $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee() == null ||
	           $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getState() == null ||
	           $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getState() < 4) {
	        	continue;
	        }
            $employer=$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer();
            $eHEs=$employer->getEmployerHasEmployees();
            //CREAR orden de compra
            $entity = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
            $pos = $entity->findOneBy(array('idNovoPay' => 'P1')); // Estado pendiente por pago
            $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
            $idUser = $userRepo->findOneBy(array("personPerson" => $employer->getPersonPerson()));
            $purchaseOrder = new PurchaseOrders();
            $now = new \DateTime();
            $purchaseOrder->setDateCreated($now);
            $purchaseOrder->setIdUser($idUser);
            $purchaseOrder->setName("Cierre nómina mes " . $month ." periodo ". $period);
            $purchaseOrder->setPurchaseOrdersStatus($pos);
            // el total para saber cuanto hay que pagar de pila
            $totalPilaToPay=0;
            //la descripcion para la pila
            $podPila = new PurchaseOrdersDescription();
//
	        $entre = false;
            /** @var EmployerHasEmployee $ehe */
            foreach ($eHEs as $ehe) {
                if($ehe->getState()>3){
                    $employee=$ehe->getEmployeeEmployee();
                    $contracts=$ehe->getContracts();
                    $realContract=null;
                    /** @var Contract $contract */
                    foreach ($contracts as $contract) {
                        if($contract->getState()==1){
                            $realContract=$contract;
                            break;
                        }
                    }
                    if($realContract!=null){
                        $activePayrrol=$realContract->getActivePayroll();
                        if($activePayrrol->getMonth()==$month&&$activePayrrol->getPeriod()==$period){
                            $payrollPods=$activePayrrol->getPurchaseOrdersDescription();
                            if($activePayrrol->getPaid()==0){
                                $empHasEmp=$ehe;

                                $dataNomina = $this->getInfoNominaSQL($ehe);
	                            if($dataNomina == null) {
	                            	continue;
	                            }
	                            $entre = true;
                                $totalLiquidation = $this->totalLiquidation($dataNomina);
                                $salary = $totalLiquidation['total'];
                                $pila = $this->getTotalPILA($empHasEmp, $activePayrrol);
                                //checking if any new stuff was added to this payroll
                                /** @var ArrayCollection $novelties */
                                $novelties=$totalLiquidation["novelties"];
                                $sqlNovelties=$activePayrrol->getSqlNovelties();
                                /** @var Novelty $nowNovelty */
                                for($z=0;$z<$novelties->count();$z++){
                                    if($sqlNovelties->count()<=$z){
                                        //add and end
                                        $sqlNovelties->add($novelties->get($z));
                                    }else{
                                        /** @var Novelty $actNovel */
                                        $actNovel=$sqlNovelties->get($z);
                                        $actNovel->setSqlValue($novelties->get($z)->getSqlValue());
                                        $actNovel->setNoveltyTypeNoveltyType($novelties->get($z)->getNoveltyTypeNoveltyType());
                                        $actNovel->setName($actNovel->getNoveltyTypeNoveltyType()->getName());
                                        $actNovel->setSqlNovConsec($novelties->get($z)->getSqlnovConsec());
                                        $actNovel->setUnits($novelties->get($z)->getUnits());
                                    }
                                }
                                $em=$this->getDoctrine()->getManager();
                                /** @var Novelty $sqlNovelty */
                                foreach ($sqlNovelties as $sqlNovelty) {
                                    $sqlNovelty->setSqlPayrollPayroll($activePayrrol);
                                    $em->persist($sqlNovelty);
                                }
                                $em->flush();
                                /*$req = new Request();
                                $req->request->set("employee_id", $ehe->getIdEmployerHasEmployee());
                                $req->request->set("execution_type", "C");

                                $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postExecuteFinalLiquidation", array("request" => $req), $format);
                                if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
                                    $not = new Notification();
                                    $not->setPersonPerson($empHasEmp->getEmployerEmployer()->getPersonPerson());
                                    $not->setType("alert");
                                    $not->setTitle("La nómina del mes " . $month . " no se pudo cerrar");
                                    $not->setStatus(1);
                                    $not->setAccion("liquidar nómina y pago a empleados");
                                    $not->setRelatedLink("/payroll");
                                    $not->setDescription("No fue posible cerrar la nómina");

                                    $em->persist($not);
                                    $em->flush();

                                    $total[] = array("EmployerHasEmployee " . $ehe->getIdEmployerHasEmployee() => "error en sql");
                                    continue;
                                }*/
                                if(count($activePayrrol->getPurchaseOrdersDescription())>0){
                                    $pod=$activePayrrol->getPurchaseOrdersDescription()->get(0);
                                }else{
                                    $pod = new PurchaseOrdersDescription();
                                }
                                $pod->setDescription("Pago de nómina mes " . $month);
                                $pod->setPayrollPayroll($activePayrrol);
                                $prodRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
                                $product = $prodRepo->findOneBy(array("simpleName" => "PN"));
                                $pod->setProductProduct($product);
                                $pod->setValue($salary);
                                $pod->setPurchaseOrdersStatus($pos);
                                $purchaseOrder->addPurchaseOrderDescription($pod);
                                //guardando que esta payroll ya está metida en una purchase order
                                $activePayrrol->setPaid(1);
                                $em->persist($activePayrrol);
                                $em->flush();
                                //creating the new payroll
                                $contract=$activePayrrol->getContractContract();
                                $newActivePayroll=new Payroll();
                                if($period==4){
                                    $nextMonth=($month%12)+1;
                                    if($contract->getFrequencyFrequency()->getPayrollCode()=="Q")
                                        $nextPeriod=2;
                                    else
                                        $nextPeriod=4;
                                }else{
                                    $nextPeriod=4;
                                    $nextMonth=$month;
                                }
                                if($nextMonth==1&&$period==4){
                                    $nextYear=intval($activePayrrol->getYear())+1;
                                }else{
                                    $nextYear=$activePayrrol->getYear();
                                }
                                $temporalDate = new DateTime($nextYear."-".$nextMonth."-01");
                                $newActivePayroll->setMonth($temporalDate->format("m"));
                                $newActivePayroll->setYear($temporalDate->format("Y"));
                                $newActivePayroll->setPeriod($nextPeriod);
                                $contract->addPayroll($newActivePayroll);
                                $contract->setActivePayroll($newActivePayroll);
                                $em->persist($contract);
                                $em->flush();
                                if ($activePayrrol->getPeriod() == 4) {
                                    $totalPilaToPay+= $pila["total"];//TODO verificar el resultado de pila se removio $aportes["total"] +
                                    if($activePayrrol->getPila()==null){
                                        $podPila->addPayrollsPila($activePayrrol);
                                    }else{
                                        $podPila=$activePayrrol->getPila();
                                    }
                                }

                            }

                        }
                    }

                }

            }
            if(count($podPila->getPayrollsPila())!=0&&$podPila->getPurchaseOrdersStatus()!=null&&$podPila->getPurchaseOrdersStatus()->getIdNovoPay()=="P1"){
                $podPila->setDescription("Pago de PILA mes " . $month);
                $prodRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
                $product = $prodRepo->findOneBy(array("simpleName" => "PP"));
                $podPila->setProductProduct($product);
                $podPila->setValue($totalPilaToPay);
                $podPila->setPurchaseOrdersStatus($pos);
                $purchaseOrder->addPurchaseOrderDescription($podPila);

		            if($podPila->getEnlaceOperativoFileName() == NULL && $podPila->getUploadedFile() == NULL){
			            //this means the file has not be sent yet
			            $podArray[] = $podPila->getIdPurchaseOrdersDescription();
			            $employerArray[] = $employer;
		            }
            }
            if($purchaseOrder->getPurchaseOrderDescriptions()->count()==0){
              continue;
            }
            /** @var UtilsController $utils */
            $utils = $this->get('app.symplifica_utils');
            $not = new Notification();
            $not->setPersonPerson($employer->getPersonPerson());
            $not->setType("alert");
            $not->setTitle("Pagar ". $utils->period_number_to_name($period) . " " . $utils->month_number_to_name($month));
            $not->setDescription("Pagar ". $utils->period_number_to_name($period) . " " . $utils->month_number_to_name($month));
            $not->setDeadline(new DateTime());
            $not->setStatus(1);
            $not->setAccion("Pagar");

            $em->persist($not);
            $em->persist($purchaseOrder);
            $em->flush();
            $not->setRelatedLink($this->generateUrl("payroll", array('idNotif'=>$not->getId())));
            $em->persist($not);
            $em->flush();
			$em->clear();


            $total[] = array("Empleador " . $employer->getIdEmployer() => "proceso liquidado de nomina");
	        if($entre) {
		        $cont++;
	        }
	        if($cont == 5) {
	        	break;
	        }

        }

        if (!isset($total)) {
            $total = "no hay nominas pendientes por cerrar ";
        }
	    if($idPayroll!=-1){
		    $view->setStatusCode(200);
		
		    $view->setData($total);
		    return $view;
	    }

//         $result .= count($pod);
//         $total = $data;
//
//	      //From this point we send the pila files to hightech in order to be upload on Enlace Operativo
	      foreach ($podArray as $index => $singlePodId){
		      //TODO DanielRico Remove this as soon the final liquidation works and the novelty support is complete

		      $singlePod = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->find($singlePodId);
		      $payrollsPila = $singlePod->getPayrollsPila();
		      $haveNovelties = false;

		      $payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");

		      /** @var Payroll $payrollPila */
		      foreach ( $payrollsPila as $payrollPila ){
			      if( count($payrollPila->getNovelties()) > 0){
				      $haveNovelties = true;
				      break;
			      }
			      //If is Quincenal we need to check the first payroll of the month to see If we have novelties
			      if($payrollPila->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q"){
				      $singlePayroll = $payrollRepo->findOneBy(array('contractContract' => $payrollPila->getContractContract() , 'period' => 2 , 'year' => $payrollPila->getYear() , 'month' => $payrollPila->getMonth()) );
				      if($singlePayroll != NULL){
					      if( count($singlePayroll->getNovelties()) > 0){
						      $haveNovelties = true;
						      break;
					      }
				      }
			      }
		      }
		      //End of segment

		      $transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'CPla'));

		      $transaction = new Transaction();
		      $transaction->setTransactionType($transactionType);

		      if($haveNovelties == false) {
			      $request->setMethod("GET");
			      $insertionAnswerTextFile = $this->forward('RocketSellerTwoPickBundle:PilaPlainTextRest:getMonthlyPlainText', array('podId' => $singlePod->getIdPurchaseOrdersDescription(), 'download' => 'generate'), array('_format' => 'json'));

			      $request->setMethod("POST");
			      $request->request->add(array(
				      "GSCAccount" => $employerArray[$index]->getIdHighTech(),
				      "FileToUpload" => json_decode($insertionAnswerTextFile->getContent(), true)['fileToSend']
			      ));
			      $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postUploadFileToPilaOperator', array('request' => $request) , array('_format' => 'json'));
			      if ($insertionAnswer->getStatusCode() == 200) {
				      //Received succesfully
				      $radicatedNumber = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
				      $transaction->setRadicatedNumber($radicatedNumber);
				      $purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaEnv'));

				      $total[] = array("Empleador " . $employerArray[$index]->getIdEmployer() => " archivo de pila cargado exitosamente");
			      } else {
				      //If some kind of error
				      $purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrSer'));
				      $total[] = array("Empleador " . $employerArray[$index]->getIdEmployer() => " error cargando archivo de pila");
			      }
		      }
		      else {
			      $purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrNov'));
			      $total[] = array("Empleador " . $employerArray[$index]->getIdEmployer() => " archivo de pila no fue cargado, tiene novedades");
		      }

		      $em = $this->getDoctrine()->getManager();
		      $transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
		      $em->persist($transaction);
		      $em->flush();
		      $singlePod->setUploadedFile($transaction->getIdTransaction());
		      $singlePod->addTransaction($transaction);
		      $em->persist($singlePod);
		      $em->flush();
	      }
//	    $view = View::create();
//        $view->setStatusCode(200);
//
//        $view->setData(array("cont" => 0));
//        return $view;
	
	    $view = View::create();
	    $view->setStatusCode(200);
	
	    $view->setData(array("total" => $total,  "cont" => $cont));
	    return $view;
    }
	
	/**
	 *  Send Planilla File To Enlace Operativo the ones that weren't sent in AutoLiquidatePayroll<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Send Planilla File To Enlace Operativo",
	 *   statusCodes = {
	 *     200 = "OK"
	 *   }
	 * )
	 *
	 * @param Request $request
	 * @return View
	 */
	public function putSendPlanillaFileToEnlaceOperativoBackAction(Request $request){
		$params = $request->request->all();
		$period = $params['period'];
		$month = $params['month'];
		$year = $params['year'];
		
		$conta = 0;
		
		$em=$this->getDoctrine()->getManager();
		
		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(array(
		  "period" => $period,
		  "month" => $month,
		  "year" => $year,
		  "paid" => 1));
		
		foreach($payrolls as $payroll){
			$podPila = $payroll->getPila();
			
			if($podPila != NULL && $podPila->getUploadedFile() == NULL){
				
				$payrollsPila = $podPila->getPayrollsPila();
				$haveNovelties = false;
				
				$payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
				
				/** @var Payroll $payrollPila */
				foreach ( $payrollsPila as $payrollPila ){
					if( count($payrollPila->getNovelties()) > 0){
						$haveNovelties = true;
						break;
					}
					//If is Quincenal we need to check the first payroll of the month to see If we have novelties
					if($payrollPila->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q"){
						$singlePayroll = $payrollRepo->findOneBy(array('contractContract' => $payrollPila->getContractContract() , 'period' => 2 , 'year' => $payrollPila->getYear() , 'month' => $payrollPila->getMonth()) );
						if($singlePayroll != NULL){
							if( count($singlePayroll->getNovelties()) > 0){
								$haveNovelties = true;
								break;
							}
						}
					}
				}
				
				$transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'CPla'));
				
				$transaction = new Transaction();
				$transaction->setTransactionType($transactionType);
				
				if($haveNovelties == false) {
					$request->setMethod("GET");
					$insertionAnswerTextFile = $this->forward('RocketSellerTwoPickBundle:PilaPlainTextRest:getMonthlyPlainText', array('podId' => $podPila->getIdPurchaseOrdersDescription(), 'download' => 'generate'), array('_format' => 'json'));
					
					$request->setMethod("POST");
					$request->request->add(array(
					  "GSCAccount" => $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdHighTech(),
					  "FileToUpload" => json_decode($insertionAnswerTextFile->getContent(), true)['fileToSend']
					));
					$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postUploadFileToPilaOperator', array('request' => $request) ,  array('_format' => 'json'));
					if ($insertionAnswer->getStatusCode() == 200) {
						//Received succesfully
						$radicatedNumber = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
						$transaction->setRadicatedNumber($radicatedNumber);
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaEnv'));
					} else {
						//If some kind of error
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrSer'));
					}
				}
				else {
					$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrNov'));
				}
				
				$em = $this->getDoctrine()->getManager();
				$transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
				$em->persist($transaction);
				$em->flush();
				$podPila->setUploadedFile($transaction->getIdTransaction());
				$podPila->addTransaction($transaction);
				$em->persist($podPila);
				$em->flush();
				
				$conta = $conta + 1;
				
			}
			
			if($conta == 10){
				$view = new View();
				$view->setStatusCode(200);
				
				$view->setData(array('conta' => $conta));
				return $view;
			}
		}
		
		$view = new View();
		$view->setStatusCode(200);
		
		$view->setData(array('conta' => $conta));
		return $view;
	}
	
	/**
	 *  fix pod pila after AutoLiquidatePayroll<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Send Planilla File To Enlace Operativo",
	 *   statusCodes = {
	 *     200 = "OK"
	 *   }
	 * )
	 *
	 * @param Request $request
	 * @return View
	 */
	public function putFixPODPilaAction(Request $request){
		
		$params = $request->request->all();
		$period = $params['period'];
		$month = $params['month'];
		$year = $params['year'];
		
		$em=$this->getDoctrine()->getManager();
		
		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(
		  array("period" => $period,
		    "month" => $month,
		    "year" => $year,
		    "paid" => 1));
		$pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array("idNovoPay" => "P1"));
		$product = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array("simpleName" => "PP"));
		
		foreach($payrolls as $payroll){
			$pilaPOD = $payroll->getPila();
			
			if($pilaPOD!=null&&$pilaPOD->getProductProduct() == NULL){
				
				$pilaPOD->setPurchaseOrdersStatus($pos);
				$pilaPOD->setProductProduct($product);
				$pilaPOD->setDescription("Pago de Aportes a Seguridad Social mes Enero");
				
				$poList = $payroll->getPurchaseOrdersDescription();
				
				/** @var PurchaseOrdersDescription $singlePod */
				foreach ($poList as $singlePod){
					$pilaPOD->setPurchaseOrders($singlePod->getPurchaseOrders());
					break;
				}
				
				$totalValue = 0;
				$payrollsPila = $pilaPOD->getPayrollsPila();
				
				/** @var Payroll $singlePayroll */
				foreach ($payrollsPila as $singlePayroll){
					$pilaDetails = $singlePayroll->getPilaDetails();
					
					/** @var PilaDetail $singleDetail */
					foreach ($pilaDetails as $singleDetail){
						$totalValue = $totalValue + $singleDetail->getSqlValueCia() + $singleDetail->getSqlValueEmp();
					}
				}
				
				$pilaPOD->setValue($totalValue);
				$em->persist($pilaPOD);
				$em->flush();
			}
		}
		
		$view = new View();
		$view->setStatusCode(200);
		
		$view->setData(array());
		return $view;
	}
}