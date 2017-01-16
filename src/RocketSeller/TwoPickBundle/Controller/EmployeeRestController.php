<?php

/**
 * Created by PhpStorm.
 * User: gabrielsamoma
 * Date: 11/17/15
 * Time: 10:43 AM
 */

namespace RocketSeller\TwoPickBundle\Controller;


use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use GuzzleHttp\Psr7\Response;
use RocketSeller\TwoPickBundle\Admin\ContractHasBenefitsAdmin;
use RocketSeller\TwoPickBundle\Entity\AccountType;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\Benefits;
use RocketSeller\TwoPickBundle\Entity\Campaign;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace;
use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\employeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\Frequency;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\ProcedureType;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\UserHasCampaign;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Tests\Authentication\Token\PreAuthenticatedTokenTest;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\Notification;
use DateTime;

class EmployeeRestController extends FOSRestController
{
    use SubscriptionMethodsTrait;

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
     * @param integer $idEmployerHasEmployee
     *
     *
     * @RequestParam(name="$idEmployerHasEmployee", nullable=false, strict=true, description="workplace department.")
     *
     * @return View
     */
    public function getLiquidatePayrollAction($idEmployerHasEmployee)
    {
        $em = $this->getDoctrine()->getManager();
        $repoEmployee = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
        /** @var EmployerHasEmployee $realEmployerHasEmployee */
        $realEmployerHasEmployee = $repoEmployee->find($idEmployerHasEmployee);
        $view = View::create();

        $executionType = "D";
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "employee_id" => $idEmployerHasEmployee,
            "execution_type" => $executionType,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postExecutePayrollLiquidation', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return $view->setStatusCode($insertionAnswer->getStatusCode());
        }

        $contracts = $realEmployerHasEmployee->getContracts();
        $actContract = null;
        /** @var Contract $cont */
        foreach ($contracts as $cont) {
            if ($cont->getState() == 1) {
                $actContract = $cont;
                break;
            }
        }
        $methodToCall = "postExecutePayrollLiquidation";
        $novelties = $actContract->getActivePayroll()->getNovelties();
        /** @var Novelty $nov */
        foreach ($novelties as $nov) {
            if ($nov->getNoveltyTypeNoveltyType()->getPayrollCode() == 145 || $nov->getNoveltyTypeNoveltyType()->getPayrollCode() == 150) {
                $methodToCall = "postExecuteVacationLiquidation";
            }
        }

        $executionType = "P";
        $request->setMethod("POST");
        $request->request->add(array(
            "employee_id" => $idEmployerHasEmployee,
            "execution_type" => $executionType,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:' . $methodToCall, array('_format' => 'json'));

        if ($insertionAnswer->getStatusCode() != 200) {
            return $view->setStatusCode($insertionAnswer->getStatusCode());
        }

        $request->setMethod("GET");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
            "employeeId" => $idEmployerHasEmployee,
        ), array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return $view->setStatusCode($insertionAnswer->getStatusCode());
        }
        return $view->setStatusCode(200)->setData(json_decode($insertionAnswer->getContent(), true));
    }

    /**
     * Change the contract Payment Method.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Change the contract Payment Method.",
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
     * @RequestParam(name="payTypeId", nullable=false, strict=true, description="payment tipe.")
     * @RequestParam(name="bankId", nullable=true, strict=true, description="Bank id.")
     * @RequestParam(name="accountTypeId", nullable=true, strict=true, description="Account type Id.")
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="Account number.")
     * @RequestParam(name="cellphone", nullable=true, strict=true, description="employee cellphone.")
     * @RequestParam(name="hasIt", nullable=true, strict=true, description="emloyee Has daviplata.")
     * @RequestParam(name="contractId", nullable=false, strict=true, description="id of the contract.")
     *
     * @return View
     */
    public function postChangePaymentMethodAction(ParamFetcher $paramFetcher)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        $view = View::create();
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $idContract = $paramFetcher->get("contractId");
        $contract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($idContract);
        if($contract!= null){
            $payMethod = $contract->getPayMethodPayMethod();
            $oldPayMethod = new PayMethod();
            $oldPayType = '';
            if($payMethod != null){
                if($payMethod->getPayTypePayType())
                    $oldPayType = $payMethod->getPayTypePayType()->getSimpleName();
                if($payMethod->getPayTypePayType()) $oldPayMethod->setPayTypePayType($payMethod->getPayTypePayType());
                if($payMethod->getHasIt()) $oldPayMethod->setHasIt($payMethod->getHasIt());
                if($payMethod->getCellPhone()) $oldPayMethod->setCellPhone($payMethod->getCellPhone());
                if($payMethod->getAccountNumber()) $oldPayMethod->setAccountNumber($payMethod->getAccountNumber());
                if($payMethod->getAccountTypeAccountType()) $oldPayMethod->setAccountTypeAccountType($payMethod->getAccountTypeAccountType());
                if($payMethod->getBankBank()) $oldPayMethod->setBankBank($payMethod->getBankBank());
                if($payMethod->getUserUser()) $oldPayMethod->setUserUser($payMethod->getUserUser());
                $payMethod->setHasIt(null);
                $payMethod->setCellPhone(null);
                $payMethod->setAccountTypeAccountType(null);
                $payMethod->setAccountNumber(null);
                $payMethod->setBankBank(null);
                $payMethod->setPayTypePayType(null);
            }else{
                $payMethod = new PayMethod();
            }
            $payMethod->setUserUser($user);
            if ($paramFetcher->get('payTypeId')) {
                /** @var PayType $tempPayType */
                $tempPayType = $em->getRepository("RocketSellerTwoPickBundle:PayType")->find($paramFetcher->get('payTypeId'));
                if ($tempPayType == null) {
                    $payMethod->setHasIt($oldPayMethod->getHasIt());
                    $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                    $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                    $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                    $payMethod->setBankBank($oldPayMethod->getBankBank());
                    $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                    $view->setStatusCode(404)->setHeader("error", "The payTypeId ID " . $paramFetcher->get('payTypeId') . " is invalid");
                    return $view;
                }
                $payMethod->setPayTypePayType($tempPayType);
            }
            switch ($payMethod->getPayTypePayType()->getSimpleName()){
                case 'DAV':
                    /** @var Bank $tempBank */
                    $tempBank = $em->getRepository('RocketSellerTwoPickBundle:Bank')->findOneBy(array("hightechCode" => 51)); //daviviedna bank ofr daviplata
                    $tempAccountType = $em->getRepository("RocketSellerTwoPickBundle:AccountType")->findOneBy(array("name" => "Ahorros")); //tipo de cuenta  bank ofr daviplata
                    if($paramFetcher->get("cellphone") == null or  $paramFetcher->get("cellphone") == '' ){
                        $payMethod->setHasIt($oldPayMethod->getHasIt());
                        $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                        $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                        $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                        $payMethod->setBankBank($oldPayMethod->getBankBank());
                        $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                        $view->setStatusCode(404)->setHeader("error", "cellphone not found in paramfetcher");
                        return $view;
                    }
                    $payMethod->setCellPhone($paramFetcher->get("cellphone"));
                    $payMethod->setBankBank($tempBank);
                    $payMethod->setAccountTypeAccountType($tempAccountType);
                    $payMethod->setHasIt(1);
                    break;
                case 'TRA':
                    if($paramFetcher->get('bankId')){
                        /** @var Bank $tempBank */
                        $tempBank = $em->getRepository('RocketSellerTwoPickBundle:Bank')->find($paramFetcher->get('bankId'));
                        if ($tempBank == null) {
                            $payMethod->setHasIt($oldPayMethod->getHasIt());
                            $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                            $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                            $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                            $payMethod->setBankBank($oldPayMethod->getBankBank());
                            $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                            $view->setStatusCode(404)->setHeader("error", "The bankId ID " . $paramFetcher->get('bankId') . " is invalid");
                            return $view;
                        }
                        $payMethod->setBankBank($tempBank);
                    }else{
                        $payMethod->setHasIt($oldPayMethod->getHasIt());
                        $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                        $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                        $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                        $payMethod->setBankBank($oldPayMethod->getBankBank());
                        $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                        $view->setStatusCode(404)->setHeader("error", "bankId not found in paramfetcher");
                        return $view;
                    }
                    if ($paramFetcher->get('accountTypeId')) {
                        /** @var AccountType $tempAccountType */
                        $tempAccountType = $em->getRepository("RocketSellerTwoPickBundle:AccountType")->find($paramFetcher->get('accountTypeId'));
                        if ($tempAccountType == null) {
                            $payMethod->setHasIt($oldPayMethod->getHasIt());
                            $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                            $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                            $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                            $payMethod->setBankBank($oldPayMethod->getBankBank());
                            $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                            $view->setStatusCode(404)->setHeader("error", "The accountTypeId ID " . $paramFetcher->get('accountTypeId') . " is invalid");
                            return $view;
                        }
                        $payMethod->setAccountTypeAccountType($tempAccountType);
                    }else{
                        $payMethod->setHasIt($oldPayMethod->getHasIt());
                        $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                        $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                        $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                        $payMethod->setBankBank($oldPayMethod->getBankBank());
                        $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                        $view->setStatusCode(404)->setHeader("error", "AccountTypeId not found in paramfetcher");
                        return $view;
                    }
                    if($paramFetcher->get('accountNumber')){
                        $payMethod->setAccountNumber($paramFetcher->get('accountNumber'));
                    }else{
                        $payMethod->setHasIt($oldPayMethod->getHasIt());
                        $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                        $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                        $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                        $payMethod->setBankBank($oldPayMethod->getBankBank());
                        $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                        $view->setStatusCode(404)->setHeader("error", "AccountNumber not found in paramfetcher");
                        return $view;
                    }
                    $payMethod->setCellPhone(0);
                    break;
                case 'EFE':
                    $payMethod->setCellPhone(0);
                    break;
            }
            if($oldPayType != '' and $oldPayType != 'EFE'){
                $remove = $this->removeEmployeeToHighTech($contract->getEmployerHasEmployeeEmployerHasEmployee());
                if(!$remove){
                    $payMethod->setHasIt($oldPayMethod->getHasIt());
                    $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                    $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                    $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                    $payMethod->setBankBank($oldPayMethod->getBankBank());
                    $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                    $view->setStatusCode(404)->setHeader("error", "Error removing from HighTech");
                    return $view;
                }
            }
            if($payMethod->getPayTypePayType()->getSimpleName() != 'EFE'){
                $adding = $this->addEmployeeToHighTech($contract->getEmployerHasEmployeeEmployerHasEmployee());
                if(!$adding){
                    $payMethod->setHasIt($oldPayMethod->getHasIt());
                    $payMethod->setCellPhone($oldPayMethod->getCellPhone());
                    $payMethod->setAccountTypeAccountType($oldPayMethod->getAccountTypeAccountType());
                    $payMethod->setAccountNumber($oldPayMethod->getAccountNumber());
                    $payMethod->setBankBank($oldPayMethod->getBankBank());
                    $payMethod->setPayTypePayType($oldPayMethod->getPayTypePayType());
                    $view->setStatusCode(404)->setHeader("error", "Error adding to HighTech");
                    return $view;
                }
            }
            $contract->setPayMethodPayMethod($payMethod);
            $em->persist($contract);
            $em->persist($payMethod);
            $em->flush();
            $response["new"]=$payMethod;
            $response["old"]=$oldPayMethod;
        }else{
            $view->setStatusCode(404)->setHeader("error", "Contract not found");
            return $view;
        }
        $view->setData($response);
        $view->setStatusCode(200);
        return $view;
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
     * @RequestParam(name="frequencyId", nullable=false, strict=true, description="workplace department.")
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="cellphone", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="hasIt", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="contractId", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="idEmployer", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(array=true, name="register_social_security", nullable=true, strict=true, description="afiliaciones")
     *
     * @return View
     */
    public function postNewEmployeeSubmitAction(ParamFetcher $paramFetcher)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        /** @var User $user */
        $cellphone = intval($paramFetcher->get("cellphone"));
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        /** @var Employee $employee */
        $employee = null;
