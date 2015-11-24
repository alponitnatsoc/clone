<?php
/**
 * Created by PhpStorm.
 * User: gabrielsamoma
 * Date: 11/17/15
 * Time: 10:43 AM
 */

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\AccountType;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
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
     * @RequestParam(name="birthCountry", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="birthDepartment", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="birthCity", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="")
     *
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="neighborhood", nullable=false, strict=true, description="neighborhood.")
     * @RequestParam(name="phone", nullable=false, strict=true, description="phone.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(name="email", nullable=false, strict=true, description="workplace city.")

     *
     * @RequestParam(name="employeeType", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="contractType", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="timeCommitment", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="position", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="salary", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(array=true, name="idsBenefits", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(array=true, name="idsWorkplaces", nullable=false, strict=true, description="workplace department.")
     *
     * @RequestParam(name="payTypeId", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="bankId", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="accountTypeId", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="frequency", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="cellphone", nullable=true, strict=true, description="workplace department.")
     *
     * @return View
     */
    public function postNewEmployeeSubmitAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getUser();
        /** @var Employee $employee */
        $employee=null;
        $id=$paramFetcher->get("employeeId");
        /** @var EmployerHasEmployee $employerEmployee */
        $employerEmployee=null;
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
            /** @var EmployerHasEmployee $ee */
            foreach($employee->getEmployeeHasEmployers() as $ee){
                if($ee->getEmployerEmployer()->getIdEmployer()==$idEmployer){
                    $employerEmployee=$ee;
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

        //Create the contract

        $contract=new Contract();
        $contract->setSalary($paramFetcher->get('salary'));
        $contract->setState("Active");

        //birth repos
        $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
        $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
        $conRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Country');
        //contract repos
        $contractTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ContractType');
        $employeeContractTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployeeContractType');
        $timeCommitmentRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:TimeCommitment');
        $positionRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Position');
        //payMethod repos
        $bankRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Bank');
        $accountTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:AccountType');
        $payTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PayType');



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
        // Checking if all the contract fields are valid
        /** @var ContractType $tempContractType */
        $tempContractType=$contractTypeRepo->find($paramFetcher->get('contractType'));
        if($tempContractType==null){
            $view->setStatusCode(404)->setHeader("error","The contractType ID ".$paramFetcher->get('contractType')." is invalid");
            return $view;
        }
        $contract->setContractTypeContractType($tempContractType);

        $tempEmployeeContractType=$employeeContractTypeRepo->find($paramFetcher->get('employeeType'));
        if($tempEmployeeContractType==null){
            $view->setStatusCode(404)->setHeader("error","The employeeType ID ".$paramFetcher->get('employeeType')." is invalid");
            return $view;
        }
        $contract->setEmployeeContractTypeEmployeeContractType($tempEmployeeContractType);

        $tempPosition=$positionRepo->find($paramFetcher->get('position'));
        if($tempPosition==null){
            $view->setStatusCode(404)->setHeader("error","The position ID ".$paramFetcher->get('position')." is invalid");
            return $view;
        }
        $contract->setPositionPosition($tempPosition);

        $tempTimeCommitment=$timeCommitmentRepo->find($paramFetcher->get('timeCommitment'));
        if($tempTimeCommitment==null){
            $view->setStatusCode(404)->setHeader("error","The timeCommitment ID ".$paramFetcher->get('timeCommitment')." is invalid");
            return $view;
        }
        $contract->setTimeCommitmentTimeCommitment($tempTimeCommitment);


        //Now for the payType and Pay Method
        $payMethod=new PayMethod();
        //TODO check if valid??
        $payMethod->setAccountNumber($paramFetcher->get('accountNumber'));
        //TODO check if vaild??
        $payMethod->setCellPhone($paramFetcher->get('cellphone'));
        // frecuency in days
        $payMethod->setFrequency($paramFetcher->get('frequency'));
        $payMethod->setUserUser($user);

        //check if the Pay Method Ids are valid: Bank payType and AccountType


        if($paramFetcher->get('bankId')){
            /** @var Bank $tempBank */
            $tempBank=$bankRepo->find($paramFetcher->get('bankId'));
            if($tempBank==null){
                $view->setStatusCode(404)->setHeader("error","The bankId ID ".$paramFetcher->get('bankId')." is invalid");
                return $view;
            }
            $payMethod->setBankBank($tempBank);
        }


        if($paramFetcher->get('payTypeId')){
            /** @var PayType $tempPayType */
            $tempPayType=$payTypeRepo->find($paramFetcher->get('payTypeId'));
            if($tempPayType==null){
                $view->setStatusCode(404)->setHeader("error","The payTypeId ID ".$paramFetcher->get('payTypeId')." is invalid");
                return $view;
            }
            $payMethod->setPayTypePayType($tempPayType);
        }

        if($paramFetcher->get('accountTypeId')){
            /** @var AccountType $tempAccountType */
            $tempAccountType=$accountTypeRepo->find($paramFetcher->get('accountTypeId'));
            if($tempAccountType==null){
                $view->setStatusCode(404)->setHeader("error","The accountTypeId ID ".$paramFetcher->get('accountTypeId')." is invalid");
                return $view;
            }
            $payMethod->setAccountTypeAccountType($tempAccountType);
        }


        //finally add the pay method to the contract and add the contract to the EmployerHasEmployee
        // relation that is been created
        $contract->setPayMethodPayMethod($payMethod);



        //Final Entity Validation
        $errors = $this->get('validator')->validate($employee, array('Update'));
        //ShutDown previos contracts of the employer with the current employee
        $contracts= $employerEmployee->getContracts();
        /** @var Contract $cont */
        foreach($contracts as $cont){
            $cont->setState("UnActive");
        }
        //turn on current contract
        $employerEmployee->addContract($contract);
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