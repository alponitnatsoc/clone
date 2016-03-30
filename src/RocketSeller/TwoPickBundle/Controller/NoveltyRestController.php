<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NoveltyRestController extends FOSRestController
{

    /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Returned when the notification id doesn't exists "
     *   }
     * )
     *
     * @param $idNotification
     * @return View
     */
    public function getAddNoveltySqlAction($idNotification)
    {

        $em=$this->getDoctrine();
        $noveltyRepo=$em->getRepository("RocketSellerTwoPickBundle:Novelty");
        /** @var Novelty $novelty */
        $novelty=$noveltyRepo->find($idNotification);
        $view = View::create();
        $request = $this->container->get('request');
        $noveltyType=$novelty->getNoveltyTypeNoveltyType();
        $idEmployerHasEmployee=$novelty->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee();
        if($noveltyType->getAbsenteeism()==null){
            if($noveltyType->getPayrollCode()==150||$noveltyType->getPayrollCode()==145){
                $methodToCall="postAddVacationParameters";
                $request->request->add(array(
                    "employee_id"=>$idEmployerHasEmployee,
                    "money_days"=>$novelty->getAmount(),
                    "number_days"=>$novelty->getUnits(),
                    "exit_date"=>$novelty->getDateStart()->format("d-m-Y"),
                ));
            }else{
                $methodToCall="postAddNoveltyEmployee";
                $request->request->add(array(
                    "employee_id"=>$idEmployerHasEmployee,
                    "novelty_concept_id"=>$noveltyType->getPayrollCode(),
                    "novelty_value"=>$novelty->getAmount(),
                    "unity_numbers"=>$novelty->getUnits(),
                    "novelty_start_date"=>$novelty->getDateStart()->format("d-m-Y"),
                    "novelty_end_date"=>$novelty->getDateEnd()->format("d-m-Y"),
                ));
            }
            $request->setMethod("POST");
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:'.$methodToCall, array('_format' => 'json'));
            if($insertionAnswer->getStatusCode()!=200){
                return $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array("error"=>"No se pudo agregar la novedad"));
            }
            return $view->setStatusCode(201);

        }else{
            $methodToCall="postAddAbsenteeismEmployee";
            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id"=>$idEmployerHasEmployee,
                "absenteeism_type_id"=>$noveltyType->getAbsenteeism(),
                "absenteeism_state"=>"ACT",
                "absenteeism_units"=>$novelty->getUnits(),
                "absenteeism_start_date"=>$novelty->getDateStart()->format("d-m-Y"),
                "absenteeism_end_date"=>$novelty->getDateEnd()->format("d-m-Y"),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:'.$methodToCall, array('_format' => 'json'));
            if($insertionAnswer->getStatusCode()!=200){
                echo "hola 3";

                return $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array("error"=>"No se pudo agregar la novedad"));
            }

            return $view->setStatusCode(201);
        }
    }

    /**
     * Get the validation errors
     *
     *
     * @param $dateStart
     * @param $dateEnd
     * @param int $contractId
     * @param int $payrollId
     * @Route(defaults={"contractId"="-1","payrollId"="-1"})
     *
     * @return View
     */
    public function getValidVacationDaysContractAction($dateStart, $dateEnd,$contractId,$payrollId)
    {
        $em=$this->getDoctrine()->getManager();
        if($contractId==-1){
            $payrollRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
            /** @var Payroll $realPayroll */
            $realPayroll=$payrollRepo->find($payrollId);
            $realContract=$realPayroll->getContractContract();
        }else{
            $contractRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract");
            /** @var Contract $realContract */
            $realContract=$contractRepo->find($contractId);
        }

        $wkd=array();
        if($realContract->getTimeCommitmentTimeCommitment()->getName()=="Tiempo Completo"){
            $wkd[6]=true;
            $wkd[5]=true;
            $wkd[4]=true;
            $wkd[3]=true;
            $wkd[2]=true;
            $wkd[1]=true;
        }else{
            $weekWorkableDays=$realContract->getWeekWorkableDays();
            /** @var WeekWorkableDays $wwd */
            foreach ($weekWorkableDays as $wwd) {
                $wkd[$wwd->getDayNumber()]=true;
            }
        }
        $dateRStart= new DateTime($dateStart);
        $dateREnd= new DateTime($dateEnd);
        $dateREnd->modify('+1 day');
        $interval=$dateRStart->diff($dateREnd);
        $answer=0;
        $numberDays=$interval->format("%a");
        $days=[];
        for($i=0;$i<$numberDays;$i++){
            $dateToCheck=new DateTime();
            $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);

            if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                $answer++;
                $days[]=$dateToCheck->format("Y-m-d");
            }

        }
        $view = View::create();
        $view->setStatusCode(200)->setData(array("days"=>$answer,"dateToCheck"=>$days,"wkd"=>$wkd));

        return $view;
    }

    /**
     * @param DateTime $dateToCheck
     * @return bool
     */
    private function workable ($dateToCheck){
        $holyDays=array();
        $date=new DateTime("2016-"."01"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."01"."-"."11");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."21");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."24");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."25");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."09");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."30");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."06"."-"."06");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."07"."-"."04");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."07"."-"."20");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."08"."-"."07");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."08"."-"."15");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."10"."-"."17");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."11"."-"."07");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."11"."-"."14");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."12"."-"."08");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."12"."-"."25");
        $holyDays[]=$date;
        /** @var DateTime $hd */
        foreach ($holyDays as $hd) {
            if($dateToCheck->format("Y-m-d")==$hd->format("Y-m-d"))
                return false;
        }

        return true;
    }


}
?>