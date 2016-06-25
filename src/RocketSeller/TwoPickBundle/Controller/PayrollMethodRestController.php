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
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
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
           $dayStart=$actPayrroll->getPeriod()==2?"16":"1";
           $dateStartPeriod= new DateTime($actPayrroll->getYear()."-".$actPayrroll->getMonth()."-".$dayStart);
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
           $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddNoveltyEmployee', array('_format' => 'json'));
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
     * @return View
     */
    public function putAutoLiquidatePayrollAction()
    {
        $em = $this->getDoctrine()->getManager();

        $view = View::create();
        $format = array('_format' => 'json');

        $payrollEntity = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        $month = date("m");
        $day = date("d");
        $period = 4;
        //TODO tengo que buscar las que no están pagas
        if ($day == 25) {
            $params = array(
                "month" => $month,
                "paid" => 0

            );
        } else  if ($day == 12) {
            $period = 2;
            $params = array(
                "month" => $month,
                "period" => $period,
                "paid" => 0
            );
        } else {
            $view->setStatusCode(200);

            $view->setData("No es dia para cerrar nominas automaticamente");
            return $view;
        }

        $payrolls = $payrollEntity->findBy($params);
        dump($payrolls);
//         $result = count($payrolls);

        /** @var \RocketSeller\TwoPickBundle\Entity\Payroll $payroll */
        foreach($payrolls as $payroll) {
            if($payroll->getPaid()!=0){
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

            /** @var EmployerHasEmployee $ehe */
            foreach ($eHEs as $ehe) {
                if($ehe->getState()>=3){
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
                                $salary = $this->totalLiquidation($dataNomina)['total'];
                                $pila = $this->getTotalPILA($empHasEmp);
                                $req = new Request();
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
                                }
                                if(count($activePayrrol->getPurchaseOrdersDescription())>0){
                                    $pod=$activePayrrol->getPurchaseOrdersDescription()->get(0);
                                }else{
                                    $pod = new PurchaseOrdersDescription();
                                }
                                $pod->setDescription("Pago de nómina mes " . $month);
                                $pod->setPayrollPayroll($payroll);
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
                                if($contract->getFrequencyFrequency()->getPayrollCode()=="Q"&&$period==4){
                                    $nextPeriod=2;
                                }else{
                                    $nextPeriod=4;
                                }
                                $nextMonth=($month%12)+1;
                                if($nextMonth==1){
                                    $nextYear=intval($activePayrrol->getYear())+1;
                                }else{
                                    $nextYear=$activePayrrol->getYear();
                                }
                                $newActivePayroll->setMonth($nextMonth);
                                $newActivePayroll->setYear($nextYear);
                                $newActivePayroll->setPeriod($nextPeriod);
                                $contract->addPayroll($newActivePayroll);
                                $contract->setActivePayroll($newActivePayroll);
                                $em->persist($contract);
                                $em->flush();

                                if($activePayrrol->getPila()==null){
                                    $totalPilaToPay+= $pila["total"];//TODO verificar el resultado de pila se removio $aportes["total"] +
                                    $podPila->addPayrollsPila($activePayrrol);
                                }

                            }

                        }
                    }

                }

            }
            if(count($podPila->getPayrollsPila())!=0){
                $podPila->setDescription("Pago de PILA mes " . $month);
                $podPila->setPayrollPayroll($payroll);
                $prodRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
                $product = $prodRepo->findOneBy(array("simpleName" => "PP"));
                $podPila->setProductProduct($product);
                $podPila->setValue($totalPilaToPay);
                $podPila->setPurchaseOrdersStatus($pos);
                $purchaseOrder->addPurchaseOrderDescription($podPila);
            }
            $not = new Notification();
            $not->setPersonPerson($employer->getPersonPerson());
            $not->setType("alert");
            $not->setTitle("Pagar nomina del mes ".$month." periodo ".$period);
            $not->setDescription("Pagar nomina del mes ".$month." periodo ".$period);
            $not->setDeadline(new DateTime());
            $not->setStatus(1);
            $not->setAccion("pagar");

            $em->persist($not);
            $em->persist($purchaseOrder);
            $em->flush();



            $total[] = array("Empleador " . $employer->getIdEmployer() => "proceso liquidado de nomina");

        }

        if (!isset($total)) {
            $total = "no hay nominas pendientes por cerrar";
        }

//         $result .= count($pod);
//         $total = $data;

        $view->setStatusCode(200);

        $view->setData($total);
        return $view;
    }
}