<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
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
                $request->setMethod('POST');
                $request->request->add(array('podsToPay'=>$topay,'idUser'=>$user,'paymentMethod'=>"1-none"));
                $answer = $this->forward("RocketSellerTwoPickBundle:PayrollRestSecured:postConfirm", array('request',$request), array('_format' => 'json'));
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

}