//        $idContract = $paramFetcher->get("register_social_security");
//        $idEmployer = $idContract['idEmployer'];
        $idContract = $paramFetcher->get("contractId");
        $view = View::create();

        //search the contract
        $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /** @var Contract $contract */
        $contract = $contractRepo->find($idContract);
        if ($contract == null) {
            //TODO return him to steps 2,3
            $view->setStatusCode(403)->setData(array("error" => array('contract' => "You don't have that contract")));
            return $view;
        }
        if ($user->getPersonPerson()->getEmployer()->getIdEmployer() != $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdEmployer()) {
            //TODO return him to step 2
            $view->setStatusCode(403)->setData(array(
                "error" => array('contract' => "You don't have that contract"),
                "ulr" => ""));
            return $view;
        }
        //payMethod repos
        $bankRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Bank');
        $accountTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:AccountType');
        $payTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PayType');
        $frequencyRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Frequency');
        $payMethod = $contract->getPayMethodPayMethod();
        if ($payMethod != null) {
            $payMethod->setCellPhone("");
            $payMethod->setAccountNumber("");
            $payMethod->setBankBank(null);
            $payMethod->setAccountTypeAccountType(null);
            $payMethod->setPayTypePayType(null);
        } else {
            $payMethod = new PayMethod();
        }
        //Now for the payType and Pay Method
        //TODO check if valid??
        $payMethod->setAccountNumber($paramFetcher->get('accountNumber'));
        //TODO check if vaild??
        $payMethod->setCellPhone($cellphone);
        $payMethod->setUserUser($user);

        //check if the Pay Method Ids are valid: Bank payType and AccountType

        if ($paramFetcher->get('bankId')) {
            /** @var Bank $tempBank */
            $tempBank = $bankRepo->find($paramFetcher->get('bankId'));
            if ($tempBank == null) {
                $view->setStatusCode(404)->setHeader("error", "The bankId ID " . $paramFetcher->get('bankId') . " is invalid");
                return $view;
            }
            $payMethod->setBankBank($tempBank);
        } elseif (!($paramFetcher->get('cellphone') == "" || $paramFetcher->get('cellphone') == null)) {
            $tempBank = $bankRepo->findOneBy(array("hightechCode" => 51)); //daviviedna bank ofr daviplata
            $tempaccounttype = $accountTypeRepo->findOneBy(array("name" => "Ahorros")); //tipo de cuenta  bank ofr daviplata
            $payMethod->setBankBank($tempBank);
            $payMethod->setAccountTypeAccountType($tempaccounttype);
        }


        if ($paramFetcher->get('payTypeId')) {
            /** @var PayType $tempPayType */
            $tempPayType = $payTypeRepo->find($paramFetcher->get('payTypeId'));
            if ($tempPayType == null) {
                $view->setStatusCode(404)->setHeader("error", "The payTypeId ID " . $paramFetcher->get('payTypeId') . " is invalid");
                return $view;
            }
            $payMethod->setPayTypePayType($tempPayType);
        }

        if ($paramFetcher->get('accountTypeId')) {
            /** @var AccountType $tempAccountType */
            $tempAccountType = $accountTypeRepo->find($paramFetcher->get('accountTypeId'));
            if ($tempAccountType == null) {
                $view->setStatusCode(404)->setHeader("error", "The accountTypeId ID " . $paramFetcher->get('accountTypeId') . " is invalid");
                return $view;
            }
            $payMethod->setAccountTypeAccountType($tempAccountType);
        }


        $hasIt = $paramFetcher->get("hasIt");
        if ($hasIt != null) {
            $payMethod->setHasIt($hasIt);
        }
        //TODO ADD HAS IT
        // add the user to pay
        // URL used for test porpouses, the line above should be used in production.
        $url_request = "http://localhost:8002/api/public/v1/clients";
        $userPerson = $user->getPersonPerson();
