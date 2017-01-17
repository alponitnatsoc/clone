<?php
namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class BackOfficeRestSecuredController extends FOSRestController
{

    /**
     * Creates a marketing lid that contains the referred information
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
    public function postCreateReferredAction(Request $request)
    {
        $requ = $request->request->all();
        $view = View::create();


        if(isset($requ['names'])&&isset($requ['cellphone'])){
            $cellphone = $requ['cellphone'];
            $names = $requ['names'];
		        $email = 'none';
		        if(isset($requ['email'])) {
		        	$email = $requ['email'];
		        }
            /** @var User $user */
            $user = $this->getUser();
            $newLid = new LandingRegistration();
            $newLid->setEmail($email);
            $newLid->setEntityType("persona");
            $newLid->setName($names);
            $newLid->setLastName(".R: ".$user->getCode());
            $newLid->setPhone($cellphone);
            $newLid->setType("REFERIDO");
            $newLid->setCreatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newLid);
            $em->flush();
            $view->setData(array())->setStatusCode(201);

            return $view;
        }

        $view->setData(array())->setStatusCode(400);

        return $view;
    }
  /**
   * retry pay pod <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "retry pay pod",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="idPod", nullable=false, strict=true, description="id purchase order description")
   *
   * @return View
   */
  public function postRetryPayPodAction(ParamFetcher $paramFetcher)
  {
    $idPod = $paramFetcher->get('idPod');
    /** @var User $user */
    $user = $this->getUser();
    $roles = $user->getRoles();
    $poRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
    /** @var PurchaseOrdersDescription $realPO */
    $realPO = $poRepo->find($idPod);
    $idAuthorized = false;
    $userFl = false;
    if($realPO != null && $user->getId() == $realPO->getPurchaseOrders()->getIdUser()->getId()) {
        $idAuthorized=true;
        $userFl=true;
    }
    foreach ($roles as $key => $role) {
        if($role == "ROLE_BACK_OFFICE")
            $idAuthorized = true;
    }

    if(!$idAuthorized){
      $view = View::create();
      $view->setStatusCode(401);
      return $view->setData(array());
    }

    $answer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getDispersePurchaseOrdersDescription',
                              ['idPurchaseOrderDescription' => $idPod]);
    if ($answer->getStatusCode() != 200) {
        $message = "not so good man";
    } else {
        $message = "all good man";
    }

    $view = View::create();
    if($userFl){
      $view->setStatusCode(200);
      return $view->setData(array('response' => $message, 'role' => "ROLE_USER"));
    } else {
      $view->setStatusCode(200);
      return $view->setData(array('response' => $message, 'role' => "ROLE_BACK_OFFICE"));
    }

  }

  /**
   *  return money paid in pod
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "return money paid in pod",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="idPod", nullable=false, strict=true, description="id purchase order description")
   *
   * @return View
   */
  public function postReturnMoneyPayAction(ParamFetcher $paramFetcher)
  {
    $idPod = $paramFetcher->get('idPod');
    $codesRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
    /** @var PurchaseOrdersDescription $pod */
    $pod = $codesRepo->find($idPod);
    /** @var User $user */
    $user=$this->getUser();
    $roles = $user->getRoles();
    /** @var PurchaseOrders $realPO */
    $realPO = $pod!=null ? $pod->getPurchaseOrders() : null;
    $idAuthorized=false;
    $userFl=false;
    if($realPO!=null && $user->getId()==$realPO->getIdUser()->getId()){
        $idAuthorized=true;
        $userFl=true;
    }
    foreach ($roles as $key=>$role) {
        if($role=="ROLE_BACK_OFFICE")
            $idAuthorized=true;
    }
    if(!$idAuthorized){
      $view = View::create();
      $view->setStatusCode(401);
      return $view->setData(array());
    }
    $idhightech = $pod->getPurchaseOrders()->getIdUser()->getPersonPerson()->getEmployer()->getIdHighTech();
    $targetAccount = $pod->getPurchaseOrders()->getPayMethodId();
    $value = $pod->getValue();
    $podStatusRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
    $devolutionState = $podStatusRepo->findOneBy(array("idNovoPay" => "-3"));
    $em=$this->getDoctrine()->getManager();

    $request = $this->container->get('request');
    $request->setMethod("POST");
    $request->request->add(array(
        "source" => 100,//by now change this when novopayment its in
        "accountNumber" => $idhightech,
        "accountId" => $targetAccount,
        "value" => $value
    ));
    $answer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterDevolution', array('request'=>$request), array('_format' => 'json'));

    if ($answer->getStatusCode() != 200) {
        $message = "not so good man";
    } else {
        $radicatedNumber = json_decode($answer->getContent(), true)["numeroRadicado"];

        $message = "all good man";
        $pod->setPurchaseOrdersStatus($devolutionState);
        $em->persist($pod);

        $productDevolucion = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")
            ->findOneBy(array('simpleName' => 'DEV'));
        $estatusAprobado = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")
            ->findOneBy(array('idNovoPay' => '00'));

        /** @var PurchaseOrdersDescription $podDevolucion */
        $podDevolucion = new PurchaseOrdersDescription();
        $podDevolucion->setValue($pod->getValue());
        $podDevolucion->setProductProduct($productDevolucion);
        $podDevolucion->setPurchaseOrdersStatus($estatusAprobado);
        $podDevolucion->setDescription('Devolución de '. $pod->getProductProduct()->getSimpleName() .
                                        ' pod: ' . $pod->getIdPurchaseOrdersDescription());
        $em->persist($podDevolucion);

        /** @var Pay $pay */
        $pay = new Pay();
        $pay->setIdDispercionNovo($radicatedNumber);
        $pay->setPurchaseOrdersDescription($podDevolucion);
        $pay->setUserIdUser($user);
        $em->persist($pay);

        $em->flush();
    }

    $view = View::create();
    if($userFl){
      $view->setStatusCode(200);
      return $view->setData(array('response' => $message, 'role' => "ROLE_USER"));
    } else {
      $view->setStatusCode(200);
      return $view->setData(array('response' => $message, 'role' => "ROLE_BACK_OFFICE"));
    }

  }
}
