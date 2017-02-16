<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\AccountType;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\TransactionState;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Mailer\TwigSwiftMailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodRestController extends FOSRestController
{
    /**
     * Add the credit card<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict",
     *     500 = "Novo TimeOut"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="accountNumber", nullable=false,  requirements="\d+", strict=true, description="Account Number")
     * @RequestParam(name="bankId", nullable=false,  requirements="\d+", strict=true, description="the bank id")
     * @RequestParam(name="accountTypeId", nullable=false, strict=true, description="Account type ID")
     * @RequestParam(name="userId", nullable=false  , strict=true, description="Account type ID")
     */
    public function postAddDebitAccountAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find($paramFetcher->get("userId"));
        $person = $user->getPersonPerson();
        $bankRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Bank");
        $accountTypeRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:AccountType");
        /** @var Bank $realBank */
        $realBank=$bankRepo->find($paramFetcher->get("bankId"));
        /** @var AccountType $realAccountType */
        $realAccountType=$accountTypeRepo->find($paramFetcher->get("accountTypeId"));

        $employer=$person->getEmployer();
        $view = View::create();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "accountNumber" => $employer->getIdHighTech(),
            "bankCode" => $realBank->getHightechCode(),
            "accountType" => $realAccountType->getName()=='Ahorros'?"AH":"CC",
            "bankAccountNumber" => $paramFetcher->get("accountNumber"),
            "expirationDate" => date('Y-m-d', strtotime('+1 years')),
            "authorizationDocumentName" => $person->getFullName().".txt",
            "authorizationDocument" => $person->getFullName(),
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterBankAccount', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(json_decode($insertionAnswer->getContent(), true));
            return $view;
        } else {
						$em = $this->getDoctrine()->getEntityManager();
						$arrDecoded = get_object_vars(json_decode($insertionAnswer->getContent()));
						$radicatedNumber = $arrDecoded['numeroRadicado'];
						
						$transactionTypeRCB = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:TransactionType")
						    ->findOneBy(array('code' => 'RCB'));
						$podStatusIscripcionEnviada = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")
						    ->findOneBy(array('idNovoPay' => 'InsCue-Env'));
						
						/** @var Transaction $transaction */
						$transactionBankAccount = new Transaction();
						$transactionBankAccount->setRadicatedNumber($radicatedNumber);
						$transactionBankAccount->setTransactionType($transactionTypeRCB);
						$transactionBankAccount->setPurchaseOrdersStatus($podStatusIscripcionEnviada);
						
						$employer->addTransaction($transactionBankAccount);
						$em->persist($employer);
						$em->flush();
        }

        return $view->setStatusCode(201)->setData(array());
    }
    /**
     * Add the credit card<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict",
     *     500 = "Novo TimeOut"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="credit_card", nullable=false,  requirements="\d+", strict=true, description="CC Number")
     * @RequestParam(name="expiry_date_year", nullable=false,  requirements="[0-9]{4}", strict=true, description="YEAR in YYYY format.")
     * @RequestParam(name="expiry_date_month", nullable=false,  requirements="[0-9]{2}", strict=true, description="Month in MM format.")
     * @RequestParam(name="cvv", nullable=false,  requirements="\d+", strict=true, description="CVV CC.")
     * @RequestParam(name="name_on_card", nullable=false, strict=true, description="The name on card")
     * @RequestParam(name="userId", nullable=false  , strict=true, description="Account type ID")

     */
    public function postAddCreditCardAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find($paramFetcher->get("userId"));
        $person = $user->getPersonPerson();
        $view = View::create();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "accountNumber" => $paramFetcher->get("credit_card"),
            "expirationYear" => $paramFetcher->get("expiry_date_year"),
            "expirationMonth" => $paramFetcher->get("expiry_date_month"),
            "codeCheck" => $paramFetcher->get("cvv"),
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClientPaymentMethod', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 201) {
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(json_decode($insertionAnswer->getContent(), true));
            return $view;
        }
        $chargeValue = 200;
        $idPayM = json_decode($insertionAnswer->getContent(), true)["method-id"];
        $purchaseOrder = new PurchaseOrders();
        $purchaseOrder->setValue($chargeValue);
        $purchaseOrder->setName("Cargo prueba CC");
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($purchaseOrder);
        $em->flush();
        $purchaseOrderId = $purchaseOrder->getIdPurchaseOrders();
        $em->remove($purchaseOrder);
        $em->flush();
        $request->setMethod("POST");
        $request->request->add(array(
            "documentNumber" => $person->getDocument(),
            "MethodId" => $idPayM,
            "totalAmount" => $chargeValue,
            "taxAmount" => 0,
            "taxBase" => 0,
            "commissionAmount" => 0,
            "commissionBase" => 0,
            "chargeMode" => 1,
            "chargeId" => $purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() == 200) {
            $chargeRC = json_decode($insertionAnswer->getContent(), true)["charge-rc"];
        } else {
            $chargeRC = "-1";
        }
        if (!($insertionAnswer->getStatusCode() == 200 && ($chargeRC == "00" || $chargeRC == "08"))) {
            $this->getDeletePayMethodAction($idPayM, $person->getDocument());
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array(
                'data' => $insertionAnswer->getContent(),
                'error' => array("Credit Card" => "No se pudo agregar el medio de Pago")));
            return $view;
        }
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber" => $person->getDocument(),
            "chargeId" => $purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteReversePaymentMethod', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            $view->setStatusCode(201)->setData(array('response' => array("method-id" => $idPayM), 'error' => array("Credit Card" => "Se agrego la taerjeta de Credito, pero no se pudo reversar el cobro")));
        } else {
            $view->setStatusCode(201)->setData(array('response' => array("method-id" => $idPayM), "extra-data" => $insertionAnswer->getContent()));
        }
        return $view;
    }

    /**
     * add generic pay method function<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Based on the form, call the respective add pay method",
     *   statusCodes = {
     *     201 = "Added",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict",
     *     500 = "Novo TimeOut"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="pay_method", nullable=true, strict=true, description="Account type")
     * @RequestParam(name="userId", nullable=true, strict=true, description="Account type ID")
     * @RequestParam(name="accountNumber", nullable=true,  requirements="\d+", strict=true, description="Account Number")
     * @RequestParam(name="bankId", nullable=true,  requirements="\d+", strict=true, description="the bank id")
     * @RequestParam(name="accountTypeId", nullable=true, strict=true, description="Account type ID")
     * @RequestParam(name="name_on_card", nullable=true, strict=true, description="Name on the credit card")
     * @RequestParam(name="credit_card", nullable=true,  requirements="\d+", strict=true, description="CC Number")
     * @RequestParam(name="expiry_date_year", nullable=true,  requirements="[0-9]{4}", strict=true, description="YEAR in YYYY format.")
     * @RequestParam(name="expiry_date_month", nullable=true,  requirements="[0-9]{2}", strict=true, description="Month in MM format.")
     * @RequestParam(name="cvv", nullable=true,  requirements="\d+", strict=true, description="CVV CC.")
     */
    public function postAddGenericPayMethodAction(ParamFetcher $paramFetcher)
    {
        $payMethod = $paramFetcher->get("pay_method");
        $view=null;
        if($payMethod == "Tarjeta de Crédito"){
          $view = $this->postAddCreditCardAction($paramFetcher);
        }
        elseif ($payMethod == "Cuenta Bancaria") {
          $view = $this->postAddDebitAccountAction($paramFetcher);
        }
        if($view != null && $view->getStatusCode()==201){
            $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
            /** @var User $realUser */
            $realUser = $userRepo->find($paramFetcher->get("userId"));
            if($realUser!=null){
                $context = array(
                    'emailType'=>'validatePayMethod',
                    'toEmail'=>$realUser->getEmail(),
                    'userName'=>$realUser->getPersonPerson()->getNames(),
                    'starDate'=>new DateTime(),
                    'payMethod'=>$paramFetcher->get('pay_method')
                );
                $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            }

        }
        return $view;
    }

    /**
     * Return the method Pay of user in array.
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
    public function getClientListPaymentMethodsAction($idUser){
        /** @var User $user */
        $user = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->find($idUser);
        $bankRepo = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Bank");

        $view = View::create();
        if($user==null){
            return $view->setStatusCode(404)->setData(array("user"=>"the User Does not exist"));
        }
        $clientListPaymentmethodsCC = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $user->getPersonPerson()->getDocument()), array('_format' => 'json'));
        $responsePaymentsMethodsCC = json_decode($clientListPaymentmethodsCC->getContent(), true);
	    
	      if($user->getPersonPerson()->getEmployer() != NULL && $user->getPersonPerson()->getEmployer()->getIdHighTech() != NULL ) {
		      $clientListPaymentmethodsCD = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:getEmployerPaymentMethods', array('accountNumber' => $user->getPersonPerson()->getEmployer()->getIdHighTech()), array('_format' => 'json'));
		      $responsePaymentsMethodsCD = json_decode($clientListPaymentmethodsCD->getContent(), true);
		      $realPayMethods=[];
		      if (isset($responsePaymentsMethodsCC["payment-methods"])) {
			      foreach ($responsePaymentsMethodsCC["payment-methods"] as $key=>$value ) {
				      $realPayMethods['0-'.$value["method-id"]]=array(
					      'payment-type'=>$value["payment-type"]==3?"VISA":"MasterC",
					      'account'=>$value["account"],
					      'method-id'=>'0-'.$value["method-id"],
					      'bank'=>'',
					      'id-provider'=>'0',
				      );
			      }
		      }
		      if (isset($responsePaymentsMethodsCD["payment-methods"])) {
			      foreach ($responsePaymentsMethodsCD["payment-methods"] as $key=>$value ) {
				      if($value["estado"]=="DISPONIBLE"){
					      /** @var Bank $bank */
					      $bank=$bankRepo->findOneBy(array('hightechCode'=>$value["codBanco"]));
					      $realPayMethods['1-'.$value["idCuenta"]]=array(
						      'payment-type'=>$value["tipoCuenta"]=="AH"?"Ahorros":"Corriente",
						      'account'=>$value["numeroCuenta"],
						      'method-id'=>'1-'.$value["idCuenta"],
						      'bank'=>$bank->getName(),
						      'id-provider'=>'1',
					      );
				      }
			      }
		      }
		      $rPM=array();
              foreach ($realPayMethods as $key => $realPayMethod) {
                  $rPM[]=$realPayMethod;
		      }
		
		      return $view->setStatusCode(200)->setData(array("payment-methods"=>$rPM));
	      }
	
	      return $view->setStatusCode(404);
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
    public function getDeletePayMethodAction($idPayM, $idUser)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPersonPerson()->getDocument() != $idUser) {
            $view = View::create();
            $view->setStatusCode(403);
            return $view;
        }
        $request = $this->container->get('request');
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber" => $idUser,
            "paymentMethodId" => $idPayM,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteClientPaymentMethod', array('_format' => 'json'));
        $view = View::create();
        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());

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
     * @param $idPurchaseOrder
     * @return View
     */
    public function getCalculateChargeAction($idPurchaseOrder)
    {
        /** @var User $user */
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrders $purchaseOrder */
        $purchaseOrderId = $idPurchaseOrder;
        $purchaseOrder = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($purchaseOrderId);
        /** @var Product $CT */
        $CT = $em->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array("simpleName" => "CT"));
        $view = View::create();
        $descriptions = $purchaseOrder->getPurchaseOrderDescriptions();
        $chargeDescription = new PurchaseOrdersDescription();
        $chargeDescription->setProductProduct($CT);
        $chargeDescription->setDescription($CT->getDescription());
        $chargeDescription->setValue(0);
        /** @var PurchaseOrdersDescription $desc */
        foreach ($descriptions as $desc) {
            $product = $desc->getProductProduct();
            //TODO is the new novo the same stuff
        }
        $purchaseOrder->addPurchaseOrderDescription($chargeDescription);
        $em->persist($purchaseOrder);
        $em->flush();
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * Disperse the POD again
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
     * @param $idPurchaseOrderDescription
     * @return View
     */
    public function getDispersePurchaseOrdersDescriptionAction($idPurchaseOrderDescription)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrdersDescription $purchaseOrderDesc */
        $purchaseOrderDesc = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->find($idPurchaseOrderDescription);
        $view = View::create();
        $user=$this->getUser();
        $person = $user->getPersonPerson();
        if($purchaseOrderDesc!=null&&$purchaseOrderDesc->getPurchaseOrders()!=null&&
            $purchaseOrderDesc->getPurchaseOrders()->getIdUser()!=null&&
            $purchaseOrderDesc->getPurchaseOrders()->getIdUser()->getId()==$user->getId()){
            $pays=$purchaseOrderDesc->getPayPay();
            if($pays==null){
                $dispersionAnswer=$this->disperseMoney($purchaseOrderDesc,$person);
                if($dispersionAnswer['code']!=200){
                    if($dispersionAnswer['code']==512){
                        return $view->setStatusCode(200)->setData($dispersionAnswer['data']);
                    }
                    //setting the id of the dispersion to rejected

                    $fechaRechazo = new DateTime();
                    $valor = $purchaseOrderDesc->getValue();
                    $employerPerson= $purchaseOrderDesc->getPurchaseOrders()->getIdUser()->getPersonPerson();
                    $rejectedProduct=$purchaseOrderDesc->getProductProduct();
                    $rejectedPOD=$purchaseOrderDesc;

                    $this->rejectProcess($purchaseOrderDesc);//ojo no enviar el correo antes de esto
                    $context=array(
                        'emailType'=>'failDispersion',
                        'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'toEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'userName'=>$employerPerson->getNames(),
                    );
                    $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                    $contextBack=array(
                        'emailType'=>'regectionDispersion',
                        'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'userName'=>$employerPerson->getNames(),
                        'rejectionDate'=>$fechaRechazo,
                        'toEmail'=> 'backOfficeSymplifica@gmail.com',
                        'phone'=>$employerPerson->getPhones()->first(),
                        'rejectedProduct'=>$rejectedProduct,
                        'idPOD'=>$rejectedPOD->getIdPurchaseOrdersDescription(),
                        'value'=>$valor
                    );
                    $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);
                    $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'-2'));
                    $purchaseOrderDesc->setPurchaseOrdersStatus($pos);
                    $em->persist($purchaseOrderDesc);
                    $em->flush();
                    return $view->setStatusCode($dispersionAnswer['code'])->setData($dispersionAnswer['data']);
                }else{
                    $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'00'));
                    $purchaseOrderDesc->setPurchaseOrdersStatus($pos);
                    $em->persist($purchaseOrderDesc);
                    $em->flush();
                    return $view->setStatusCode($dispersionAnswer['code'])->setData($dispersionAnswer['data']);

                }
            }else{
                $flag=false;
                /** @var Pay $pay */
                foreach ($pays as $pay) {
                    if($pay->getPurchaseOrdersStatusPurchaseOrdersStatus()==null||$pay->getPurchaseOrdersStatusPurchaseOrdersStatus()->getIdNovoPay()=="-1"){
                        $flag=true;
                        break;
                    }
                }
                if(!$flag){
                    $dispersionAnswer=$this->disperseMoney($purchaseOrderDesc,$person);
                    if($dispersionAnswer['code']!=200){
                        if($dispersionAnswer['code']==512){
                            return $view->setStatusCode(200)->setData($dispersionAnswer['data']);
                        }
                        //setting the id of the dispersion to rejected
                        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'-2'));
                        $purchaseOrderDesc->setPurchaseOrdersStatus($pos);
                        $em->persist($purchaseOrderDesc);
                        $em->flush();

                        $fechaRechazo = new DateTime();
                        $valor = $purchaseOrderDesc->getValue();
                        $employerPerson= $purchaseOrderDesc->getPurchaseOrders()->getIdUser()->getPersonPerson();
                        $rejectedProduct=$purchaseOrderDesc->getProductProduct();
                        $rejectedPOD=$purchaseOrderDesc;
                        $this->rejectProcess($purchaseOrderDesc);//ojo no enviar el correo antes de esto
                        $context=array(
                            'emailType'=>'failDispersion',
                            'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'toEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'userName'=>$employerPerson->getNames(),
                        );
                        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                        $contextBack=array(
                            'emailType'=>'regectionDispersion',
                            'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'userName'=>$employerPerson->getNames(),
                            'rejectionDate'=>$fechaRechazo,
                            'toEmail'=> 'backOfficeSymplifica@gmail.com',
                            'phone'=>$employerPerson->getPhones()->first(),
                            'rejectedProduct'=>$rejectedProduct,
                            'idPOD'=>$rejectedPOD->getIdPurchaseOrdersDescription(),
                            'value'=>$valor
                        );
                        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);
                        return $view->setStatusCode($dispersionAnswer['code'])->setData($dispersionAnswer['data']);
                    }else{
                        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'00'));
                        $purchaseOrderDesc->setPurchaseOrdersStatus($pos);
                        $em->persist($purchaseOrderDesc);
                        $em->flush();
                        return $view->setStatusCode($dispersionAnswer['code'])->setData(isset($dispersionAnswer['data'])?$dispersionAnswer['data']:array());

                    }
                }
            }
        }
        return $view->setStatusCode(403)->setData(array());


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
     * @param $idPurchaseOrder
     * @return View
     */
    public function getDispersePurchaseOrderAction($idPurchaseOrder)
    {

        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrders $purchaseOrder */
        $purchaseOrderId = $idPurchaseOrder;
        $purchaseOrder = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($purchaseOrderId);
        $view = View::create();
        $descriptions = $purchaseOrder->getPurchaseOrderDescriptions();
        /** @var User $user */
        $user = $purchaseOrder->getIdUser();
        $person = $user->getPersonPerson();
        $failFlag=true;
        $codes=array();
        /** @var PurchaseOrdersDescription $desc */
        foreach ($descriptions as $desc) {
            if($desc->getProductProduct()!=null&&$desc->getProductProduct()->getSimpleName()=="DIS"){
                continue;
            }
            $pays=$desc->getPayPay();
            if($pays==null){
                $dispersionAnswer=$this->disperseMoney($desc,$person);
                if($dispersionAnswer['code']!=200){
                    if($dispersionAnswer['code']==512){
                        continue;
                    }
                    //setting the id of the dispersion to rejected

                    $fechaRechazo = new DateTime();
                    $valor = $desc->getValue();
                    $employerPerson= $desc->getPurchaseOrders()->getIdUser()->getPersonPerson();
                    $rejectedProduct=$desc->getProductProduct();
                    $rejectedPOD=$desc;

                    $this->rejectProcess($desc);//ojo no enviar el correo antes de esto

                    $context=array(
                        'emailType'=>'failDispersion',
                        'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'toEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'userName'=>$employerPerson->getNames(),
                    );
                    $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                    $contextBack=array(
                        'emailType'=>'regectionDispersion',
                        'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                        'userName'=>$employerPerson->getNames(),
                        'rejectionDate'=>$fechaRechazo,
                        'toEmail'=> 'backOfficeSymplifica@gmail.com',
                        'phone'=>$employerPerson->getPhones()->first(),
                        'rejectedProduct'=>$rejectedProduct,
                        'idPOD'=>$rejectedPOD->getIdPurchaseOrdersDescription(),
                        'value'=>$valor
                    );
                    //$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);
                    $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'-2'));
                    $desc->setPurchaseOrdersStatus($pos);
                    $em->persist($purchaseOrder);
                    $em->persist($desc);
                    $em->flush();
                    $codes[$desc->getIdPurchaseOrdersDescription()]=$dispersionAnswer['code'];
                    $failFlag=false;
                }else{
                    $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'00'));
                    $desc->setPurchaseOrdersStatus($pos);
                }
            }else{
                $flag=false;
                /** @var Pay $pay */
                foreach ($pays as $pay) {
                    if($pay->getPurchaseOrdersStatusPurchaseOrdersStatus()==null||$pay->getPurchaseOrdersStatusPurchaseOrdersStatus()->getIdNovoPay()=="-1"){
                        $flag=true;
                        break;
                    }
                }
                if(!$flag){
                    $dispersionAnswer=$this->disperseMoney($desc,$person);
                    if($dispersionAnswer['code']!=200){
                        if($dispersionAnswer['code']==512){
                            continue;
                        }
                        //setting the id of the dispersion to rejected
                        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'-2'));
                        $desc->setPurchaseOrdersStatus($pos);
                        $em->persist($purchaseOrder);
                        $em->persist($desc);
                        $em->flush();

                        $fechaRechazo = new DateTime();
                        $valor = $desc->getValue();
                        $employerPerson= $desc->getPurchaseOrders()->getIdUser()->getPersonPerson();
                        $rejectedProduct=$desc->getProductProduct();
                        $rejectedPOD=$desc;
                        $this->rejectProcess($desc);//ojo no enviar el correo antes de esto
                        $context=array(
                            'emailType'=>'failDispersion',
                            'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'toEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'userName'=>$employerPerson->getFullName(),
                        );
                        //$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                        $contextBack=array(
                            'emailType'=>'regectionDispersion',
                            'userEmail'=>$rejectedPOD->getPurchaseOrders()->getIdUser()->getEmail(),
                            'userName'=>$employerPerson->getFullName(),
                            'rejectionDate'=>$fechaRechazo,
                            'toEmail'=> 'backOfficeSymplifica@gmail.com',
                            'phone'=>$employerPerson->getPhones()->first(),
                            'rejectedProduct'=>$rejectedProduct,
                            'idPOD'=>$rejectedPOD->getIdPurchaseOrdersDescription(),
                            'value'=>$valor
                        );
                        //$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);
                        $codes[$desc->getIdPurchaseOrdersDescription()]=$dispersionAnswer['code'];
                        $failFlag=false;
                    }else{
                        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'00'));
                        $desc->setPurchaseOrdersStatus($pos);
                    }
                }
            }
        }
        $em->persist($purchaseOrder);
        $em->flush();
        if($failFlag)
            $view->setStatusCode(200)->setData(array());
        else{
            $view->setStatusCode(400)->setData($codes);
        }
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
     * @param $idPurchaseOrder
     * @return View
     */
    public function getPayPurchaseOrderAction($idPurchaseOrder)
    {

        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrders $purchaseOrder */
        $purchaseOrderId = $idPurchaseOrder;
        $purchaseOrder = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($purchaseOrderId);
        $view = View::create();
        if($purchaseOrder==null){
            return $view->setStatusCode(404)->setData(array('purchaseOrder'=>"la orden de compra no existe"));
        }
        if($purchaseOrder->getAlreadyRecived()==1){
            $view->setStatusCode(200)->setData(array('alreadySent'=>'the confirmation was already sent to the server'));
            return $view;
        }
        /** @var User $user */
        $user = $purchaseOrder->getIdUser();
        $person = $user->getPersonPerson();
        $descriptions = $purchaseOrder->getPurchaseOrderDescriptions();
        $pmid=$purchaseOrder->getPayMethodId();
        $pmArray=explode('-',$pmid);
        $purchaseOrder->setPayMethodId($pmArray[1]);
        $purchaseOrder->setProviderId($pmArray[0]);
        $em=$this->getDoctrine()->getManager();
        $em->persist($purchaseOrder);
        $em->flush();
        $extractAnswer=$this->extractMoney($purchaseOrder,$person);
        if($extractAnswer['code']!=200){
            return $view->setStatusCode($extractAnswer['code'])->setData($extractAnswer['data']);
        }
        $view->setStatusCode(200)->setData(array());
        return $view;
    }

    /**
     * @param PurchaseOrdersDescription $purchaseOrderDescription
     * @param Person $person
     * @return mixed
     */
    private function disperseMoney($purchaseOrderDescription, $person)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $purchaseOrder=$purchaseOrderDescription->getPurchaseOrders();
