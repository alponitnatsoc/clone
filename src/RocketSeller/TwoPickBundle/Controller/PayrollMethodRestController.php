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
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
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
   public function getGeneralPayrollsAction($employeeId){
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
       $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll',array('employeeId'=>$employeeId), array('_format' => 'json'));
       if($insertionAnswer->getStatusCode()!=200){
           return $insertionAnswer;
       }
       return $insertionAnswer;

   }
}