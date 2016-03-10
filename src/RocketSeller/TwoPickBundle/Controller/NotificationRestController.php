<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NotificationRestController extends FOSRestController
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
     * @param Request $request
     * @return View
     */
    public function postAddNoveltySQLAction(Request $request)
    {
        /** @var Novelty $novelty */
        $novelty=$request->request->get("novelty");
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
                return $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array("error"=>"No se pudo agregar la novedad"));
            }
            return $view->setStatusCode(201);
        }
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }


}
?>