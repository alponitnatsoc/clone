<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\RocketSellerTwoPickBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\User;
use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Tests\Request\ParamFetcherTest;

class UtilsRestController extends FOSRestController
{

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getUsersDotationAction()
    {
        //correo nombre telefono nombre de la empleada
        $contracts=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->findAll();
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $targetDate = new \DateTime("2016-08-21");
        $answer= array();
        $reals=new ArrayCollection();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            if($contract->getStartDate()<=$targetDate)
                $reals->add($contract);
        }
        $i=0;
        /** @var Contract $real */
        foreach ($reals as $real) {
            $answer[$i]=array();
            $employerP=$real->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
            /** @var User $user */
            $user=$userRepo->findOneBy(array('personPerson'=>$employerP->getIdPerson()));
            $answer[$i]['correo']=$user->getEmailCanonical();
            $answer[$i]['nombre']=$employerP->getFullName();
            $answer[$i]['telefono']=$employerP->getFullName();
            $answer[$i]['ENombre']=$real->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
            $answer[$i]['FechaI']=$real->getStartDate()->format("Y-m-d");
            $i++;
        }

        $view = View::create();
        $view->setData($answer)->setStatusCode(200);

        return $view;
    }



    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getMissingFCESAction()
    {
        //correo nombre telefono nombre de la empleada
        $entity = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Entity")->findBy(array('name'=>'NO SE'));
        $ehe = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployeeHasEntity")->findBy(array('entityEntity'=>$entity));
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");

        $answer= array();
        $i=0;
        /** @var EmployeeHasEntity $item */
        foreach ($ehe as $item) {
            $employers = $item->getEmployeeEmployee()->getEmployeeHasEmployers();
            /** @var EmployerHasEmployee $employer */
            foreach ($employers as $employer) {
                /** @var User $user */
                $user = $userRepo->findOneBy(array('personPerson'=>$employer->getEmployerEmployer()->getPersonPerson()));
                $answer[$i]=array();
                $answer[$i]['nombreEmpleador'] = $employer->getEmployerEmployer()->getPersonPerson()->getFullName();
                $answer[$i]['nombreEmpleado'] = $employer->getEmployeeEmployee()->getPersonPerson()->getFullName();
                $answer[$i]['correo'] = $user->getEmail();
                $i++;
            }
        }

        $view = View::create();
        $view->setData($answer)->setStatusCode(200);

        return $view;
    }

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param Request $request
     * @return View
     */
    public function putUserPayBackAction(Request $request)
    {
        $requ = $request->request->all();
        $view = View::create();


        if(isset($requ['token'])&&isset($requ['toPay'])&&isset($requ['user'])&&$requ['user']!=null){
            $topay = $requ['toPay'];
            $token = $requ['token'];
            $user = $requ['user'];
            /** @var User $backUser */
            $backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('salt'=>$token));
            $auth=false;
            if($backUser!=null ){
                $roles = $backUser->getRoles();
                $auth=false;
                foreach ($roles as $key => $role) {
                    if($role=="ROLE_MONEY_ADMIN")
                        $auth=true;
                }
            }
            if(!$auth){
                $view = View::create();
                $view->setStatusCode(403);
                return $view;
            }else{
                $request = $this->container->get('request');
                $request->setMethod('POST');
                foreach ( $topay as $key => $item) {
                    $topay[$key]="".$item;
                }
                $request->request->add(array('podsToPay'=>$topay,'idUser'=>"".$user,'paymentMethod'=>"1-none"));
                $answer = $this->forward("RocketSellerTwoPickBundle:PayrollRestSecured:postConfirm", array('request'=>$request), array('_format' => 'json'));
                $view->setData($answer->getContent())->setStatusCode($answer->getStatusCode());
                return $view;

            }
        }

        $view->setData(array())->setStatusCode(400);

        return $view;
    }

    /**
     * create refund<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "create refund",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *   }
     * )
     *
     * @param Request $request.
     *  Rest Parameters:
     * (name="source", nullable=true, requirements="101|100", description="source 100 for hightech 101 for novopayment")
     * (name="account_number", nullable=false, requirements="[0-9]+",description="employer hightech id")
     * (name="account_id", nullable=false, requirements="[0-9]+", description="id from the account when asking hightech")
     * (name="value", nullable=false, requirements="[0-9]+(\.[0-9]+)?",description="Value for the purchase order")
     * (name="token", nullable=false, requirements="[0-9]+(\.[0-9]+)?",description="the security token")
     * @return View
     */

    public function putCreateRefundPurchaseOrderAction(Request $request){

        $parameters = $request->request->all();
        $token = $parameters['token'];
        /** @var User $backUser */
        $backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('salt'=>$token));
        $auth=false;
        $view = View::create();
        if($backUser!=null ){
            $roles = $backUser->getRoles();
            $auth=false;
            foreach ($roles as $key => $role) {
                if($role=="ROLE_MONEY_ADMIN")
                    $auth=true;
            }
        }
        if(!$auth){
            $view->setStatusCode(403);
            return $view;
        }
        $em = $this->getDoctrine()->getManager();
        if(!$parameters["account_number"]){
            $view->setStatusCode(400);
            return $view;
        }
        $accountNumber = $parameters["account_number"];
        if(!$parameters["account_id"]){
            $view->setStatusCode(400);
            return $view;
        }
        $accountId = $parameters["account_id"];
        if(!$parameters["source"]){
            $view->setStatusCode(400);
            return $view;
        }
        $source = intval($parameters["source"]);
        if(!$parameters["value"]){
            $view->setStatusCode(400);
            return $view;
        }
        $value = floatval($parameters["value"]);
        /** @var Employer $employer */
        $employer = $em->getRepository("RocketSellerTwoPickBundle:Employer")->findOneBy(array('idHighTech'=>$parameters["account_number"]));
        if(!$employer){
            $view->setStatusCode(404);
            return $view;
        }
        $person = $employer->getPersonPerson();
        /** @var User $user */
        $user = $em->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson'=>$person));
        if(!$user){
            $view = View::create();
            $view->setStatusCode(404);
            return $view;
        }
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            'source'=>$source,
            'accountNumber'=>$accountNumber,
            'accountId'=>$accountId,
            'value'=>$value));
        $responseView = $this->forward("RocketSellerTwoPickBundle:Payments2Rest:postRegisterDevolution",array('request' => $request), array('_format' => 'json'));
        if ($responseView->getStatusCode() != 200) {
            //If some kind of error
            $view = View::create();
            $view->setStatusCode(400);
            return $view;
        }
        $radicatedNumber = json_decode($responseView->getContent(), true)["numeroRadicado"];
        $responseCode = json_decode($responseView->getContent(), true)["codigoRespuesta"];
        $PO = new PurchaseOrders();
        $PO->setIdUser($user);
        $PO->setPayMethodId($accountId);
        if($source==100){
            $PO->setProviderId(1);
        }else{
            $PO->setProviderId(0);
        }
        $PO->setDateCreated(new \DateTime());
        $PO->setDateModified(new \DateTime());
        $PO->setName("Devolución");
        $PO->setValue($value);
        if($responseCode==0){
            $PO->setDatePaid(new \DateTime());
            $PO->setPurchaseOrdersStatus($em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'00')));
        }else{
            $PO->setPurchaseOrdersStatus($em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'-2')));
        }
        $PO->setAlreadyRecived(1);
        $em->persist($PO);
        $pod = new PurchaseOrdersDescription();
        $pod->setPurchaseOrders($PO);
        $pod->setValue($value);
        $pod->setProductProduct($em->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array('simpleName'=>'DEV')));
        $pod->setDescription("Devolución");
        $em->persist($pod);
        $pay = new Pay();
        $pay->setUserIdUser($user);
        $pay->setPurchaseOrdersDescription($pod);
        $pay->setIdDispercionNovo($radicatedNumber);
        $em->persist($pay);
        $em->flush();
        $view = View::create();
        $view->setData(array(
            'userID'=>$user->getId(),
            'employerId'=>$employer->getIdEmployer(),
            'numeroRadicado'=>$radicatedNumber,
            'purchaseOrderId'=>$PO->getIdPurchaseOrders(),
            'purchaseOrderDescriptionId'=>$pod->getIdPurchaseOrdersDescription(),
            'payId'=>$pay->getIdPay(),
            'codigoRespuestaDevolucion'=>$responseCode,
            'estado'=>'OK'
            ));
        $view->setStatusCode(200);
        return $view;

    }

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getGeneratePodMembershipUsersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $view = View::create();

        /** @var Product $PS1 */
        $PS1 = $em->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName" => "PS1"));
        /** @var Product $PS2 */
        $PS2 = $em->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName" => "PS2"));
        /** @var Product $PS3 */
        $PS3 = $em->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName" => "PS3"));
        /** @var PurchaseOrdersStatus $PS3 */
        $status_peding = $em->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'P1') );
        $data = array();

        $dateToday = new \DateTime();


        $users = $em->getRepository("RocketSellerTwoPickBundle:User")->findBy(array('status' => '2'));

        /** @var User $user */
        foreach ($users as $user){
            if($user->getLastPayDate() == null || $user->getLastPayDate() == '')
            {
                continue;
            }
            $total = 0;
            $effectiveDate = $user->getLastPayDate();
            $isFreeMonths = $user->getIsFree();
            if ($isFreeMonths == 0) {
                $isFreeMonths += 1;
            }

            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($effectiveDate->format("Y-m-") . "25"))));

            if ($dateToday->format("d") >= 14 && ($dateToday->format("m") >= $effectiveDate->format("m") || $dateToday->format("Y") > $effectiveDate->format("Y")) && $dateToday->format("Y") >= $effectiveDate->format("Y")) {
                //this means that the user has to pay the symplifica fee this month
                $symplificaPO = new PurchaseOrders();
                $symplificaPO->setIdUser($user);

                $symplificaPO->setPurchaseOrdersStatus($status_peding);

                $symplificaPOD = new PurchaseOrdersDescription();
                $symplificaPOD->setDescription("Subscripción Symplifica");
                $symplificaPOD->setPurchaseOrdersStatus($status_peding);

                $ehes = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();

                $ps1Count = 0;
                $ps2Count = 0;
                $ps3Count = 0;
                /** @var EmployerHasEmployee $ehe */
                foreach ($ehes as $ehe) {
                    if ($ehe->getState() < 3) {
                        continue;
                    }
                    $contracts = $ehe->getContracts();
                    $actualContract = null;
                    /** @var Contract $contract */
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 1) {
                            $actualContract = $contract;
                            break;
                        }
                    }
                    if ($actualContract == null) {
                        continue;
                    }
                    $actualDays = $actualContract->getWorkableDaysMonth();
                    if ($actualDays < 10) {
                        $ps1Count++;
                    } elseif ($actualDays <= 19) {
                        $ps2Count++;
                    } else {
                        $ps3Count++;
                    }

                }
                $total = round(($PS1->getPrice() * (1 + $PS1->getTaxTax()->getValue()) * $ps1Count) +
                    ($PS2->getPrice() * (1 + $PS2->getTaxTax()->getValue()) * $ps2Count) +
                    ($PS3->getPrice() * (1 + $PS3->getTaxTax()->getValue()) * $ps3Count), 0);

                $symplificaPOD->setValue($total);

                $symplificaPOD->setProductProduct($PS3);
                $symplificaPO->addPurchaseOrderDescription($symplificaPOD);
                $symplificaPO->setValue($total);

                $user->setLastPayDate(new DateTime($dateToday->format("Y-m-")."25"));

                $user->setIsFree(0);

                // Se consulta el PayMethod
                $response = $this->forward('RocketSellerTwoPickBundle:UtilsRest:getPayMethodUser',array('idUser' => $user->getId()), array('format' => 'json'));
                $method_id = json_decode($response->getContent(),true);

                /** @var PurchaseOrders $po  */
                $symplificaPO->setPayMethodId($method_id['method_id']);
                $em->persist($user);
                $em->persist($symplificaPO);
                $em->persist($symplificaPOD);

                $em->flush();

                $response =  $this->sendPayToPodMembershipUsers($symplificaPO->getIdPurchaseOrders());
                if($response->getStatusCode() != 200){
                    array_push($data, array(
                        'id_user' => $user->getId(),
                        'Name_user' => $user->getId().' '.$user->getPersonPerson()->getFullName(),
                        'id_PO' => $symplificaPO->getIdPurchaseOrders(),
                        'id_POD' => $symplificaPOD->getIdPurchaseOrdersDescription(),
                        'error' => $response->getContent(),
                    ));
                }else {
                    array_push($data, array(
                        'id_user' => $user->getId(),
                        'Name_user' => $user->getId() . ' ' . $user->getPersonPerson()->getFullName(),
                        'id_PO' => $symplificaPO->getIdPurchaseOrders(),
                        'id_POD' => $symplificaPOD->getIdPurchaseOrdersDescription(),
                        'error' => 'none'
                    ));
                }
            }

        }

        $view->setStatusCode(200);
        $view->setData($data);

        return $view;
    }

    /** Envia la peticion de cobro de la suscripcion.
     *
     */
    private function sendPayToPodMembershipUsers($po){

        $response = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array('idPurchaseOrder' => $po), array( 'format' => 'json'));
        return $response;

    }

    /**
     * Consulta Paymenthod de un user
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the PayMethod User",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getPayMethodUserAction($idUser){

        $view = View::create();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder();
        $query->add('select', 'po');

        $query->from("RocketSellerTwoPickBundle:PurchaseOrders",'po')
            ->Join("po.purchaseOrdersStatus",'ps')
            ->where("ps.idNovoPay = '00' and po.payMethodId != 'none' and po.idUser = ?1")
            ->setMaxResults(1)
            ->setParameter(1,$idUser);

        /** @var PurchaseOrders $poUser */
        $poUser = $query->getQuery()->getResult();

        $paymenthod = 0;
        if(count($poUser) > 0){
            $paymenthod = $poUser[0]->getProviderId().'-'.$poUser[0]->getPayMethodId();
            if($paymenthod != '' || $paymenthod != null){
                return $view->setStatusCode(200)->setData(array('method_id' => $paymenthod));
            }
        }

        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $idUser), array('_format' => 'json'));
        $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);


        if(!isset($responsePaymentsMethods['payment-methods'][0]['method-id'])){
            $responsePaymentsMethods = array('payment-methods' => array(array(
                    "payment-type" => "Ahorros",
                    "account" => "*******0444",
                    "method-id" => "1-00000000",
                    "bank" => "BANCOLOMBIA S.A.",
                    "id-provider" >= "1"
                )));
        }
        $paymenthod = $responsePaymentsMethods['payment-methods'][0]['method-id'];

        return $view->setStatusCode(200)->setData(array('method_id' => $paymenthod));
    }
}
