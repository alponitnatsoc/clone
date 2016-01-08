<?php
/**
 * Created by PhpStorm.
 * User: gabrielsamoma
 * Date: 11/17/15
 * Time: 10:43 AM
 */

namespace RocketSeller\TwoPickBundle\Controller;

use GuzzleHttp\Psr7\Response;
use RocketSeller\TwoPickBundle\Admin\ContractHasBenefitsAdmin;
use RocketSeller\TwoPickBundle\Entity\AccountType;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\Benefits;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace;
use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Phone;
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
     *
     * @RequestParam(name="payTypeId", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="bankId", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="accountTypeId", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="frequency", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="cellphone", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="contractId", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="creditCard", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="expiryDate", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="cvv", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="nameOnCard", nullable=false, strict=true, description="id of the contract.")
     *
     * @return View
     */
    public function postNewEmployeeSubmitAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getUser();
        /** @var Employee $employee */
        $employee=null;
        $idContract=$paramFetcher->get("contractId");
        $view = View::create();

        //search the contract
        $contractRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /** @var Contract $contract */
        $contract=$contractRepo->find($idContract);
        if($contract==null){
            //TODO return him to steps 2,3
            $view->setStatusCode(403)->setData(array("error"=> array('contract'=>"You don't have that contract")));
            return $view;
        }
        if($user->getPersonPerson()->getEmployer()->getIdEmployer()!=$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdEmployer()){
            //TODO return him to step 2
            $view->setStatusCode(403)->setData(array(
                "error"=> array('contract'=>"You don't have that contract"),
                "ulr"=>""));
            return $view;

        }
        //payMethod repos
        $bankRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Bank');
        $accountTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:AccountType');
        $payTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PayType');


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

        // add the user to pay
        // URL used for test porpouses, the line above should be used in production.
        $url_request ="http://localhost:8002/api/public/v1/clients";
        $userPerson=$user->getPersonPerson();
        $parameters=array(
            'documentType'=>    $userPerson->getDocumentType(),
            'documentNumber'=>  $userPerson->getDocument(),
            'name'=>            $userPerson->getNames(),
            'lastName'=>        $userPerson->getLastName1()." ".$userPerson->getLastName2(),
            'year'=>            $userPerson->getBirthDate()->format("Y"),
            'month'=>           $userPerson->getBirthDate()->format("m"),
            'day'=>             $userPerson->getBirthDate()->format("d"),
            'phone'=>           $userPerson->getPhones()->get(0)->getPhoneNumber(),
            'email'=>           $user->getEmail(),
        );
        $options = array(
            'json'        => $parameters,
        );
