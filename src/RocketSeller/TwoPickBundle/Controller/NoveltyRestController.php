<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Novelty;
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
     * @param $contractId
     * @return View
     */
    protected function getVacationDaysAction($dateStart, $dateEnd,$contractId)
    {
        $em=$this->getDoctrine()->getManager();
        $contractRepo=$em->getRepository("RocketSellerTwoPickBundle:Contract");
        /** @var Contract $realContract */
        $realContract=$contractRepo->find($contractId);
        $wkd=array();
        $weekWorkableDays=$realContract->getWeekWorkableDays();
        /** @var WeekWorkableDays $wwd */
        foreach ($weekWorkableDays as $wwd) {
            $wkd[$wwd->getDayNumber()]=true;
        }
        $dateRStart= new DateTime($dateStart);
        $dateREnd= new DateTime($dateEnd);
        $interval=$dateRStart->diff($dateREnd);
        $answer=0;
        $numberDays=$interval->format("%a");
        for($i=0;$i<$numberDays;$i++){
            $dateToCheck=new DateTime();
            $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);
            $this->workable($dateToCheck);
            if(isset($wkd[$dateToCheck->format("w")])){
                $answer++;
            }

        }


        $view = View::create();
        $view->setStatusCode(200)->setData(array("days"=>$answer));

        return $view;
    }
    private function workable ($dateToCheck){
        return true;
    }


}
?>