//        if($purchaseOrder->getProviderId()==0){
//            $payRoll = $purchaseOrderDescription->getPayrollPayroll();
//            if ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PP") {
//                $type = 2;
//            } elseif ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PN") {
//                $type = 1;
//            } else {
//                return array('code'=>200);
//            }
//
//            $request->setMethod("POST");
//            $request->request->add(array(
//                "documentNumber" => $person->getDocument(),
//                "beneficiaryId" => $payRoll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getDocument(),
//                "beneficiaryAmount" => $purchaseOrderDescription->getValue(),
//                "dispersionType" => $type,
//                "chargeId" => $purchaseOrder->getIdPurchaseOrders(),
//            ));
//            $methodToCall='RocketSellerTwoPickBundle:PaymentsRest:postClientPayment';
//        }else{
            $payRoll = $purchaseOrderDescription->getPayrollPayroll();
            if($payRoll==null){
                $employeePerson=$purchaseOrder->getIdUser()->getPersonPerson();
                $payMethod=null;
            }else{
                //add the other contract reference
                $employeePerson=$payRoll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                $payMethod=$payRoll->getContractContract()->getPayMethodPayMethod();
            }

            $dateToSend=null;
            $filePila=null;
            $moraToAdd=0;
            if ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "CM") {
                //this goes with pilla so we continue
                return array('code'=>200);
            }
            if ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PP"||$purchaseOrderDescription->getProductProduct()->getSimpleName() == "SVR") {
                $accountType = "CC";
                if ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PP")
                    $bankCode="PL";
                else
                    $bankCode="PC";
                $paymentMethodAN="9999999";
                $documentTypeEmployee="NIT";
                $documentEmployee ="900862831";
                if($purchaseOrderDescription->getDateToPay()!=null){
                    $dateToday=new DateTime();
                    if($dateToday<$purchaseOrderDescription->getDateToPay()){
                        $dateToSend=$purchaseOrderDescription->getDateToPay();
                    }
                }
                if($purchaseOrderDescription->getEnlaceOperativoFileName()==null || $purchaseOrderDescription->getUploadedFile() != -1){
                    /** @var TwigSwiftMailer $smailer */
                    $smailer = $this->get('symplifica.mailer.twig_swift');
                    $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage(array('emailType'=>'backWarning','toEmail'=>'backofficesymplifica@symplifica.com','idPod'=>$purchaseOrderDescription->getIdPurchaseOrdersDescription()));
                    return array('code'=>512,'data'=>array('error' => array('Dispersion' => 'Backoffice no ha subido el número de pila')));
                }else{
                    $filePila=$purchaseOrderDescription->getEnlaceOperativoFileName();
                }
                //adding the mora if applies
                $pods = $purchaseOrderDescription->getPurchaseOrders()->getPurchaseOrderDescriptions();
                /** @var PurchaseOrdersDescription $pod */
                foreach ($pods as $pod) {
                    if($pod->getProductProduct()->getSimpleName()=="CM"){
                        $moraToAdd=$pod->getValue();
                        break;
                    }
                }

            } elseif ($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PN" || $purchaseOrderDescription->getProductProduct()->getSimpleName() == "PRM" ) {
                if($purchaseOrderDescription->getProductProduct()->getSimpleName() == "PRM" ){
                    $payMethod=$purchaseOrderDescription->getPrima()->getContractContract()->getPayMethodPayMethod();
                    $employeePerson=$purchaseOrderDescription->getPrima()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                }
                if($payMethod->getPayTypePayType()->getSimpleName()=="DAV"){
                    $accountType="DP";
                    $paymentMethodAN=$payMethod->getCellPhone();
                }else{
                    $accountType = $payMethod->getAccountTypeAccountType()->getName() == "Ahorros" ? "AH" : ($payMethod->getAccountTypeAccountType()->getName() == "Corriente" ? "CC" : "EN");
                    $paymentMethodAN=$payMethod->getAccountNumber();
                }
                $bankCode=$payMethod->getBankBank()->getHightechCode();
                $documentTypeEmployee=$employeePerson->getDocumentType();
                $documentEmployee =$employeePerson->getDocument();

            } else {
                $accountType ="AH";
                $bankCode="51";
                $paymentMethodAN="0550006200737432";//Symplifica bank account
                $documentTypeEmployee="NIT";
                $documentEmployee ="900862831";
            }
            $request->request->add(array(
                "accountNumber" => $person->getEmployer()->getIdHighTech(),
                "documentTypeEmployee" => $documentTypeEmployee,
                "documentEmployee" => $documentEmployee,
                "accountType" => $accountType,
                "accountBankNumber" => $paymentMethodAN,
                "bankCode" => $bankCode,
                "value" => $purchaseOrderDescription->getValue()+$moraToAdd,//this only applies to PilaPay
                "source" => $purchaseOrderDescription->getPurchaseOrders()->getProviderId()==1?100:101
            ));
            if($dateToSend!=null){
                //payment_date
                $request->request->add(array(
                    "payment_date" => $dateToSend->format("Y-m-d")
                ));
            }
            if($filePila!=null){
                $request->request->add(array(
                    "reference" => $filePila
                ));
            }

            $methodToCall='RocketSellerTwoPickBundle:Payments2Rest:postRegisterDispersion';
        //}

        $insertionAnswer = $this->forward($methodToCall,array('request'=>$request), array('_format' => 'json'));