//        /** @var Response $response */
//        $response = $this->get('guzzle.client.api_rest')->post($url_request, $options);
//        if($response->getStatusCode()!=201){
//            $view->setData(array('error'=>array('credit card'=>'something is wrong with the information')))->setStatusCode($response->getStatusCode());
//            return $view;
//        }
//        //finally add the pay method to the contract and add the contract to the EmployerHasEmployee
        // relation that is been created
        //if the CC data is null then add notification to add it

        $contract->setPayMethodPayMethod($payMethod);
        /** @var Frequency $tempFrequency */
        $tempFrequency = $frequencyRepo->find($paramFetcher->get('frequencyId'));
        if ($tempFrequency == null) {
            $view->setStatusCode(404)->setHeader("error", "The frequencyId ID " . $paramFetcher->get('frequencyId') . " is invalid");
            return $view;
        }
        $contract->setFrequencyFrequency($tempFrequency);


        $planillaTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PlanillaType');
        $calculatorConstraintsRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:CalculatorConstraints');
        $minWage = $calculatorConstraintsRepo->findOneBy(array("name" => "smmlv"));
        $minWage = $minWage->getValue();

        $realSalary = 0;
        if ($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD") {
            $realSalary = $contract->getSalary() /*/ $contract->getWorkableDaysMonth()*/
            ;
            //$realSalary = $realSalary * (($contract->getWorkableDaysMonth() / 4) * 4.34523810);
        }

        // Logic to determine the contract planilla type
        if ($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD" && $contract->getSisben() == 1 && $realSalary < $minWage) {
            $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "E"));
            $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
        } else {
            $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "S"));
            $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
        }

        //Final Entity Validation
        $errors = $this->get('validator')->validate($contract, array('Update'));

        if (count($errors) == 0) {
            $employee = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee();
            if ($employee->getRegisterState() == 75) {
                $employee->setRegisterState(95);
                $em->persist($employee);
            }
            $em->persist($contract);
            $em->persist($payMethod);
            $em->flush();
            if ($hasIt == -1) {
                $notifs = $user->getPersonPerson()->getNotifications();
                $flagDavi = true;
                /** @var Notification $nots */
                foreach ($notifs as $nots) {
                    if ($nots->getAccion() == "Crear Daviplata") {
                        $explode = explode("/", $nots->getRelatedLink());
                        if ($explode[2] == $contract->getPayMethodPayMethod()->getIdPayMethod()) {
                            $flagDavi = false;
                            break;
                        }
                    }
                }
                if ($flagDavi) {
                    /** @var UtilsController $utils */
                    $utils = $this->get('app.symplifica_utils');
                    $notification = new Notification();
                    $notification->setPersonPerson($user->getPersonPerson());
                    $notification->activate();
                    $notification->setType('alert');
                    $notification->setDescription("Crear Cuenta DaviPlata para " . $utils->mb_capitalize($employee->getPersonPerson()->getFullName()));
                    $notification->setAccion("Crear Daviplata");
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($notification);
                    $em->flush();
                    $notification->setRelatedLink($this->generateUrl("daviplata_guide", array("idNotification" => $notification->getId(), "payMethodId" => $contract->getPayMethodPayMethod()->getIdPayMethod())));
                    $em->persist($notification);
                    $em->flush();
                }
            }
            //$idContract id del contrato que se esta creando o editando, true para eliminar payroll existentes y dejar solo el nuevo
            $data = $this->forward('RocketSellerTwoPickBundle:Payroll:createPayrollToContract', array(
                'idContract' => $contract->getIdContract(),
                'deleteActivePayroll' => true,
                'period' => null,
                'month' => null,
                'year' => null
            ));
            $view->setData(array('url' => $this->generateUrl('show_dashboard')))->setStatusCode(200);
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        /** @var User $user */
        $user = $this->getUser();
        /** @var Person $people */
        $people = $user->getPersonPerson();
        $employer = $people->getEmployer();
        if ($employer == null) {
            //TODO return user to step 1
        }

        //all the data is valid
        if (true) {
            /** @var Employee $employee */
            $employee = null;
            $id = $paramFetcher->get("employeeId");
            /** @var EmployerHasEmployee $employerEmployee */
            $employerEmployee = null;
            $view = View::create();
            if ($id == -1) {
                $employee = new Employee();
                $person = new Person();
                $employee->setPersonPerson($person);
                $employerEmployee = new EmployerHasEmployee();
                $employerEmployee->setEmployeeEmployee($employee);
                $employerEmployee->setEmployerEmployer($user->getPersonPerson()->getEmployer());
                $employee->addEmployeeHasEmployer($employerEmployee);

            } elseif ($id == -2) {
                $employee = new Employee();
                $person = $people;
                $employee->setPersonPerson($person);
                $employerEmployee = new EmployerHasEmployee();
                $employerEmployee->setEmployeeEmployee($employee);
                $employerEmployee->setEmployerEmployer($user->getPersonPerson()->getEmployer());
                $employee->addEmployeeHasEmployer($employerEmployee);
            } else {
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
                $employee = $repository->find($id);
                //verify if the Id exists or it belongs to the logged user
                if ($employee == null) {
                    $view->setStatusCode(404)->setHeader("error", "The Employee ID " . $paramFetcher->get('employeeId') . " is invalid");
                    $view->setData(array('url' => '/dashboard'));
                    return $view;
                }
                $idEmployer = $user->getPersonPerson()->getEmployer()->getIdEmployer();
                $flag = false;
                /** @var EmployerHasEmployee $ee */
                foreach ($employee->getEmployeeHasEmployers() as $ee) {
                    if ($ee->getEmployerEmployer()->getIdEmployer() == $idEmployer) {
                        if ($ee->getState() == -1)
                            $ee->setState(0);
                        $employerEmployee = $ee;
                        $flag = true;
                        break;
                    }
                }
//              @todo Gabriel revisar porque está generando entradas duplicadas en la BD en employer_has_employee

                if (!$flag && ($employee->getPersonPerson()->getDocumentType() == $paramFetcher->get('documentType') && $employee->getPersonPerson()->getDocument() == $paramFetcher->get('document'))) {
                    $flag = true;
                    $employerEmployee = new EmployerHasEmployee();
                    $employerEmployee->setEmployeeEmployee($employee);
                    $employerEmployee->setEmployerEmployer($user->getPersonPerson()->getEmployer());
                    $employee->addEmployeeHasEmployer($employerEmployee);
                }
                if (!$flag) {
                    $view->setStatusCode(403)->setHeader("error", "Not your Employee");
                    $view->setData(array('url' => '/dashboard'));
                    return $view;
                }
            }

            $employerEmployee->setIsFree($user->getIsFree());

            /** @var Person $person */
            $person = $employee->getPersonPerson();

            /** @var UtilsController $utils */
            $utils = $this->get('app.symplifica_utils');

            $person->setNames($utils->mb_capitalize($paramFetcher->get('names')));
            $person->setLastName1($utils->mb_capitalize($paramFetcher->get('lastName1')));
            $person->setLastName2($utils->mb_capitalize($paramFetcher->get('lastName2')));
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
            $cityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $conRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Country');

            $tempBCity = $cityRepo->find($paramFetcher->get('birthCity'));
//             @todo No es un campo obligatorio para el registro
//             if ($tempBCity == null) {
//                 $view->setStatusCode(404)->setHeader("error", "The city ID " . $paramFetcher->get('birthCity') . " is invalid");
//                 return $view;
//             }
            $person->setBirthCity($tempBCity);
            $tempBDep = $depRepo->find($paramFetcher->get('birthDepartment'));
//             @todo No es un campo obligatorio para el registro
//             if ($tempBDep == null) {
//                 $view->setStatusCode(404)->setHeader("error", "The department ID " . $paramFetcher->get('birthDepartment') . " is invalid");
//                 return $view;
//             }
            $person->setBirthDepartment($tempBDep);
            $tempBCou = $conRepo->find($paramFetcher->get('birthCountry'));
//             @todo No es un campo obligatorio para el registro
//             if ($tempBCou == null) {
//                 $view->setStatusCode(404)->setHeader("error", "The country ID " . $paramFetcher->get('birthCountry') . " is invalid");
//                 return $view;
//             }
            $person->setBirthCountry($tempBCou);
            $em = $this->getDoctrine()->getManager();
            $errors = $this->get('validator')->validate($employee, array('Update'));
            if (count($errors) == 0) {
                if ($employee->getRegisterState() == 0) {
                    $employee->setRegisterState(25);
                }
                if ($employerEmployee->getLegalFF() == -1) {
                    $employerEmployee->setLegalFF($user->getLegalFlag());
                }
                $user->setLegalFlag(-1);

                $configToDeleteArr = $user->getPersonPerson()->getConfigurations();
                foreach ($configToDeleteArr as $cr) {
                    $employee->getPersonPerson()->removeConfiguration($cr);
                    $employee->getPersonPerson()->addConfiguration($cr);
                    $user->getPersonPerson()->removeConfiguration($cr);
                }

                $em->persist($user);
                $em->persist($employerEmployee);
                $em->persist($employee);
                $em->flush();
                $view->setData(array('response' => array('idEmployee' => $employee->getIdEmployee())))->setStatusCode(200);
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        $user = $this->getUser();
        /** @var Person $person */
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();
        if ($employer == null) {
            //TODO Return user to step 1
        }

        //all the data is valid
        if (true) {
            /** @var Employee $employee */
            $employee = null;
            $id = $paramFetcher->get("employeeId");
            /** @var EmployerHasEmployee $employerEmployee */
            $employerEmployee = null;
            $view = View::create();
            if ($id == -1) {
                //TODO Return user to step 2,1
            } else {
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
                $employee = $repository->find($id);
                //verify if the Id exists or it belongs to the logged user
                $idEmployer = $user->getPersonPerson()->getEmployer()->getIdEmployer();
                $flag = false;
                /** @var EmployerHasEmployee $ee */
                foreach ($employee->getEmployeeHasEmployers() as $ee) {
                    if ($ee->getEmployerEmployer()->getIdEmployer() == $idEmployer) {
                        $employerEmployee = $ee;
                        $flag = true;
                        break;
                    }
                }
                if ($employee == null || !$flag) {
                    $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                    return $this->render(
                        'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig', array(
                        'employees' => $employeesData));
                }
            }
            $people = $employerEmployee->getEmployeeEmployee()->getPersonPerson();
            $people->setMainAddress($paramFetcher->get('mainAddress'));
            $people->setEmail($paramFetcher->get('email'));
            $phoneRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Phone');
            $cityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $actualPhonesId = $paramFetcher->get('phonesIds');
            $actualPhonesAdd = $paramFetcher->get('phones');
            $tempCity = $cityRepo->find($paramFetcher->get('city'));
            if ($tempCity == null) {
                $view->setStatusCode(404)->setHeader("error", "The city ID " . $paramFetcher->get('city') . " is invalid");
                return $view;
            }
            $people->setCity($tempCity);
            $tempDep = $depRepo->find($paramFetcher->get('department'));
            if ($tempDep == null) {
                $view->setStatusCode(404)->setHeader("error", "The department ID " . $paramFetcher->get('department') . " is invalid");
                return $view;
            }
            $people->setDepartment($tempDep);
            $em = $this->getDoctrine()->getManager();

            $actualPhones = new ArrayCollection();
            for ($i = 0; $i < count($actualPhonesAdd); $i++) {
                $tempPhone = null;
                if ($actualPhonesId[$i] != "") {
                    /** @var Phone $tempPhone */
                    $tempPhone = $phoneRepo->find($actualPhonesId[$i]);
                    if ($tempPhone->getPersonPerson()->getEmployee()->getIdEmployee() != $employee->getIdEmployee()) {
                        $view = View::create()->setData(array('url' => $this->generateUrl('edit_profile', array('step' => '1')),
                            'error' => array('wokplaces' => 'you dont have those phones')))->setStatusCode(400);
                        return $view;
                    }
                } else {
                    $tempPhone = new Phone();
                }
                $tempPhone->setPhoneNumber($actualPhonesAdd[$i]);
                $actualPhones->add($tempPhone);
            }
            $phones = $people->getPhones();
            /** @var Phone $phone */
            foreach ($phones as $phone) {
                /** @var Phone $actPhone */
                $flag = false;
                foreach ($actualPhones as $actPhone) {
                    if ($phone->getIdPhone() == $actPhone->getIdPhone()) {
                        $flag = true;
                        $phone = $actPhone;
                        $actualPhones->removeElement($actPhone);
                        continue;
                    }
                }
                if (!$flag) {
                    $phone->setPersonPerson(null);
                    $em->persist($phone);
                    $em->remove($phone);
                    $em->flush();
                    $phones->removeElement($phone);
                }
            }
            foreach ($actualPhones as $phone) {
                $people->addPhone($phone);
            }

            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));

            if (count($errors) == 0) {
                if ($employee->getRegisterState() == 25) {
                    $employee->setRegisterState(50);
                }
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
     * @RequestParam(name="contractType", nullable=false, strict=true, description="contract type.")
     * @RequestParam(name="timeCommitment", nullable=false, strict=true, description="ammount of time in the job.")
     * @RequestParam(name="position", nullable=false, strict=true, description="labors taht are going to be performed.")
     * @RequestParam(name="salary", nullable=true, strict=true, description="ammount of money gettig paid.")
     * @RequestParam(name="salaryD", nullable=true, strict=true, description="ammount of money gettig paid daily.")
     * @RequestParam(array=true, name="idsBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(array=true, name="benefType", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(array=true, name="amountBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(array=true, name="periodicityBenefits", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="idWorkplace", nullable=false, strict=true, description="place of work.")
     * @RequestParam(name="transportAid", nullable=true, strict=true, description="aid for the employee to transport.")
     * @RequestParam(name="worksSat", nullable=true, strict=true, description="if the employee works on saturdays or not.")
     * @RequestParam(name="sisben", nullable=true, strict=true, description="employee belongs to SISBEN.")
     * @RequestParam(name="benefitsConditions", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(name="startDate", nullable=false, strict=true, description="benefits conditions.")
     * @RequestParam(name="endDate", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(name="workableDaysMonth", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(array=true, name="workTimeStart", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(array=true, name="workTimeEnd", nullable=true, strict=true, description="benefits conditions.")
     * @RequestParam(name="weekWorkableDays", nullable=true, strict=true, description="days the employee will work per week.")
     * @RequestParam(array=true,name="weekDays", nullable=true, strict=true, description="days the employee will work per week.")
     * @RequestParam(name="employeeId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(name="contractId", nullable=true, strict=true, description="id of the contract.")
     * @RequestParam(name="holidayDebt", nullable=true, strict=true, description="vacations days that are unpaid.")
     * @return View
     */
    public function postNewEmployeeSubmitStep3Action(ParamFetcher $paramFetcher)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        $user = $this->getUser();
        /** @var Person $person */
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();
        if ($employer == null) {
            //TODO Return user to step 1
        }

        //all the data is valid
        if (true) {
            /** @var Employee $employee */
            $employee = null;
            $id = $paramFetcher->get("employeeId");
            /** @var EmployerHasEmployee $employerEmployee */
            $employerEmployee = null;
            $view = View::create();
            if ($id == -1) {
                //TODO Return user to step 2,1
            } else {
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
                $employee = $repository->find($id);
                //verify if the Id exists or it belongs to the logged user
                $idEmployer = $user->getPersonPerson()->getEmployer()->getIdEmployer();
                $flag = false;
                /** @var EmployerHasEmployee $ee */
                foreach ($employee->getEmployeeHasEmployers() as $ee) {
                    if ($ee->getEmployerEmployer()->getIdEmployer() == $idEmployer) {
                        $employerEmployee = $ee;
                        $flag = true;
                        break;
                    }
                }
                if ($employee == null || !$flag) {
                    $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                    return $this->render(
                        'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig', array(
                        'employees' => $employeesData));
                }
            }
            $idContract = $paramFetcher->get("contractId");
            //search the contract
            $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
            /** @var Contract $contract */
            $contract = $contractRepo->find($idContract);
            if ($contract == null) {
                //Create the contract
                $contract = new Contract();
                $contract->setState(1);
                $employerEmployee->addContract($contract);
            }
            if ($user->getPersonPerson()->getEmployer()->getIdEmployer() != $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdEmployer()) {
                //TODO return him to step 2
                $view->setStatusCode(403)->setData(array(
                    "error" => array('contract' => "You don't have that contract"),
                    "ulr" => ""));
                return $view;
            }


            //contract repos
            $contractTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ContractType');
            //$employeeContractTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployeeContractType');
            $timeCommitmentRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:TimeCommitment');
            $positionRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Position');
            $workplaceRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Workplace');
            $benefitRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Benefits');
            $contracHasBenefitRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ContractHasBenefits');

            $em = $this->getDoctrine()->getManager();

            /** @var ContractType $tempContractType */
            $tempContractType = $contractTypeRepo->findOneBy(array("payroll_code" => $paramFetcher->get('contractType')));
            if ($tempContractType == null) {
                $view->setStatusCode(404)->setHeader("error", "The contractType ID " . $paramFetcher->get('contractType') . " is invalid");
                return $view;
            }
            $contract->setContractTypeContractType($tempContractType);

            /* $tempEmployeeContractType = $employeeContractTypeRepo->find($paramFetcher->get('employeeType'));
              if ($tempEmployeeContractType == null) {
              $view->setStatusCode(404)->setHeader("error", "The employeeType ID " . $paramFetcher->get('employeeType') . " is invalid");
              return $view;
              }
              $contract->setEmployeeContractTypeEmployeeContractType($tempEmployeeContractType); */

            $tempPosition = $positionRepo->find($paramFetcher->get('position'));
            if ($tempPosition == null) {
                $view->setStatusCode(404)->setHeader("error", "The position ID " . $paramFetcher->get('position') . " is invalid");
                return $view;
            }
            $contract->setPositionPosition($tempPosition);

            $tempTimeCommitment = $timeCommitmentRepo->find($paramFetcher->get('timeCommitment'));
            if ($tempTimeCommitment == null) {
                $view->setStatusCode(404)->setHeader("error", "The timeCommitment ID " . $paramFetcher->get('timeCommitment') . " is invalid");
                return $view;
            }
            $contract->setTimeCommitmentTimeCommitment($tempTimeCommitment);

            $startDate = $paramFetcher->get('startDate');
            $endDate = null;
            $datetime = new DateTime($startDate);
            $contract->setStartDate($datetime);
            $contract->setEndDate($endDate);
            $contract->setHolidayDebt($paramFetcher->get("holidayDebt"));
            /* $workTimeStart = $paramFetcher->get('workTimeStart');
              $datetime = new DateTime();
              $datetime->setTime($workTimeStart['hour'], $workTimeStart['minute']);
              $contract->setWorkTimeStart($datetime);

              $workTimeEnd = $paramFetcher->get('workTimeEnd');
              $datetime = new DateTime();
              $datetime->setTime($workTimeEnd['hour'], $workTimeEnd['minute']);
              $contract->setWorkTimeEnd($datetime); */

            if ($contract->getContractTypeContractType()->getName() == "Término fijo") {
                $endDate = $paramFetcher->get('endDate');
                $datetime = new DateTime($endDate);
                $contract->setEndDate($datetime);
            }
            if ($contract->getTimeCommitmentTimeCommitment()->getName() == "Trabajador por días") {
                $actualWeekWorkableDayss = $paramFetcher->get('weekWorkableDays');
                $actualWeekWorkableDays = $paramFetcher->get('weekDays');
                $sisben = $paramFetcher->get('sisben');
                $contract->setWorkableDaysMonth($actualWeekWorkableDayss * 4);
                $contract->setSisben($sisben);
                $contract->setSalary($paramFetcher->get('salaryD') * $actualWeekWorkableDayss * 4);

                $workableDays = $contract->getWeekWorkableDays();
                foreach ($workableDays as $workableDay) {

                    $flagActualRemove = true;
                    $ifFound = null;
                    /** @var WeekWorkableDays $workableDay */
                    foreach ($actualWeekWorkableDays as $key => $value) {
                        if ($workableDay->getDayName() == $value) {
                            $flagActualRemove = false;
                            $ifFound = $key;
                            break;
                        }
                    }
                    if ($flagActualRemove) {
                        $contract->removeWeekWorkableDay($workableDay);
                        $em->remove($workableDay);
                        continue;
                    } else {
                        unset($actualWeekWorkableDays[$ifFound]);
                    }
                }
                foreach ($actualWeekWorkableDays as $key => $value) {
                    $weekWorkableDay = new WeekWorkableDays();
                    $weekWorkableDay->setContractContract($contract);
                    $weekWorkableDay->setDayName($value);
                    $dayNumber = 0;
                    switch ($value) {
                        case "lunes":
                            $dayNumber = 1;
                            break;
                        case "martes":
                            $dayNumber = 2;
                            break;
                        case "miercoles":
                            $dayNumber = 3;
                            break;
                        case "jueves":
                            $dayNumber = 4;
                            break;
                        case "viernes":
                            $dayNumber = 5;
                            break;
                        case "sabado":
                            $dayNumber = 6;
                            break;
                        case "domingo":
                            $dayNumber = 0;
                            break;
                    }
                    $weekWorkableDay->setDayNumber($dayNumber);
                    $contract->addWeekWorkableDay($weekWorkableDay);
                }

                $contract->setWorkableDaysMonth($contract->getWeekWorkableDays()->count() * 4);
            } else {
                $contract->setSalary($paramFetcher->get('salary'));
                $contract->setWorksSaturday($paramFetcher->get('worksSat'));
                $contract->setWorkableDaysMonth(30);
                $contract->setTransportAid($paramFetcher->get('transportAid'));
            }

            //Workplaces and Benefits
            $benefits = $paramFetcher->get("idsBenefits");
            $benefitsType = $paramFetcher->get("benefType");
            $benefitsAmount = $paramFetcher->get("amountBenefits");
            $benefitsPeriod = $paramFetcher->get("periodicityBenefits");
            $workplace = $paramFetcher->get("idWorkplace");
            /** @var Workplace $realWorkplace */
            $realWorkplace = $workplaceRepo->find($workplace);

            if ($realWorkplace == null) {
                $view->setStatusCode(404)->setHeader("error", "The workplace ID " . $workplace . " is invalid");
                return $view;
            }
            $contract->setWorkplaceWorkplace($realWorkplace);
            /* $contractHasBenefits = $contract->getBenefits();
              $idsCHB = array();
              /** @var ContractHasBenefits $contractHasBenefit
              foreach ($contractHasBenefits as $contractHasBenefit) {
              $idsCHB[$contractHasBenefit->getIdContractHasBenefits()] = $contractHasBenefit->getIdContractHasBenefits();
              }
              for ($i = 0; $i < count($benefitsType); $i++) {
              $realContractHasBenefit = null;
              $flagExist = false;
              if ($benefits[$i] != null) {
              $flagExist = true;
              /** @var ContractHasBenefits $realContractHasBenefit
              $realContractHasBenefit = $contracHasBenefitRepo->find($benefits[$i]);
              unset($idsCHB[$realContractHasBenefit->getIdContractHasBenefits()]);
              }
              /** @var Benefits $realBenefit
              $realBenefit = $benefitRepo->find($benefitsType[$i]);
              if ($realBenefit == null) {
              $view->setStatusCode(404)->setHeader("error", "The Benefit ID " . $benefitsType[$i] . " is invalid");
              return $view;
              }
              if (!$flagExist) {
              $realContractHasBenefit = new ContractHasBenefits();
              }
              $realContractHasBenefit->setAmount($benefitsAmount[$i]);
              $realContractHasBenefit->setBenefitsBenefits($realBenefit);
              $realContractHasBenefit->setPeriodicity($benefitsPeriod[$i]);
              if (!$flagExist) {
              $contract->addBenefit($realContractHasBenefit);
              }
              }
              foreach ($idsCHB as $key => $value) {
              $toRemove = $contracHasBenefitRepo->find($value);
              $em->remove($toRemove);
              }
              $contract->setBenefitsConditions($paramFetcher->get('benefitsConditions')); */
            //turn on current contract
            $contract->setState(1);

            $errors = $this->get('validator')->validate($contract, array('Update'));
            $view = View::create();
            if (count($errors) == 0) {

                if ($contract->getContractTypeContractType()->getPayrollCode() == 1) {
                    $endTestPeriod = new DateTime(date('Y-m-d', strtotime('+2 month', strtotime($contract->getStartDate()->format("Y-m-d")))));
                } else {
                    /** @var DateTime $endTestPeriod2 */
                    $endTestPeriod2 = new DateTime(date('Y-m-d', strtotime('+' . intval($contract->getStartDate()->diff($contract->getEndDate())->format("%a") / 5) . ' day', strtotime($contract->getStartDate()->format("Y-m-d")))));
                    /** @var DateTime $endTestPeriod */
                    $endTestPeriod = new DateTime(date('Y-m-d', strtotime('+2 month', strtotime($contract->getStartDate()->format("Y-m-d")))));
                    if ($endTestPeriod2 < $endTestPeriod) {
                        $endTestPeriod = $endTestPeriod2;
                    }
                }
                $contract->setTestPeriod($endTestPeriod);
                if ($employee->getRegisterState() == 50) {
                    $employee->setRegisterState(75);
                }
                $em->persist($employee);
                $em->flush();
                $view->setData(array('response' => array('idContract' => $contract->getIdContract())))->setStatusCode(200);

                $this->addFlash(
                    'notice', 'Your changes were saved!'
                );
                return $view;
            } else {
                $this->addFlash(
                    'error', 'Your changes were NO saved!'
                );
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }

    /**
     * Save edit contract.<br/>
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
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="cellphone", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="hasIt", nullable=true, strict=true, description="workplace department.")
     * @RequestParam(name="contractId", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(name="idEmployer", nullable=false, strict=true, description="id of the contract.")
     * @RequestParam(array=true, name="register_social_security", nullable=true, strict=true, description="afiliaciones")
     *
     * @return View
     */
    public function postSaveEditContractAction(ParamFetcher $paramFetcher)
    {
        $view = View::create();
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        $em = $this->getDoctrine()->getManager();

        $idContract = $paramFetcher->get("contractId");

        //search the contract
        $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /** @var Contract $contract */
        $contract = $contractRepo->find($idContract);
        if ($contract == null) {
            $view->setStatusCode(403)->setData(array("error" => array('contract' => "You don't have that contract")));
            return $view;
        }
        //payMethod repos
        $bankRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Bank');
        $accountTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:AccountType');
        $payTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PayType');
        $frequencyRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Frequency');
        $payMethod = $contract->getPayMethodPayMethod();
        if ($payMethod != null) {
            $payMethod->setCellPhone("");
            $payMethod->setAccountNumber("");
            $payMethod->setBankBank(null);
            $payMethod->setAccountTypeAccountType(null);
            $payMethod->setPayTypePayType(null);
        } else {
            $payMethod = new PayMethod();
        }


        if ($paramFetcher->get('bankId')) {
            /** @var Bank $tempBank */
            $tempBank = $bankRepo->find($paramFetcher->get('bankId'));
            if ($tempBank == null) {
                $view->setStatusCode(404)->setHeader("error", "The bankId ID " . $paramFetcher->get('bankId') . " is invalid");
                return $view;
            }
            $payMethod->setAccountNumber($paramFetcher->get('accountNumber'));
            $payMethod->setBankBank($tempBank);
        } elseif (!($paramFetcher->get('cellphone') == "" || $paramFetcher->get('cellphone') == null)) {
            $tempBank = $bankRepo->findOneBy(array("hightechCode" => 51)); //daviviedna bank ofr daviplata
            $tempaccounttype = $accountTypeRepo->findOneBy(array("name" => "Ahorros")); //tipo de cuenta  bank ofr daviplata
            $payMethod->setCellPhone($paramFetcher->get('cellphone'));
            $payMethod->setBankBank($tempBank);
            $payMethod->setAccountTypeAccountType($tempaccounttype);
        }


        if ($paramFetcher->get('payTypeId')) {
            /** @var PayType $tempPayType */
            $tempPayType = $payTypeRepo->find($paramFetcher->get('payTypeId'));
            if ($tempPayType == null) {
                $view->setStatusCode(404)->setHeader("error", "The payTypeId ID " . $paramFetcher->get('payTypeId') . " is invalid");
                return $view;
            }
            $payMethod->setPayTypePayType($tempPayType);
        }

        if ($paramFetcher->get('accountTypeId')) {
            /** @var AccountType $tempAccountType */
            $tempAccountType = $accountTypeRepo->find($paramFetcher->get('accountTypeId'));
            if ($tempAccountType == null) {
                $view->setStatusCode(404)->setHeader("error", "The accountTypeId ID " . $paramFetcher->get('accountTypeId') . " is invalid");
                return $view;
            }
            $payMethod->setAccountTypeAccountType($tempAccountType);
        }

        $contract->setPayMethodPayMethod($payMethod);

        $planillaTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PlanillaType');
        $calculatorConstraintsRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:CalculatorConstraints');
        $minWage = $calculatorConstraintsRepo->findOneBy(array("name" => "smmlv"));
        $minWage = $minWage->getValue();

        $realSalary = 0;
        if ($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD") {
            $realSalary = $contract->getSalary() /*/ $contract->getWorkableDaysMonth()*/
            ;
            //$realSalary = $realSalary * (($contract->getWorkableDaysMonth() / 4) * 4.34523810);
        }

        // Logic to determine the contract planilla type
        if ($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD" && $contract->getSisben() == 1 && $realSalary < $minWage) {
            $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "E"));
            $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
        } else {
            $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "S"));
            $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
        }

        $em->persist($contract);
        $em->persist($payMethod);
        $em->flush();
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * Save data<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Save data.",
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
     * @RequestParam(array=true, name="register_social_security", nullable=true, strict=true, description="afiliaciones")
     *
     * @return View
     */
    public function postMatrixChooseSubmitAction(ParamFetcher $paramFetcher)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $view = View::create();
            return $view->setStatusCode(401)->setData(array("error" => array("login" => "El usuario no está logeado"), "url" => $this->generateUrl("fos_user_security_login")));
        }
        $flag = false;
        $save3 = $this->saveMatrixChooseSubmitStep3($paramFetcher);
        if ($save3->getData('response')['response']['message'] == 'added') {
            /** @var User $user */
            $user = $this->getUser();
            $user->setStatus(2);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $flag = true;
        } else {
            return $save3;
        }

        if ($flag) {
            //return $this->forward('RocketSellerTwoPickBundle:Default:subscriptionChoices');
            return $this->redirectToRoute('ajax');
            //$view->setData(array('url' => $this->generateUrl('subscription_choices')))->setStatusCode(200);
        } else {
            $view = View::create();
            $view->setData(array('response' => array('message' => 'something went wrong')))->setStatusCode(400);
            return $view;
        }
    }

    private function saveMatrixChooseSubmitStep3(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user == null) {
            $view = View::create();
            $view->setData(array('error' => array('employee' => 'user not logged')))->setStatusCode(403);
            return $view;
        }

        $register_social_security = $paramFetcher->get("register_social_security");

        $employerHasEmployeeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $employerHasEmployees = $register_social_security['employerHasEmployees'];
        $idEmployer = $register_social_security['idEmployer'];

        foreach ($employerHasEmployees as $idEmployerHasEmployee) {
            /** @var EmployerHasEmployee $realEmployerHasEmployee */
            $realEmployerHasEmployee = $employerHasEmployeeRepo->find($idEmployerHasEmployee);
            $realEmployeer = $realEmployerHasEmployee->getEmployerEmployer();
            if ($idEmployer == $realEmployeer->getIdEmployer()) {
                $this->validateDocumentsEmployee2($realEmployerHasEmployee->getEmployeeEmployee());
                $this->validateEntitiesEmployee2($realEmployerHasEmployee->getEmployeeEmployee());
            } else {
                $view = View::create();
                $view->setData(array('error' => array('employee' => 'do not contain')))->setStatusCode(401);
                return $view;
            }
        }

        $this->validateDocumentsEmployerr($idEmployer);

        $view = View::create();
        $view->setData(array('response' => array('message' => 'added')))->setStatusCode(200);
        return $view;
    }

    private function validateEntitiesEmployee2($realEmployee)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $personEmployee = $realEmployee->getPersonPerson();
        $employeeHasEntityRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity');
        $entities_b = $employeeHasEntityRepo->findByEmployeeEmployee($realEmployee);
        if (gettype($entities_b) != "array") {
            $entities[] = $entities;
        } else {
            $entities = $entities_b;
        }
        //foreach ($entities as $key => $value) {
        $msj = "Subir documentos de " . $personEmployee->getFullName() . " para afiliarlo a las entidades.";
        $url = $this->generateUrl("show_documents", array('id' => $personEmployee->getIdPerson()));
        $this->createNotification2($user->getPersonPerson(), $msj, $url);
        //}
    }

    private function validateDocumentsEmployee2($realEmployee)
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        /** @var Person $person */
        $person = $realEmployee->getPersonPerson();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);

        $docs = array('Cedula' => false, 'Contrato' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " . $person->getFullName();
                    $documentType = 'Cedula';
                } elseif ($type == 'Contrato') {
                    $msj = "Subir copia del contrato de " . $person->getFullName();
                    $documentType = 'Contrato';
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification2($user->getPersonPerson(), $msj, $url);
            }
        }
    }

    private function validateDocumentsEmployerr($idEmployer)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $employerRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer');
        $realEmployer = $employerRepo->find($idEmployer);
        $person = $realEmployer->getPersonPerson();

        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);

        $docs = array('Cedula' => false, 'RUT' => false, 'Carta autorización Symplifica' => false, 'Cédula de extranjería' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " . $person->getFullName();
                    $documentType = 'Cedula';
                } elseif ($type == 'RUT') {
                    $msj = "Subir copia del RUT de " . $person->getFullName();
                    $documentType = 'Contrato';
                } elseif ($type == 'Carta autorización Symplifica') {
                    $msj = "Subir carta de autorización Symplifica de " . $person->getFullName();
                    $documentType = 'Carta autorización Symplifica';
                } elseif ($type == 'Cédula de extranjería'){
                	  $msj = "Subir copia del documento de identidad de " . $person->getFullName();
	                  $documentType = 'Cédula de extranjería';
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification2($user->getPersonPerson(), $msj, $url);
            }
        }
    }

    private function createNotification2($person, $descripcion, $url, $action = "Subir")
    {
        $notification = new Notification();
        $notification->setPersonPerson($person);
        $notification->activate();
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
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
     * @RequestParam(name="idContract", nullable=true, strict=true, description="employee type.")
     * @RequestParam(name="idEmployee", nullable=false, strict=true, description="benefits of the employee.")
     * @RequestParam(name="beneficiaries", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="pension", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="pensionExists", nullable=true, strict=true, description="checks if already have pension.")
     * @RequestParam(name="wealth", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="wealthExists", nullable=true, strict=true, description="checks if already have wealth.")
     * @RequestParam(name="ars", nullable=true, strict=true, description="benefits of the employee.")
     * @RequestParam(name="severances", nullable=false, strict=true, description="benefits of the employee.")
     * @RequestParam(name="severancesExists", nullable=false, strict=true, description="checks if already have severances.")
     * @return View
     */
    public function postMatrixChooseSubmitStep1Action(ParamFetcher $paramFetcher)
    {
        return $this->saveMatrixChooseSubmitStep1($paramFetcher);
    }

    private function saveMatrixChooseSubmitStep1(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($user == null) {
            $view = View::create();
            $view->setData(array('error' => array('employee' => 'user not logged')))->setStatusCode(403);
            return $view;
        }

        $contractRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        $entityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Entity');
        $realEmployer = $user->getPersonPerson()->getEmployer();

        $flag = false;
        /** @var Entity $tempPens */
        $tempPens = $entityRepo->find($paramFetcher->get('pension'));
        $pensionExists = $paramFetcher->get('pensionExists');

        /** @var Entity $tempWealth */
        $tempWealth = $entityRepo->find($paramFetcher->get('wealth'));
        $wealthExists = $paramFetcher->get('wealthExists');

        /** @var Entity $tempArs */
        //$tempArs = $entityRepo->find($paramFetcher->get('ars'));

        /** @var Entity $tempSeverances */
        $tempSeverances = $entityRepo->find($paramFetcher->get('severances'));
        $severancesExists = $paramFetcher->get('severancesExists');

        $beneficiarie = $paramFetcher->get('beneficiaries');

        if ($tempPens == null /*|| ( $tempWealth == null && $tempArs == null)*/ || $tempSeverances == null) {
            $view = View::create();
            $view->setData(array('error' => array('entity' => 'do not exist')))->setStatusCode(404);
            return $view;
        }
        /** @var Contract $contract */
        $contract = $contractRepo->find($paramFetcher->get("idContract"));

        $realEmployerHasEmployee = $contract->getEmployerHasEmployeeEmployerHasEmployee();
        if ($realEmployerHasEmployee->getEmployerEmployer()->getIdEmployer() == $realEmployer->getIdEmployer()) {
            $realEmployee = $realEmployerHasEmployee->getEmployeeEmployee();
        } else {
            $view = View::create();
            $view->setData(array('error' => array('employee' => 'do not contain')))->setStatusCode(401);
            return $view;
        }
        $realEmployeeEnt = $realEmployee->getEntities();

        if ($realEmployeeEnt->count() == 0) {

            $employeeHasEntityPens = new EmployeeHasEntity();
            $employeeHasEntityPens->setEmployeeEmployee($realEmployee);
            $employeeHasEntityPens->setEntityEntity($tempPens);
            $employeeHasEntityPens->setState($pensionExists);
            $realEmployee->addEntity($employeeHasEntityPens);
            $em->persist($employeeHasEntityPens);

            $employeeHasEntityCes = new EmployeeHasEntity();
            $employeeHasEntityCes->setEmployeeEmployee($realEmployee);
            $employeeHasEntityCes->setEntityEntity($tempSeverances);
            $employeeHasEntityCes->setState($severancesExists);
            $realEmployee->addEntity($employeeHasEntityCes);
            $em->persist($employeeHasEntityCes);

            if ($tempWealth != null) {
                $employeeHasEntityWealth = new EmployeeHasEntity();
                $employeeHasEntityWealth->setEmployeeEmployee($realEmployee);
                $employeeHasEntityWealth->setEntityEntity($tempWealth);
                $employeeHasEntityWealth->setState($wealthExists);
                $realEmployee->addEntity($employeeHasEntityWealth);
                $em->persist($employeeHasEntityWealth);
            } else {
                $employeeHasEntityARS = new EmployeeHasEntity();
                $employeeHasEntityARS->setEmployeeEmployee($realEmployee);
                $localARSRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EntityType');
                $localARS = $localARSRepo->findOneBy(array('payroll_code' => 'ARS'));
                $employeeHasEntityARS->setEntityEntity($localARS->getEntities()->get(0));
                $employeeHasEntityARS->setState(-1);
                $realEmployee->addEntity($employeeHasEntityARS);
                $em->persist($employeeHasEntityARS);
            }


            $realEmployee->setAskBeneficiary($beneficiarie);
            $em->persist($realEmployee);

            $em->flush();
            $flag = true;
        } else {
            /** @var EmployeeHasEntity $rEE */
            foreach ($realEmployeeEnt as $rEE) {
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "EPS") {
                    if ($tempWealth != null) {
                        $rEE->setEntityEntity($tempWealth);
                        $rEE->setState($wealthExists);
                        $em->persist($rEE);
                    } else {
                        $realEmployee->removeEntity($rEE);
                        $em->remove($rEE);
                        $em->flush();
                        $employeeHasEntityARS = new EmployeeHasEntity();
                        $employeeHasEntityARS->setEmployeeEmployee($realEmployee);
                        $localARSRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EntityType');
                        $localARS = $localARSRepo->findOneBy(array('payroll_code' => 'ARS'));
                        $employeeHasEntityARS->setEntityEntity($localARS->getEntities()->get(0));
                        $employeeHasEntityARS->setState(-1);
                        $realEmployee->addEntity($employeeHasEntityARS);
                        $em->persist($employeeHasEntityARS);
                    }
                }
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "ARS" && $tempWealth != null) {
                    /*if ($tempArs != null) {
                        $rEE->setEntityEntity($tempArs);
                        $em->persist($rEE);
                    } else {*/
                    $em->remove($rEE);
                    $em->flush();
                    $employeeHasEntityWealth = new EmployeeHasEntity();
                    $employeeHasEntityWealth->setEmployeeEmployee($realEmployee);
                    $employeeHasEntityWealth->setEntityEntity($tempWealth);
                    $employeeHasEntityWealth->setState($wealthExists);
                    $realEmployee->addEntity($employeeHasEntityWealth);
                    $em->persist($employeeHasEntityWealth);
                    //}
                }
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "AFP") {
                    $rEE->setEntityEntity($tempPens);
                    $rEE->setState($pensionExists);
                    $em->persist($rEE);
                }

                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "FCES") {
                    $rEE->setEntityEntity($tempSeverances);
                    $rEE->setState($severancesExists);
                    $em->persist($rEE);
                }
            }
            $realEmployee->setAskBeneficiary($beneficiarie);
            $em->persist($realEmployee);
            $em->persist($user);
            $em->flush();
            $flag = true;
        }
        if ($realEmployee->getRegisterState() == 95) {
            $realEmployee->setRegisterState(99);
            $em->persist($realEmployee);
            $em->flush();
        }

        if ($flag) {
            $view = View::create();
            if ($realEmployerHasEmployee->getState() == 2) {
                $view->setData(array('url' => $this->generateUrl('show_dashboard')))->setStatusCode(200);
                return $view;
            } else {
                // It sends here the verification code.
                if($this->sendVerificationCode()){
                    $view->setData(array())->setStatusCode(200);
                    return $view;
                }else{
                    $this->finishEmployee($realEmployerHasEmployee );
                    $view->setData(array('url' => $this->generateUrl('show_dashboard')))->setStatusCode(200);
                    return $view;
                }
            }

        } else {
            $view = View::create();
            $view->setData(array('response' => array('message' => 'something went wrong')))->setStatusCode(400);
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
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when the requested Ids don't exist"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(array=true, name="severances", nullable=false, strict=true, description="employee type.")
     * @RequestParam(array=true, name="severancesExists", nullable=false, strict=true, description="checks if already has Severances.")
     * @RequestParam(name="arl", nullable=false, strict=true, description="employee type.")
     * @RequestParam(name="arlExists", nullable=false, strict=true, description="checks if already has ARL.")
     * @RequestParam(name="economicalActivity", nullable=true, strict=true, description="employee type.")
     * @return View
     */
    public function postMatrixChooseSubmitStep2Action(ParamFetcher $paramFetcher)
    {
        return $this->saveMatrixChooseSubmitStep2($paramFetcher);
    }

    private function saveMatrixChooseSubmitStep2(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        $view = View::create();

        if ($user == null) {
            return $view->setData(array('error' => array('User' => 'user not logged')))->setStatusCode(403);
        }

        $entityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Entity');
        /** @var Employer $realEmployer */
        $realEmployer = $user->getPersonPerson()->getEmployer();
        if ($user->getPersonPerson()->getEmployer() != $realEmployer) {
            return $view->setData(array('error' => array('Employer' => 'not the loged employer')))->setStatusCode(403);
        }
        $realEmployer->setEconomicalActivity($paramFetcher->get('economicalActivity') ?: 2435);
        /** @var Entity $realArl */
        $realArl = $entityRepo->find($paramFetcher->get('arl'));
        $arlExists = $paramFetcher->get('arlExists');

        $realSeverances = new ArrayCollection();
        $severances = $paramFetcher->get('severances');
        foreach ($severances as $sever) {
            $realSeverances->add($entityRepo->find($sever));
        }

        $realSeverancesExists = new ArrayCollection();
        $severancesExists = $paramFetcher->get('severancesExists');
        foreach ($severancesExists as $severExist) {
            $realSeverancesExists->add($severExist);
        }

        if ($realSeverances == null || $realArl == null) {
            return $view->setData(array('error' => array('Entity' => 'Entities Not found')))->setStatusCode(404);
        }
        $realEmployerEnt = $realEmployer->getEntities();
        $em = $this->getDoctrine()->getManager();

        if ($realEmployerEnt->count() < $realSeverances->count() + 1) {
            $counter = 0;
            $exist = false;
            /** @var EmployerHasEntity $rEE */
            foreach ($realEmployerEnt as $rEE) {
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "ARP") {
                    $rEE->setEntityEntity($realArl);
                    $rEE->setState($arlExists);
                    $exist = true;
                }
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "PARAFISCAL") {
                    $rEE->setEntityEntity($realSeverances->get($counter));
                    $rEE->setState($realSeverancesExists->get($counter));
                    $counter++;
                }
            }
            if (!$exist) {
                $realArlHasEmployer = new EmployerHasEntity();
                $realArlHasEmployer->setEntityEntity($realArl);
                $realArlHasEmployer->setState($arlExists);
                $realArlHasEmployer->setEmployerEmployer($realEmployer);
                $realEmployer->addEntity($realArlHasEmployer);
            }
            if ($counter < $realSeverances->count()) {
                for ($i = $counter; $i < $realSeverances->count(); $i++) {
                    $realSevereancesHasEmployer = new EmployerHasEntity();
                    $realSevereancesHasEmployer->setEntityEntity($realSeverances->get($i));
                    $realSevereancesHasEmployer->setState($realSeverancesExists->get($i));
                    $realSevereancesHasEmployer->setEmployerEmployer($realEmployer);
                    $realEmployer->addEntity($realSevereancesHasEmployer);
                }
            }
            $em->persist($realEmployer);
            $em->flush();
        } else {
            $counter = 0;
            /** @var EmployerHasEntity $rEE */
            foreach ($realEmployerEnt as $rEE) {
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "ARP") {
                    $rEE->setEntityEntity($realArl);
                    $rEE->setState($arlExists);
                }
                if ($rEE->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "PARAFISCAL") {
                    $rEE->setEntityEntity($realSeverances->get($counter));
                    $rEE->setState($realSeverancesExists->get($counter));
                    $counter++;
                }
            }
            $em->persist($realEmployer);
            $em->flush();
        }
        if ($realEmployer->getRegisterState() == 95) {
            $realEmployer->setRegisterState(100);
            $em->persist($realEmployer);
            $em->flush();
        }
        if ($realEmployer->getEmployerHasEmployees()->count() == 0) {
            $view->setData(array('url' => $this->generateUrl('register_employee', array('id' => -1, 'tab' => 1))))->setStatusCode(200);
        } else {
            $view->setData(array('url' => $this->generateUrl('show_dashboard')))->setStatusCode(200);
        }
        return $view;
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
     * @RequestParam(name="idContract", nullable=false, strict=true, description="the id of the contract")
     * @return View
     */
    public function getVacationsDaysTaken(ParamFetcher $paramFetcher)
    {
        $contractRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract");
        /** @var Contract $contract */
        $contract = $contractRepo->find($paramFetcher->get("idContract"));
        $payRolls = $contract->getPayrolls();
        /** @var Payroll $payRoll */
        foreach ($payRolls as $payRoll) {
            $novelties = $payRoll->getNovelties();
            /** @var Novelty $novelty */
            foreach ($novelties as $novelty) {
                if ($novelty->getNoveltyTypeNoveltyType()->getGrupo() == "Vacaciones") {
                    //TODO Necesitamos los festivos y eso es super gg
                }
            }
        }
    }

    /**
     * Create a Beneficiary from the submitted data.<br/>
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
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false,  strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false,  strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false,  strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year of birth.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month of birth.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day of birth.")
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="the civil status of the employee")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="idEmployee", nullable=false, strict=true, description="benefits of the employee.")
     * @RequestParam(name="relation", nullable=false, strict=true, description="employee relationship with beneficiary")
     * @RequestParam(name="disability", nullable=false, strict=true, description="disability")
     * @RequestParam(name="eps", nullable=false, strict=true, description="eps id")
     * @RequestParam(name="cc", nullable=false, strict=true, description="caja de compensacion id")
     * @return View
     */
    public function postNewBeneficiaryAction(ParamFetcher $paramFetcher)
    {


        $em = $this->getDoctrine()->getManager();
        $depRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
        /** @var Employee $employee */
        $employee = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:employee')->find($paramFetcher->get('idEmployee'));
        $entities = array();
        $eps = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Entity')->find($paramFetcher->get('eps'));
        array_push($entities, $eps);
        $cc = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Entity')->find($paramFetcher->get('cc'));
        array_push($entities, $cc);
        $cityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
        $tempBDep = $depRepo->find($paramFetcher->get('department'));

        $tempBCity = $cityRepo->find($paramFetcher->get('city'));
        $person = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Person')->findOneByDocument($paramFetcher->get("document"));
        if (!$person) {
            $person = new Person();
            $person->setDocument($paramFetcher->get("document"));
            $person->setDocumentType($paramFetcher->get("documentType"));
            $person->setNames($paramFetcher->get("names"));
            $person->setLastName1($paramFetcher->get("lastName1"));
            $person->setLastName2($paramFetcher->get("lastName2"));
            $datetime = new DateTime();
            $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
            $person->setBirthDate($datetime);
            $person->setCivilStatus($paramFetcher->get("civilStatus"));
            $person->setMainAddress($paramFetcher->get("mainAddress"));
            $person->setDepartment($tempBDep);
            $person->setCity($tempBCity);
            $beneficiary = new Beneficiary();
            $beneficiary->setDisability($paramFetcher->get('disability'));
            $beneficiary->setPersonPerson($person);
            foreach ($entities as $ent) {
                $employeeHasBeneficiary = new employeeHasBeneficiary();
                $employeeHasBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                $employeeHasBeneficiary->setEmployeeEmployee($employee);
                $employeeHasBeneficiary->setEntityEntity($ent);
                $employeeHasBeneficiary->setRelation($paramFetcher->get('relation'));
                $em->persist($beneficiary);
                $em->persist($person);
                $em->persist($employeeHasBeneficiary);
                $em->flush();
            }

            $view = View::create();
            $view->setStatusCode(200);
        } else {
            $beneficiary = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Beneficiary')->findOneByPersonPerson($person);
            if (!$beneficiary) {
                $beneficiary = new Beneficiary();
                $beneficiary->setDisability($paramFetcher->get('disability'));
                $beneficiary->setPersonPerson($person);
                foreach ($entities as $ent) {
                    $employeeHasBeneficiary = new employeeHasBeneficiary();
                    $employeeHasBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                    $employeeHasBeneficiary->setEmployeeEmployee($employee);
                    $employeeHasBeneficiary->setEntityEntity($ent);
                    $employeeHasBeneficiary->setRelation($paramFetcher->get('relation'));
                    $em->persist($beneficiary);
                    $em->persist($employeeHasBeneficiary);
                    $em->flush();
                }
            } else {

                foreach ($entities as $ent) {
                    $eHasBe = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:employeeHasBeneficiary')->findOneBy(
                        array('beneficiaryBeneficiary' => $beneficiary, 'employeeEmployee' => $employee, 'entityEntity' => $ent)
                    );
                    if (!$eHasBe) {
                        $employeeHasBeneficiary = new employeeHasBeneficiary();
                        $employeeHasBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                        $employeeHasBeneficiary->setEmployeeEmployee($employee);
                        $employeeHasBeneficiary->setEntityEntity($ent);
                        $employeeHasBeneficiary->setRelation($paramFetcher->get('relation'));
                        $em->persist($beneficiary);
                        $em->persist($employeeHasBeneficiary);
                        $em->flush();
                    } else {
                        $view = View::create();
                        $view->setData(array('error' => array('EmployeeHasBeneficiary' => 'Ya existe')))->setStatusCode(200);
                    }
                }
            }

            $view = View::create();
            $view->setData(array('msj' => 'exito agregando el beneficiario'))->setStatusCode(200);
        }


        return $view;
    }

    public function getBeneficiaryAction($idBeneficiary)
    {
        $beneficiary = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Beneficiary')->find($idBeneficiary);
        $employeeHasBeneficiary = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')->findOneByBeneficiaryBeneficiary($beneficiary);
        $view = View::create();
        $view->setData(array(
            'names' => $beneficiary->getPersonPerson()->getNames(),
            'documentType' => $beneficiary->getPersonPerson()->getDocumentType(),
            'document' => $beneficiary->getPersonPerson()->getDocument(),
            'lastName1' => $beneficiary->getPersonPerson()->getLastName1(),
            'lastName2' => $beneficiary->getPersonPerson()->getLastName2(),
            'civilStatus' => $beneficiary->getPersonPerson()->getCivilStatus(),
            'gender' => $beneficiary->getPersonPerson()->getGender(),
            'birthDate' => $beneficiary->getPersonPerson()->getBirthDate() ? array(
                'year' => $beneficiary->getPersonPerson()->getBirthDate()->format("Y"),
                'month' => intval($beneficiary->getPersonPerson()->getBirthDate()->format("m")),
                'day' => intval($beneficiary->getPersonPerson()->getBirthDate()->format("d")),) : array(),
            'mainAddress' => $beneficiary->getPersonPerson()->getMainAddress(),
            'department' => $beneficiary->getPersonPerson()->getDepartment()->getIdDepartment(),
            'city' => $beneficiary->getPersonPerson()->getCity()->getIdCity(),
            'disability' => $beneficiary->getDisability(),
            'relation' => $employeeHasBeneficiary->getRelation(),
            'beneficiary' => $beneficiary->getIdBeneficiary()
        ))->setStatusCode(200);
        return $view;
    }

    /**
     * Edit a Beneficiary from the submitted data.<br/>
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
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false,  strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false,  strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false,  strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year of birth.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month of birth.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day of birth.")
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="the civil status of the employee")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="idEmployee", nullable=false, strict=true, description="benefits of the employee.")
     * @RequestParam(name="relation", nullable=false, strict=true, description="employee relationship with beneficiary")
     * @RequestParam(name="disability", nullable=false, strict=true, description="disability")
     * @RequestParam(name="beneficiary", nullable=false, strict=true, description="id beneficiario")
     * @return View
     */
    public function postEditBeneficiaryAction(ParamFetcher $paramFetcher)
    {

        $view = View::create();
        $em = $this->getDoctrine()->getManager();

        $beneficiary = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Beneficiary')->find($paramFetcher->get('beneficiary'));

        $employee = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee')->find($paramFetcher->get('idEmployee'));
        $employeeHasBeneficiaries = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:employeeHasBeneficiary')->findBy(array('beneficiaryBeneficiary' => $beneficiary, 'employeeEmployee' => $employee));

        $depRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
        $cityRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');


        $tempBDep = $depRepo->find($paramFetcher->get('department'));

        $tempBCity = $cityRepo->find($paramFetcher->get('city'));

        $person = $beneficiary->getPersonPerson();
        $person->setDocument($paramFetcher->get("document"));
        $person->setDocumentType($paramFetcher->get("documentType"));
        $person->setNames($paramFetcher->get("names"));
        $person->setLastName1($paramFetcher->get("lastName1"));
        $person->setLastName2($paramFetcher->get("lastName2"));
        $datetime = new DateTime();
        $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
        $person->setBirthDate($datetime);
        $person->setCivilStatus($paramFetcher->get("civilStatus"));
        $person->setMainAddress($paramFetcher->get("mainAddress"));
        $person->setDepartment($tempBDep);
        $person->setCity($tempBCity);
        $beneficiary->setDisability($paramFetcher->get('disability'));
        $em->persist($person);
        $em->persist($beneficiary);
        $em->flush();
        foreach ($employeeHasBeneficiaries as $employeeHasBeneficiary) {
            $employeeHasBeneficiary->setRelation($paramFetcher->get('relation'));
            $em->persist($employeeHasBeneficiary);
            $em->flush();
        }

        $view->setStatusCode(200);
        return $view;
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

    public function getEmployeeInfoAction($idEmployee)
    {

        $employee = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee')->find($idEmployee);

        $view = View::create();
        $view->setData(array(
            'civilStatus' => $employee->getPersonPerson()->getCivilStatus(),
            'birthDate' => $employee->getPersonPerson()->getBirthDate() ? array(
                'year' => $employee->getPersonPerson()->getBirthDate()->format("Y"),
                'month' => intval($employee->getPersonPerson()->getBirthDate()->format("m")),
                'day' => intval($employee->getPersonPerson()->getBirthDate()->format("d")),) : array(),
            'mainAddress' => $employee->getPersonPerson()->getMainAddress(),
            'department' => $employee->getPersonPerson()->getDepartment()->getIdDepartment(),
            'city' => $employee->getPersonPerson()->getCity()->getIdCity(),
            'birthCountry' => $employee->getPersonPerson()->getBirthCountry()->getIdCountry(),
            'birthDepartment' => $employee->getPersonPerson()->getBirthDepartment()->getIdDepartment(),
            'birthCity' => $employee->getPersonPerson()->getBirthCity()->getIdCity(),
            'phone' => $employee->getPersonPerson()->getPhones()[0]->getPhoneNumber(),
            'email' => $employee->getPersonPerson()->getEmail(),
        ))->setStatusCode(200);
        return $view;
    }

    /**
     * Edit a Beneficiary from the submitted data.<br/>
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
     * @RequestParam(name="civilStatus", nullable=false, strict=true, description="the civil status of the employee")
     * @RequestParam(name="year", nullable=false, strict=true, description="year of birth.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month of birth.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day of birth.")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="phone", nullable=false, strict=true, description="telefono.")
     * @RequestParam(name="email", nullable=false, strict=true, description="correo electronico.")
     * @RequestParam(name="birthDepartment", nullable=false, strict=true, description="birth department.")
     * @RequestParam(name="birthCity", nullable=false, strict=true, description="birth city.")
     * @RequestParam(name="birthCountry", nullable=false, strict=true, description="birth country.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(name="idEmployee", nullable=false, strict=true, description="benefits of the employee.")
     * @return View
     */
    public function postEditEmployeeAction(ParamFetcher $paramFetcher)
    {
        $view = $view = View::create();

        $em = $this->getDoctrine()->getManager();

        $employee = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee')->find($paramFetcher->get('idEmployee'));
        $city = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City')->find($paramFetcher->get('city'));
        $birthCity = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City')->find($paramFetcher->get('birthCity'));
        $department = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department')->find($paramFetcher->get('department'));
        $birthDepartment = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department')->find($paramFetcher->get('birthDepartment'));
        $birthCountry = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Country')->find($paramFetcher->get('birthCountry'));


        $person = $employee->getPersonPerson();
        $person->setEmail($paramFetcher->get('email'));
        $person->setCivilStatus($paramFetcher->get('civilStatus'));
        $person->setMainAddress($paramFetcher->get('mainAddress'));
        $datetime = new DateTime();
        $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
        $person->setBirthDate($datetime);
        $phone = $person->getPhones()[0];
        $phone->setPhoneNumber($paramFetcher->get('phone'));

        $person->setDepartment($department);
        $person->setBirthDepartment($birthDepartment);

        $person->setCity($city);
        $person->setBirthCity($birthCity);
        $person->setBirthCountry($birthCountry);

        $em->persist($person);
        $em->flush();

        $view->setStatusCode(200);
        return $view;
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

    public function sendVerificationCode()
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $code = rand(10100, 99999);
        $message = "Tu codigo de confirmacion de Symplifica es: " . $code;

        $user->setSmsCode($code);
        $em->persist($user);
        $em->flush();

        /** @var Phone $phone */
        $phone = $user->getPersonPerson()->getPhones()[0];

        $twilio = $this->get('twilio.api');
        $cellphone = $phone;

        try{
            $twilio->account->messages->sendMessage(
                "+19562671001", "+57" . $cellphone->getPhoneNumber(), $message);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * Update employee vacations debts in active contract.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Update employee vacations debts in active contract.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="start_index", nullable=false, strict=true, description="start_index.")
     * @RequestParam(name="end_index", nullable=false, strict=true, description="end_index.")
     *
     * @return View
     */
    public function postVacationsDebtUpdateAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $response = 'START'.'<br>';
        $start = $paramFetcher->get('start_index');
        $end = $paramFetcher->get('end_index');
        $eHEs = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findAll();
        $count = 1;
        /** @var EmployerHasEmployee $eHE */
        foreach ($eHEs as $eHE) {
            if($eHE->getState()>=4){
                if($count >= $start and $count <= $end){
                    /** @var Contract $contract */
                    $contract = $eHE->getActiveContract();//Active Contracts
                    if($contract){
                        $start_date = $contract->getStartDate();
                        $end_date = $contract->getEndDate();
                        $today = new DateTime();
                        if(!$end_date or $end_date>=$today){
                            $day = intval($start_date->format('d'));
                            $month = intval($start_date->format('m'));
                            $year = intval($start_date->format('Y'));
                            $response.= "<br>EMPLOYER: ".$eHE->getEmployerEmployer()->getPersonPerson()->getFullName()." CONTRACT ID: ".$contract->getIdContract().".<br>";
                            if($start_date < new DateTime("16-06-01 00:00:00"))
                                $response.= "ANTIQUE.<br>";
                            $response.= 'EHE: ' . $eHE->getIdEmployerHasEmployee() . ' (' . $day . '-' . $month . '-'.$year.')';
                            if($end_date){
                                $response.= ' (' . intval($end_date->format('d')) . '-' . intval($end_date->format('m')) . '-'.intval($end_date->format('Y')).').<br>';
                            }else{
                                $response.='.<br>';
                            }
                            $payrolls = $contract->getPayrolls();
                            $vacationDebt = 0.0;
                            $vacationDebt+= $contract->getHolidayDebt();
                            $response.= "VACATIONS_DEBT: ".$vacationDebt."<br>";
                            /** @var Payroll $payroll */
                            foreach ($payrolls as $payroll) {
                                $response.="ID_PAYROLL: ".$payroll->getIdPayroll()." MONTH: ".$payroll->getMonth()." YEAR: ".$payroll->getYear()." PERIOD: ".$payroll->getPeriod()." ";
                                $novelties = $payroll->getSqlNovelties();
                                if(count($novelties)==0){
                                    $response.="NO NOVELTIES.<br>";
                                }else{
                                    $response.="<br>";
                                }
                                $workableDays = 0;
                                $notPaidDays = 0;
                                $minusVacations = 0;
                                /** @var Novelty $novelty */
                                foreach ($novelties as $novelty) {
                                    if($novelty->getNoveltyTypeNoveltyType()!= null){
                                        if(intval($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == 1)){//Salary novelty type
                                            $response.=" | NOVELTY: SALARY ";
                                            if($novelty->getUnits() != null){
                                                $response.="UNITS: ".intval($novelty->getUnits());
                                                $workableDays += intval($novelty->getUnits());
                                            }else{
                                                $response.="ERROR";
                                            }
                                        }
                                        if(intval($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == 145)){//Vacations novelty type
                                            $response.=" | NOVELTY: VACATIONS ";
                                            if($novelty->getUnits() != null){
                                                $response.="UNITS: ".intval($novelty->getUnits());
                                                $minusVacations += intval($novelty->getUnits());
                                            }else{
                                                $response.="ERROR";
                                            }
                                        }
                                        if(intval($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == 3120)){//Unpaid Leave novelty type
                                            $response.="| NOVELTY: UNPAID LEAVE ";
                                            if($novelty->getUnits() != null){
                                                $response.="UNITS: ".intval($novelty->getUnits());
                                                $notPaidDays += intval($novelty->getUnits());
                                            }else{
                                                $response.="ERROR";
                                            }
                                        }
                                        if(intval($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == 28)){//Labor inability novelty type
                                            $response.=" | NOVELTY: LABOR INABILITY ";
                                            if($novelty->getUnits() != null){
                                                $response.="UNITS: ".intval($novelty->getUnits());
                                                $workableDays += intval($novelty->getUnits());
                                            }else{
                                                $response.="ERROR";
                                            }
                                        }
                                        if(intval($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == 23)){//Paid Leave novelty type
                                            $response.=" | NOVELTY: PAID LEAVE ";
                                            if($novelty->getUnits() != null){
                                                $response.="UNITS: ".intval($novelty->getUnits());
                                                $workableDays += intval($novelty->getUnits());
                                            }else{
                                                $response.="ERROR";
                                            }
                                        }
                                    }else{
                                        $response.="___NO TYPE<br>";
                                    }
                                }
                                if($payroll->getPeriod()==4){
                                    $payrollDate = new DateTime( $payroll->getYear().'-'.$payroll->getMonth().'-26 00:00:00');
                                }else{
                                    $payrollDate = new DateTime( $payroll->getYear().'-'.$payroll->getMonth().'-13 00:00:00');
                                }
                                if($today->format('m')==1){
                                    $vacMonth = new DateTime($today->format('y').'-12-30 00:00:00');
                                }else{
                                    $vacMonth = new DateTime($today->format('y').'-'.(intval($today->format('m'))-1).'-30 00:00:00');
                                }
                                if($payrollDate<$vacMonth){
                                    $response.=' | WORKABLE DAYS: ' . $workableDays . ' UNPAID DAYS: ' . $notPaidDays . ' PAID_VACATIONS: ' . $minusVacations ." | DEBT: ".(((($workableDays-$notPaidDays)/720)*30)-$minusVacations);
                                    $vacationDebt += ((($workableDays-$notPaidDays)/720)*30)-$minusVacations;
                                }
                                $response.="<br>";
                            }
                            $response.= 'VACATIONS: ' . $vacationDebt . '<br>';
                            $contract->setHolidayDebt($vacationDebt);
                            $em->persist($contract);
                        }else{
                            $response.="ID_CONTRACT: ".$contract->getIdContract()."<br>";
                        }
                    }
                    if($count % 30 == 0){
                        $response .= '<br>GROUP FROM ' . ( $count - 30 ) . ' TO ' . $count . ' FLUSHED' . '<br><br>';
                        $em->flush();
                        $em->clear();
                    }
                    if($count % 30 != 0 and $count== $end){
                        $response .= '<br>GROUP FROM ' . ( $count - ( $count % 30 ) ) . ' TO ' . ( $count ) . ' FLUSHED' . '<br><br>';
                        $em->flush();
                        $em->clear();
                    }
                }
                $count++;
            }
        }
        if($count <= $end and $count % 30 != 0 ){
            $response .= '<br>GROUP FROM ' . ( $count - ( $count % 30 ) ) . ' TO ' . ( $count - 1 ) . ' FLUSHED' . '<br><br>';
            $em->flush();
            $em->clear();
        }
        $view = $view = View::create($response);
        $view->setStatusCode(200);
        return $view;
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
     * @RequestParam(name="verificationCode", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="contractId", nullable=false, strict=true, description="documentType.")
     *
     * @return View
     */
    public function postVerifyVerificationCodeAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $code = $paramFetcher->get('verificationCode');
        /** @var Contract $contract */
        $contract = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract')->find($paramFetcher->get('contractId'));
        $ehe = $contract->getEmployerHasEmployeeEmployerHasEmployee();
        $realEmployee = $ehe->getEmployeeEmployee();
        $view = View::create();
        /** @var User $user */
        $user = $this->getUser();
        if ($code == 0)
            $view->setData([])->setStatusCode(404);
        elseif ($code == $user->getSmsCode()) {
            $this->finishEmployee($ehe);
            $view->setData(['url' => $this->generateUrl('show_dashboard')])->setStatusCode(200);
        } else
            $view->setData([])->setStatusCode(401);

        if ($view->getStatusCode() != 200) {
            // If the error code was invalid we send another.
            $this->sendVerificationCode();
        }

        return $view;
    }

    /**
     * @param EmployerHasEmployee $ehe
     */
    private function finishEmployee($ehe){
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user= $this->getUser();
        $realEmployee = $ehe->getEmployeeEmployee();
        if ($realEmployee->getRegisterState() == 99) {
            $ehe->setState(2);
            $realEmployee->setRegisterState(100);
            $contracts=$ehe->getContracts();
            $actContract=null;
            /** @var Contract $contract */
            foreach ($contracts as $contract) {
                if($contract->getState()==1){
                    $actContract=$contract;
                    break;
                }
            }
            $em->persist($realEmployee);
            $em->persist($ehe);
            $campaignRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Campaign");
            /** @var Campaign $campaign150 */
            $campaign150 = $campaignRepo->findOneBy(array('description'=>'150k'));
            $dateToday = new DateTime();
            if($campaign150->getDateStart()<=$dateToday&&$campaign150->getDateEnd()>=$dateToday&&$actContract->getPayMethodPayMethod()->getPayTypePayType()->getSimpleName()!="EFE"){
                $uHCs = $user->getUserHasCampaigns();
                if($this->check150kCampaing($uHCs)==null){
                    //activate 150k Campaign
                    $eligible = new UserHasCampaign();
                    $eligible->setCampaignCampaign($campaign150);
                    $eligible->setUserUser($user);
                    $em->persist($eligible);
                }
            }
            $em->flush();
            if($user->getStatus()==2){
                $ehe->setState(3);
                $ehe->setIsPostRegister(true);
                $em->persist($ehe);
                $em->flush();
                $this->crearTramites($user);
                $this->validateDocuments($user);
            }

        }
    }

}
