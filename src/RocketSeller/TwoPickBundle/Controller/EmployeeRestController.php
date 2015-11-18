<?php
/**
 * Created by PhpStorm.
 * User: gabrielsamoma
 * Date: 11/17/15
 * Time: 10:43 AM
 */

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class EmployeeRestController extends FOSRestController
{
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when any Id does not exist in the DB"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day.")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="neighborhood", nullable=false, strict=true, description="neighborhood.")
     * @RequestParam(name="phone", nullable=false, strict=true, description="phone.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(name="email", nullable=false, strict=true, description="workplace city.")
     * @RequestParam(name="birthCountry", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="birthDepartment", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="birthCity", nullable=false, strict=true, description="workplace department.")
     * @return View
     */
    public function postNewEmployeeSubmitAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getUser();
        /** @var Employee $employee */
        $employee=null;
        $id=$paramFetcher->get("employeeId");
        $view = View::create();
        if ($id==-1) {
            $employee= new Employee();
            $person= new Person();
            $employee->setPersonPerson($person);
            $employerEmployee= new EmployerHasEmployee();
            $employerEmployee->setEmployeeEmployee($employee);
            $employerEmployee->setEmployerEmployer($user->getPersonPerson()->getEmployer());
            $employee->addEmployeeHasEmployer($employerEmployee);
        }else{
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
            $employee= $repository->find($id);
            //verify if the Id exists or it belongs to the logged user
            $idEmployer=$user->getPersonPerson()->getEmployer()->getIdEmployer();
            $flag=false;
            foreach($employee->getEmployeeHasEmployers() as $ee){
                if($ee->getEmployerEmployer()->getIdEmployer()==$idEmployer){
                    $flag=true;
                    break;
                }
            }
            if($employee==null||!$flag){
                $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                return $this->render(
                    'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                    'employees'=>$employeesData));
            }
        }
        /** @var Person $person */
        $person=$employee->getPersonPerson();
        $person->setNames($paramFetcher->get('names'));
        $person->setLastName1($paramFetcher->get('lastName1'));
        $person->setLastName2($paramFetcher->get('lastName2'));
        $person->setDocument($paramFetcher->get('document'));
        $person->setDocumentType($paramFetcher->get('documentType'));
        $person->setMainAddress($paramFetcher->get('mainAddress'));
        $person->setNeighborhood($paramFetcher->get('neighborhood'));
        $person->setPhone($paramFetcher->get('phone'));
        $person->setCivilStatus($paramFetcher->get('civilStatus'));
        // TODO send email?
        $person->setEmail($paramFetcher->get('email'));
        $datetime = new DateTime();
        $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
        // TODO validate Date
        $person->setBirthDate($datetime);
        // TODO Check if null
        $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
        $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
        $conRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Country');

        // Checking if all the cities deps, and countries are valid

        $tempCity=$cityRepo->find($paramFetcher->get('city'));
        if($tempCity==null){
            $view->setStatusCode(404)->setHeader("error","The city ID ".$paramFetcher->get('city')." is invalid");
            return $view;
        }
        $person->setCity($tempCity);
        $tempDep=$depRepo->find($paramFetcher->get('department'));
        if($tempDep==null){
            $view->setStatusCode(404)->setHeader("error","The department ID ".$paramFetcher->get('department')." is invalid");
            return $view;
        }
        $person->setDepartment($tempDep);
        $tempBCity=$cityRepo->find($paramFetcher->get('birthCity'));
        if($tempBCity==null){
            $view->setStatusCode(404)->setHeader("error","The city ID ".$paramFetcher->get('birthCity')." is invalid");
            return $view;
        }
        $person->setBirthCity($tempBCity);
        $tempBDep=$depRepo->find($paramFetcher->get('birthDepartment'));
        if($tempBDep==null){
            $view->setStatusCode(404)->setHeader("error","The department ID ".$paramFetcher->get('birthDepartment')." is invalid");
            return $view;
        }
        $person->setBirthDepartment($tempBDep);
        $tempBCou=$conRepo->find($paramFetcher->get('birthCountry'));
        if($tempBCou==null){
            $view->setStatusCode(404)->setHeader("error","The country ID ".$paramFetcher->get('birthCountry')." is invalid");
            return $view;
        }
        $person->setBirthCountry($tempBCou);
        $errors = $this->get('validator')->validate($employee, array('Update'));
        $em = $this->getDoctrine()->getManager();
        if (count($errors) == 0) {
            $em->persist($employee);
            $em->flush();
            $view->setData($this->generateUrl('show_dashboard') )->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
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