//        /** @var Response $response */
//        $response = $this->get('guzzle.client.api_rest')->post($url_request, $options);
//        if($response->getStatusCode()!=201){
//            $view->setData(array('error'=>array('credit card'=>'something is wrong with the information')))->setStatusCode($response->getStatusCode());
//            return $view;
//        }
//        //finally add the pay method to the contract and add the contract to the EmployerHasEmployee
        // relation that is been created
        $contract->setPayMethodPayMethod($payMethod);


        //Final Entity Validation
        $errors = $this->get('validator')->validate($contract, array('Update'));



        $em = $this->getDoctrine()->getManager();
        if (count($errors) == 0) {
            $em->persist($contract);
            $em->flush();
            $view->setData(array('url'=>$this->generateUrl('show_dashboard')))->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false,  strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false,  strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false,  strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year of birth.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month of birth.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day of birth.")
     * @RequestParam(name="birthCountry", nullable=false, strict=true, description="Country where the employee was birth.")
     * @RequestParam(name="birthDepartment", nullable=false, strict=true, description="Department where the employee was birth.")
     * @RequestParam(name="birthCity", nullable=false, strict=true, description="city where the employee was birth.")
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="the civil status of the employee")
     * @RequestParam(name="gender", nullable=false, strict=true, description="the gender of the employee")
     * @RequestParam(name="documentExpeditionDateYear", nullable=false, strict=true, description="document expedition year")
     * @RequestParam(name="documentExpeditionDateMonth", nullable=false, strict=true, description="document expedition month")
     * @RequestParam(name="documentExpeditionDateDay", nullable=false, strict=true, description="document expedition day")
     * @RequestParam(name="documentExpeditionPlace", nullable=false, strict=true, description="the gender of the employee")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     *
     * @return View
     */
    public function postNewEmployeeSubmitStep1Action(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        /** @var Person $people */
        $people =$user->getPersonPerson();
        $employer=$people->getEmployer();
        if ($employer==null) {
            //TODO return user to step 1
        }

        //all the data is valid
        if (true) {
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
            $person->setCivilStatus($paramFetcher->get('civilStatus'));
            $person->setGender($paramFetcher->get('gender'));
            $datetime = new DateTime();
            $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
            // TODO validate Date
            $person->setBirthDate($datetime);
            $datetimeDocument = new DateTime();
            $datetimeDocument->setDate($paramFetcher->get('documentExpeditionDateYear'), $paramFetcher->get('documentExpeditionDateMonth'), $paramFetcher->get('documentExpeditionDateDay'));
            $person->setDocumentExpeditionDate($datetimeDocument);
            $person->setDocumentExpeditionPlace($paramFetcher->get('documentExpeditionPlace'));

            //birth repos
            $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $conRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Country');

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
            $em = $this->getDoctrine()->getManager();
            $errors = $this->get('validator')->validate($employee, array('Update'));
            if (count($errors) == 0) {
                $em->persist($employee);
                $em->flush();
                $view->setData(array('response'=>array('idEmployee'=>$employee->getIdEmployee())))->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }



        }
    }
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when the requested Ids don't exist"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="neighborhood", nullable=false, strict=true, description="neighborhood.")
     * @RequestParam(array=true, name="phonesIds", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(array=true, name="phones", nullable=false, strict=true, description="main workplace Address.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(name="email", nullable=false, strict=true, description="workplace city.")
     * @return View
     */
    public function postNewEmployeeSubmitStep2Action(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        /** @var Person $person */
        $person =$user->getPersonPerson();
        $employer=$person->getEmployer();
        if ($employer==null) {
            //TODO Return user to step 1
        }

        //all the data is valid
        if (true) {
            /** @var Employee $employee */
            $employee=null;
            $id=$paramFetcher->get("employeeId");
            /** @var EmployerHasEmployee $employerEmployee */
            $employerEmployee=null;
            $view = View::create();
            if ($id==-1) {
                //TODO Return user to step 2,1
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
            $people=$employerEmployee->getEmployeeEmployee()->getPersonPerson();
            $people->setMainAddress($paramFetcher->get('mainAddress'));
            $people->setNeighborhood($paramFetcher->get('neighborhood'));
            $people->setEmail($paramFetcher->get('email'));
            $phoneRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Phone');
            $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $actualPhonesId=$paramFetcher->get('phonesIds');
            $actualPhonesAdd=$paramFetcher->get('phones');
            $tempCity=$cityRepo->find($paramFetcher->get('city'));
            if($tempCity==null){
                $view->setStatusCode(404)->setHeader("error","The city ID ".$paramFetcher->get('city')." is invalid");
                return $view;
            }
            $people->setCity($tempCity);
            $tempDep=$depRepo->find($paramFetcher->get('department'));
            if($tempDep==null){
                $view->setStatusCode(404)->setHeader("error","The department ID ".$paramFetcher->get('department')." is invalid");
                return $view;
            }
            $people->setDepartment($tempDep);
            $em = $this->getDoctrine()->getManager();

            $actualPhones= new ArrayCollection();
            for($i=0;$i<count($actualPhonesAdd);$i++){
                $tempPhone=null;
                if($actualPhonesId[$i]!=""){
                    /** @var Phone $tempPhone */
                    $tempPhone=$phoneRepo->find($actualPhonesId[$i]);
                    if($tempPhone->getPersonPerson()->getEmployee()->getIdEmployee()!=$employee->getIdEmployee()){
                        $view = View::create()->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'1')),
                            'error'=>array('wokplaces'=>'you dont have those phones')))->setStatusCode(400);
                        return $view;
                    }

                }else{
                    $tempPhone=new Phone();
                }
                $tempPhone->setPhoneNumber($actualPhonesAdd[$i]);
                $actualPhones->add($tempPhone);
            }
            $phones = $people->getPhones();
            /** @var Phone $phone */
            foreach($phones as $phone){
                /** @var Phone $actPhone */
                $flag=false;
                foreach($actualPhones as $actPhone){
                    if($phone->getIdPhone()==$actPhone->getIdPhone()){
                        $flag=true;
                        $phone=$actPhone;
                        $actualPhones->removeElement($actPhone);
                        continue;
                    }
                }
                if(!$flag){
                    $phone->setPersonPerson(null);
                    $em->persist($phone);
                    $em->remove($phone);
                    $em->flush();
                    $phones->removeElement($phone);
                }
            }
            foreach($actualPhones as $phone){
                $people->addPhone($phone);
            }

            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));

            if (count($errors) == 0) {
                $em->persist($employee);
                $em->flush();
                $view->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when the requested Ids don't exist"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="employeeType", nullable=false, strict=true, description="employee type.")
     * @RequestParam(name="contractType", nullable=false, strict=true, description="contract type.")
     * @RequestParam(name="timeCommitment", nullable=false, strict=true, description="ammount of time in the job.")
     * @RequestParam(name="position", nullable=false, strict=true, description="labors taht are going to be performed.")
     * @RequestParam(name="salary", nullable=false, strict=true, description="ammount of money gettig paid.")
     * @RequestParam(array=true, name="idsBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(array=true, name="amountBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(array=true, name="periodicityBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="idWorkplace", nullable=false, strict=true, description="place of work.")
     * @RequestParam(name="transportAid", nullable=false, strict=true, description="aid for the employee to transport.")
     * @RequestParam(name="benefitsConditions", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     * @return View
     */
    public function postNewEmployeeSubmitStep3Action(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        /** @var Person $person */
        $person =$user->getPersonPerson();
        $employer=$person->getEmployer();
        if ($employer==null) {
            //TODO Return user to step 1
        }

        //all the data is valid
        if (true) {
            /** @var Employee $employee */
            $employee=null;
            $id=$paramFetcher->get("employeeId");
            /** @var EmployerHasEmployee $employerEmployee */
            $employerEmployee=null;
            $view = View::create();
            if ($id==-1) {
                //TODO Return user to step 2,1
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

            //Create the contract

            $contract=new Contract();
            $contract->setSalary($paramFetcher->get('salary'));
            $contract->setState("Active");

            //contract repos
            $contractTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ContractType');
            $employeeContractTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployeeContractType');
            $timeCommitmentRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:TimeCommitment');
            $positionRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Position');
            $workplaceRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Workplace');
            $benefitRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Benefits');

            $em = $this->getDoctrine()->getManager();

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

            //Workplaces and Benefits
            $benefits=$paramFetcher->get("idsBenefits");
            $benefitsAmount=$paramFetcher->get("amountBenefits");
            $benefitsPeriod=$paramFetcher->get("periodicityBenefits");
            $workplace=$paramFetcher->get("idWorkplace");
            /** @var Workplace $realWorkplace */
            $realWorkplace=$workplaceRepo->find($workplace);
            if($realWorkplace==null){
                $view->setStatusCode(404)->setHeader("error","The workplace ID ".$workplace." is invalid");
                return $view;
            }
            $contract->setWorkplaceWorkplace($realWorkplace);
            for($i=0;$i<count($benefits);$i++){
                /** @var Benefits $realBenefit */
                $realBenefit=$benefitRepo->find($benefits[$i]);
                if($realBenefit==null){
                    $view->setStatusCode(404)->setHeader("error","The Benefit ID ".$benefits[$i]." is invalid");
                    return $view;
                }
                $tempContractHasBenefit=new ContractHasBenefits();
                $tempContractHasBenefit->setAmount($benefitsAmount[$i]);
                $tempContractHasBenefit->setBenefitsBenefits($realBenefit);
                $tempContractHasBenefit->setPeriodicity($benefitsPeriod[$i]);
                $contract->addBenefit($tempContractHasBenefit);
            }
            $contract->setBenefitsConditions($paramFetcher->get('benefitsConditions'));
            $contract->setTransportAid($paramFetcher->get('transportAid'));
            $contracts= $employerEmployee->getContracts();
            /** @var Contract $cont */
            foreach($contracts as $cont){
                $cont->setState("UnActive");
            }
            //turn on current contract
            $contract->setState("Active");
            $employerEmployee->addContract($contract);
            $errors = $this->get('validator')->validate($contract, array('Update'));
            $view = View::create();
            if (count($errors) == 0) {
                $em->persist($employee);
                $em->flush();
                $view->setData(array('response'=>array('idContract'=>$contract->getIdContract())))->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
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