//        if($purchaseOrder->getProviderId()==0) {
//            if ($insertionAnswer->getStatusCode() == 200) {
//                $transferId = json_decode($insertionAnswer->getContent(), true)["transfer-id"];
//                $pay = new Pay();
//                $pay->setUserIdUser($purchaseOrderDescription->getPurchaseOrders()->getIdUser());
//                $pay->setIdDispercionNovo($transferId);
//                $pay->setPurchaseOrdersDescription($purchaseOrderDescription);
//                $em->persist($pay);
//                $em->flush();
//                return array('code'=>200);
//
//            } else {
//                //TODO TIMEOUT
//                return array('code'=>$insertionAnswer->getStatusCode(),'data'=>array('error' => array('Dispersion' => 'se exedio el tiempo de espera pero el dinero se sacó')));
//            }
//        }else{
            if ($insertionAnswer->getStatusCode() == 200) {
                $transferId = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
                $pay = new Pay();
                $pay->setUserIdUser($purchaseOrderDescription->getPurchaseOrders()->getIdUser());
                $pay->setIdDispercionNovo($transferId);
                $pay->setPurchaseOrdersDescription($purchaseOrderDescription);
                $em->persist($pay);
                $em->flush();
                return array('code'=>200);

            } else {
                //TODO TIMEOUT
                return array('code'=>$insertionAnswer->getStatusCode(),'data'=>array('error' => $insertionAnswer->getContent()));
            }

        //}
    }
    /**
     * @param PurchaseOrders $purchaseOrder
     * @param Person $person
     * @return mixed
     */
    private function extractMoney($purchaseOrder, $person)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $pOSRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $flag=false;
        if($purchaseOrder->getPayMethodId()=="none"){
            //we inmidiatly disperse it to symplifica account
            $radicatedNumber=substr(md5($purchaseOrder->getIdPurchaseOrders()),0,18);
            $purchaseOrder->setRadicatedNumber($radicatedNumber);
            $pOS = $pOSRepo->findOneBy(array("idNovoPay" => "00"));
            $purchaseOrder->setPurchaseOrdersStatus($pOS);
            $purchaseOrder->setDatePaid(new DateTime());
            $em->persist($purchaseOrder);
            $em->flush();
            $request->setMethod("POST");
            $request->request->add(array(
                "numeroRadicado" => $radicatedNumber,
                "estado" => "0"
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:HighTechRest:postCollectionNotification',array("request"=>$request), array('_format' => 'json'));
            return array('code'=>$insertionAnswer->getStatusCode(),'data'=> $insertionAnswer->getContent());
        }
        if($purchaseOrder->getProviderId()==0){
//            $simpleName=$purchaseOrder->getPurchaseOrderDescriptions()->get(0)->getProductProduct()->getSimpleName();
//            if(!($simpleName=='PN'||$simpleName=='PP')){
//                $tax = $purchaseOrder->getPurchaseOrderDescriptions()->get(0)->getProductProduct()->getTaxTax();
//                $taxAmount=$purchaseOrder->getValue() - ($purchaseOrder->getValue() / ($tax->getValue() + 1));
//                $taxBase=$purchaseOrder->getValue() / ($tax->getValue() + 1);
//                $chargeMode=3;
//                $flag=true;
//            }else{
//                $chargeMode=2;
//                $taxAmount=0;
//                $taxBase=0;
//            }

            $request->request->add(array(
                "documentNumber" => $person->getDocument(),
                "methodId" => $purchaseOrder->getPayMethodId(),
                "totalAmount" => $purchaseOrder->getValue(),
                "taxAmount" => 0,
                "taxBase" => 0,
                "commissionAmount" => 0,
                "commissionBase" => 0,
                "chargeMode" => 2, //this is for transfers so wont change
                "chargeId" => $purchaseOrder->getIdPurchaseOrders(),
            ));
            $methodToCall='RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval';
        }else{
            $request->request->add(array(
                "accountNumber" => $person->getEmployer()->getIdHighTech(),
                "accountId" => $purchaseOrder->getPayMethodId(),
                "value" => $purchaseOrder->getValue(),
            ));
            $methodToCall='RocketSellerTwoPickBundle:Payments2Rest:postClientGscPayment';

        }
        $insertionAnswer = $this->forward($methodToCall,array("request"=>$request), array('_format' => 'json'));

        if($purchaseOrder->getProviderId()==0) {
            $answer = json_decode($insertionAnswer->getContent(), true);
            $chargeRC = isset($answer["charge-rc"])?$answer["charge-rc"]:"";
            if (!($insertionAnswer->getStatusCode() == 200 && ($chargeRC == "00" || $chargeRC == "08"))) {
                $pOS = $pOSRepo->findOneBy(array("idNovoPay" => $chargeRC));
                $purchaseOrder->setPurchaseOrdersStatus($pOS);
                $em->persist($purchaseOrder);
                $em->flush();
                return array('code'=>400,'data'=>array('error' => array("Credit Card" => "No se pudo hacer el cobro a la tarjeta de Credito", "charge-rc" => $chargeRC)));
            }else{
                //we inmidiatly disperse it to symplifica account
 	            $radicatedNumber=substr(md5($purchaseOrder->getIdPurchaseOrders()),0,18);
              $purchaseOrder->setRadicatedNumber($radicatedNumber);
              $pOS = $pOSRepo->findOneBy(array("idNovoPay" => "00"));
              $purchaseOrder->setPurchaseOrdersStatus($pOS);
              $purchaseOrder->setDatePaid(new DateTime());
              $em->persist($purchaseOrder);
              $em->flush();
              $request->setMethod("POST");
              $request->request->add(array(
                  "numeroRadicado" => $radicatedNumber,
                  "estado" => "0"
              ));
              $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:HighTechRest:postCollectionNotification',array("request"=>$request), array('_format' => 'json'));
              return array('code'=>$insertionAnswer->getStatusCode(),'data'=> $insertionAnswer->getContent());
            }
        }else{
            $answer = json_decode($insertionAnswer->getContent(), true);
            $radicatedNumber = isset($answer["numeroRadicado"])?$answer["numeroRadicado"]:"";
            if (!($insertionAnswer->getStatusCode() == 200)) {
                $pOS = $pOSRepo->findOneBy(array("idNovoPay" => 12));//Transaccion invalida
                $purchaseOrder->setPurchaseOrdersStatus($pOS);
                $em->persist($purchaseOrder);
                $em->flush();
                return array('code'=>$insertionAnswer->getStatusCode(),'data'=>array('error' => array("Credit Card" => "No se pudo hacer el cobro a la cuenta debito", "charge-rc" => 12)));
            }
            $purchaseOrder->setRadicatedNumber($radicatedNumber);
        }

        $pOS = $pOSRepo->findOneBy(array("idNovoPay" => "00"));
        $purchaseOrder->setPurchaseOrdersStatus($pOS);
        $purchaseOrder->setDatePaid(new DateTime());
        $em->persist($purchaseOrder);
        $em->flush();
        return array('code'=>200);

    }

    private function getInvoiceNumber()
    {
        $em = $this->getDoctrine()->getManager();
        $configRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config");
        /** @var \RocketSeller\TwoPickBundle\Entity\Config $ufg */
        $ufg = $configRepo->findOneBy(array(
            'name' => 'ufg'
        ));

        $newInvoiceNumber = $ufg->getValue()+1;

        $ufg->setValue($newInvoiceNumber);
        $em->persist($ufg);
        $em->flush();

        return $newInvoiceNumber;
    }

    private function rejectProcess(PurchaseOrdersDescription $pod)
    {
        $notification= new Notification();
        $notification->setAccion("Ver");
        $notification->setStatus("1");
        $notification->setDescription("El item de ". $pod->getProductProduct()->getName()." presentó un error");
        $notification->setType("alert");
        $notification->setPersonPerson($pod->getPurchaseOrders()->getIdUser()->getPersonPerson());
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $notification->setRelatedLink($this->generateUrl("show_pod_description" ,array(
            'idPOD'=>$pod->getIdPurchaseOrdersDescription(),
            'notifRef'=>$notification->getId())));
        $em->persist($notification);
        $em->flush();
    }
}
