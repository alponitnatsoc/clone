<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPExcel_Style_Border;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\EmailGroup;
use RocketSeller\TwoPickBundle\Entity\EmailInfo;
use RocketSeller\TwoPickBundle\Entity\EmailType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\PilaDetail;
use RocketSeller\TwoPickBundle\Entity\Prima;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\Severances;
use RocketSeller\TwoPickBundle\Entity\Supply;
use RocketSeller\TwoPickBundle\Entity\ToCall;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\addDocument;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use GuzzleHttp\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use ZipArchive;


class BackOfficeController extends Controller
{
    use SubscriptionMethodsTrait;

    /**
     * Funcion que carga la pagina de inicio del Back Office muestra un acceso rapido a:
     *      Tramites
     *      Consulta
     *      Asistencia Legal
     *      Registro Express
     *      Marketing
     * Solo tiene permiso de acceso el rol back_office
     * @return Response index /backoffice
     */
    public function indexAction()
    {
        if(!$this->isGranted('ROLE_BACK_OFFICE')){
            $this->createAccessDeniedException();
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }

    public function testSeverancesEmailAction()
    {
        if(!$this->isGranted('ROLE_BACK_OFFICE')){
            throw $this->createAccessDeniedException();
        }
        /** @var User $user */
        $user=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find(4);//gabriel user
        $em=$this->getDoctrine()->getManager();

        $shash = md5($user->getEmailCanonical());
        $user->setSHash($shash);
        $em->persist($user);
        $em->flush();
        $url =  $user->getSHash();
        $this->get("symplifica.mailer.twig_swift")->sendEmailByTypeMessage(array("emailType"=>"severancesAdvice","toEmail"=>$user->getEmail(),"redirectUrl"=>$url));

        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }

    public function showPODSeverancesAction(Request $request)
    {
        if(!$this->isGranted('ROLE_BACK_OFFICE')){
            throw $this->createAccessDeniedException();
        }
        $sever = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Severances")->findAll();
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showSeverances.html.twig',array('severances'=>$sever));
    }

    public function addPODSeverancesAction(Request $request)
    {
        if(!$this->isGranted('ROLE_BACK_OFFICE')){
            throw $this->createAccessDeniedException();
        }
        $didSomething=false;
        if(count($request->request->all())>0){
            $em=$this->getDoctrine()->getManager();
            $requ = $request->request->all();
            $filename = $requ["filename"];
            unset($requ["filename"]);
            foreach ($requ as $key=> $value) {

                /** @var EmployerHasEmployee $realEhe */
                $realEhe = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($key);
                if($realEhe==null)
                    continue;
                $contracts = $realEhe->getContracts();
                $realContract=null;
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    if($contract->getState()==1){
                        $realContract=$contract;
                    }
                }
                if($realContract==null){
                    continue;
                }
                /** @var User $realUser */
                $realUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson'=>$realEhe->getEmployerEmployer()->getPersonPerson()->getIdPerson()));
                $aSever=false;
                $Severs = $realContract->getSeverances();
                /** @var Severances  $pr */
                foreach ($Severs as $pr) {
                    if($pr->getYear()=="2017")
                        $aSever=true;
                }
                if($aSever==true)
                    break;
                $didSomething=true;
                //create the po and pod with the Sever
                $newPo = new PurchaseOrders();
                $newPOD = new PurchaseOrdersDescription();
                $SeverProduct = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array('simpleName'=>'SVR'));
                $pendingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'P1'));
                $newPOD->setProductProduct($SeverProduct);
                $newPOD->setValue($value);
                $newPOD->setDescription("Cesantías 2017");
                $newPOD->setPurchaseOrdersStatus($pendingStatus);
                $newPOD->setEnlaceOperativoFileName($filename);
                $newPOD->setUploadedFile(-1);
                $newPo->setPurchaseOrdersStatus($pendingStatus);
                $newPo->addPurchaseOrderDescription($newPOD);
                $realUser->addPurchaseOrder($newPo);
                //finally we add the Sever to the contract
                $Sever= new Severances();
                $Sever->setMonth("02");
                $Sever->setYear("2017");
                $Sever->setValue($value);
                $ehes = $realEhe->getEmployerEmployer()->getEmployerHasEmployees();
                /** @var EmployerHasEmployee $ehe */
                foreach ($ehes as $ehe) {
                    if($ehe->getState()>=4){
                        $actCont = $ehe->getActiveContract();
                        if($actCont)
                            $Sever->addContract($actCont);
                    }
                }


                $em->persist($realUser);
                $em->flush();
                $Sever->setPurchaseOrdersDescriptionPurchaseOrdersDescription($newPOD);
                $em->persist($Sever);
                $em->flush();

            }
        }
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->select('c')
            ->from('RocketSellerTwoPickBundle:Contract', 'c')
            ->where('c.startDate < ?1 and c.state = 1 ')->setParameter(1,"2017-01-01");
        $result = $qb->getQuery()->getResult();
        $pendingSeverances = array();
        /** @var Contract $contract */
        foreach ($result as $contract) {
            $skip=false;
            $severances = $contract->getSeverances();

            /** @var Severances $severance */
            foreach ($severances as $severance) {
                if($severance->getYear()==2017){
                    $skip=true;
                }
            }
            if(!$skip&&$contract->getEmployerHasEmployeeEmployerHasEmployee()->getState()>=4){
                $idEmployer=$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdEmployer();
                if(!isset($pendingSeverances[$idEmployer]))
                    $pendingSeverances[$idEmployer]= array();
                $pendingSeverances[$idEmployer][]=$contract->getEmployerHasEmployeeEmployerHasEmployee();
            }
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:addSeverances.html.twig', array('employers'=>$pendingSeverances));


    }
    public function emailSeverancesAction()
    {
        if(!$this->isGranted('ROLE_BACK_OFFICE')){
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->select('c')
            ->from('RocketSellerTwoPickBundle:Contract', 'c')
            ->where('c.startDate < ?1 and c.state = 1 ')->setParameter(1,"2017-01-01");
        $result = $qb->getQuery()->getResult();
        $array= array();
        $userRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var Contract $contract */
        foreach ($result as $contract) {
            if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getState()>=4){
                /** @var User $tUser */
                $tUser = $userRepo->findOneBy(array('personPerson'=>$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()));
                $array[$tUser->getId()]=$tUser;
            }
        }
        /** @var User $value */
        foreach ($array as $key => $value) {
            $shash = md5($value->getEmailCanonical());
            $value->setSHash($shash);
            $em->persist($value);
            $em->flush();
            $url =  $value->getSHash();
            $this->get("symplifica.mailer.twig_swift")->sendEmailByTypeMessage(array("emailType"=>"severancesAdvice","toEmail"=>$value->getEmail(),"redirectUrl"=>$url));
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }
    public function addPrimaAction(Request $request)
    {

        $didSomething=false;
        if(count($request->request->all())>0){
            $em=$this->getDoctrine()->getManager();
            $requ = $request->request->all();
            $lab=$requ["LAB"];
            $noLab=$requ["NOLAB"];
            unset($requ["LAB"]);
            unset($requ["NOLAB"]);
            foreach ($requ as $key=> $value) {

                /** @var EmployerHasEmployee $realEhe */
                $realEhe = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($key);
                if($realEhe==null)
                    continue;
                $contracts = $realEhe->getContracts();
                $realContract=null;
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    if($contract->getState()==1){
                        $realContract=$contract;
                    }
                }
                if($realContract==null){
                    continue;
                }
                /** @var User $realUser */
                $realUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson'=>$realEhe->getEmployerEmployer()->getPersonPerson()->getIdPerson()));
                $aPrima=false;
                $primas = $realContract->getPrimas();
                /** @var Prima  $pr */
                foreach ($primas as $pr) {
                    if($pr->getMonth()=="12"&&$pr->getYear()=="2016")
                        $aPrima=true;
                }
                if($aPrima==true)
                    break;
                $didSomething=true;
                //create the po and pod with the prima
                $newPo = new PurchaseOrders();
                $newPOD = new PurchaseOrdersDescription();
                $primaProduct = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array('simpleName'=>'PRM'));
                $pendingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay'=>'P1'));
                $newPOD->setProductProduct($primaProduct);
                $newPOD->setValue($value);
                $newPOD->setDescription("Prima Empleado ".$realEhe->getEmployeeEmployee()->getPersonPerson()->getFullName());
                $newPOD->setPurchaseOrdersStatus($pendingStatus);
                $newPo->setPurchaseOrdersStatus($pendingStatus);
                $newPo->addPurchaseOrderDescription($newPOD);
                $realUser->addPurchaseOrder($newPo);
                //finally we add the prima to the contract
                $prima= new Prima();
                $prima->setMonth("12");
                $prima->setYear("2016");
                $prima->setValue($value);
                $prima->setDateEnd(new DateTime("2016-12-31"));
                $datestart =new DateTime("2016-07-01");
                if($contract->getStartDate()>$datestart){
                    $datestart=$contract->getStartDate();
                }
                $prima->setDateStart($datestart);
                $aux=0;
                if($contract->getTransportAid()==0){
                    $aux=77700;
                }
                $prima->setTransportAid($aux);
                $prima->setWorked($lab);
                $prima->setNotWorked($noLab);
                $realContract->addPrima($prima);

                $em->persist($realUser);
                $em->flush();
                $prima->setPurchaseOrdersDescriptionPurchaseOrdersDescription($newPOD);
                $em->persist($realContract);
                $em->flush();

            }
        }
        $contracts = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->findBy(array('state'=>'1'));
        $realEHES=new ArrayCollection();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getState()>=4&&$contract->getPrimas()->count()==0){
                $realEHES->add($contract->getEmployerHasEmployeeEmployerHasEmployee());
            }
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:addPrima.html.twig' , array('ehes'=>$realEHES));
    }

    public function generateCodesAction($amount)
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->getRealProcedure();

        $codesTypeRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCodeType");
        $em=$this->getDoctrine()->getManager();
        $clientBetaReal=$codesTypeRepo->findOneBy(array("shortName"=>"CB"));
        $creating= new ArrayCollection();
        for($i=0;$i<$amount;$i++){
            $tempCode=new PromotionCode();
            $tempCode->setPromotionCodeTypePromotionCodeType($clientBetaReal);
            $em->persist($tempCode);
            $creating->add($tempCode);
        }
        $em->flush();
        /** @var PromotionCode $promC */
        foreach ($creating as $promC) {
            $promC->setCode(substr(md5($promC->getIdPromotionCode()),1,12));
            $em->persist($clientBetaReal);

        }
        $em->flush();
        return $this->redirectToRoute("show_un_active_codes");
    }

    public function showUnActiveCodesAction()
    {
        $codesRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCode");
        $codesTypeRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCodeType");
        $clientBetaReal=$codesTypeRepo->findOneBy(array("shortName"=>"CB"));
        $codes= $codesRepo->findBy(array("userUser"=>null,'promotionCodeTypePromotionCodeType'=>$clientBetaReal));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:promotionCodes.html.twig',array('codes'=>$codes));

    }

    public function showHaveToPayUsersAction()
    {
        $users = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        $dateNow= new DateTime();
        $response= new ArrayCollection();
        /** @var User $user */
        foreach ($users as $user) {
            $isFreeMonths = $user->getIsFree();
            if($user->getLastPayDate()==null)
                continue;
            if ($isFreeMonths > 0) {
                $isFreeMonths -= 1;
            }
            $isFreeMonths += 1;
            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($user->getLastPayDate()->format("Y-m-1")))));
            if($effectiveDate<=$dateNow){
                $response->add($user);
            }

        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:haveToPay.html.twig',array('users'=>$response));

    }

    public function showRejectedPODAction()
    {
        $codesRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        $podStatusRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $rejectedState = $podStatusRepo->findOneBy(array("idNovoPay" => "-2"));
        $rejectedPods = $codesRepo->findBy(array('purchaseOrdersStatus'=>$rejectedState));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:rejectedPurchaseOrdersDescriptions.html.twig',array('rejectedPods'=>$rejectedPods));

    }

    public function retryPayAction($idPO)
    {
        /** @var User $user */
        $user=$this->getUser();
        $roles = $user->getRoles();
        $poRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
        /** @var PurchaseOrders $realPO */
        $realPO = $poRepo->find($idPO);
        $flag=false;
        $userFl=false;
        if($realPO!=null && $user->getId()==$realPO->getIdUser()->getId()){
            $flag=true;
            $userFl=true;
        }
        foreach ($roles as $key=>$role) {
            if($role=="ROLE_BACK_OFFICE")
                $flag=true;
        }
        if(!$flag){
            $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
            return $this->redirectToRoute("show_rejected_pods");
        }
        $answer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getDispersePurchaseOrder', ['idPurchaseOrder' => $idPO]);
        if ($answer->getStatusCode() != 200) {
            $mesange = "not so good man";
        } else {
            $mesange = "all good man";
        }
        if($userFl){
            return $this->redirectToRoute("list_pods_description");
        }
        return $this->redirectToRoute("show_rejected_pods");
    }

    public function retryPayPODAction($idPOD)
    {
      $request = $this->container->get('request');
      $request->setMethod("POST");
      $request->request->add(array(
          "idPod" => $idPOD,
      ));
      $result = $this->forward('RocketSellerTwoPickBundle:BackOfficeRestSecured:postRetryPayPod', array('request'=>$request), array('_format' => 'json'));
      if($result->getStatusCode() == 401) {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        return $this->redirectToRoute("show_rejected_pods");
      }

      $user = $this->getUser();
      $poRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
      $realPO = $poRepo->find($idPOD);
      $userFl = false;
      if($realPO != null && $user->getId() == $realPO->getPurchaseOrders()->getIdUser()->getId()) {
          $idAuthorized=true;
          $userFl=true;
      }

      if($userFl){
          return $this->redirectToRoute("show_pod_description", array('idPOD'=>$idPOD));
      } else { // role = ROLE_BACK_OFFICE
        return $this->redirectToRoute("show_rejected_pods");
      }
    }
    public function showMoneyRequestsAction(Request $request)
    {
        $pending = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ToCall")->findAll();
        $uRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $eRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        $fixedPending = array();
        /** @var ToCall $item */
        foreach ($pending as $item) {
            if($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putUserPayBack"){
                $params = $item->getParameters();
                /** @var User $tUser */
                $tUser = $uRepo->find($params['user']);
                $fixedPending[$item->getIdToCall()]=array();
                $fixedPending[$item->getIdToCall()]['type']="PB";
                $fixedPending[$item->getIdToCall()]['person']=$tUser->getPersonPerson();
                $fixedPending[$item->getIdToCall()]['item']=$item;
                $topays = $params['toPay'];
                $value = 0;
                foreach ( $topays as $key => $topay) {
                    /** @var PurchaseOrdersDescription $tPod */
                    $tPod = $podRepo->find($topay);
                    if ($tPod != null)
                        $value+=$tPod->getValue();
                }
                $fixedPending[$item->getIdToCall()]['value']=$value;


            }elseif ($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putCreateRefundPurchaseOrder"){
                $params = $item->getParameters();
                /** @var Employer $tEmployer */
                $tEmployer = $eRepo->findOneBy(array('idHighTech'=>$params['account_number']));
                $fixedPending[$item->getIdToCall()]=array();
                $fixedPending[$item->getIdToCall()]['type']="RM";
                $fixedPending[$item->getIdToCall()]['person']= $tEmployer->getPersonPerson();
                $fixedPending[$item->getIdToCall()]['value']=$params['value'];
                $fixedPending[$item->getIdToCall()]['item']=$item;
            }
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showRequestMoney.html.twig', array('toApprove'=>$fixedPending));
    }
    public function showUsersToCreateMoneyRequestsAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findBy(array('status'=>2));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showUsersMoneyAction.html.twig', array(
            'users' => $users,
        ) );
    }
    public function createMoneyRequestsAction(Request $request, $idUser, $intend)
    {
        $requ = $request->request->all();

        /** @var User $rUser */
        $rUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find($idUser);
        if(count($requ)>1){
            $toEmail="camilo.mendez@symplifica.com";//This is the CEO
            if($intend=="p"){//Pagar
                $item= new ToCall();
                $item->setReasonToAuthorize($requ['reason']);
                $item->setService("RocketSellerTwoPickBundle:UtilsRest:putUserPayBack");
                $params= array();
                $params['user']=$rUser->getId();
                $params['methodTy']='PUT';
                unset($requ['reason']);
                $topay = array();
                foreach ($requ as $key => $value) {
                    $topay[]=$key;
                }
                $params['toPay']=$topay;
                $item->setParameters($params);
            }elseif ($intend=="r") {//Retornar Dinero
                $item= new ToCall();
                $item->setReasonToAuthorize($requ['reason']);
                $item->setService("RocketSellerTwoPickBundle:UtilsRest:putCreateRefundPurchaseOrder");
                $params= array();
                $params['source']='100';
                $params['account_number']=$rUser->getPersonPerson()->getEmployer()->getIdHighTech();
                $params['account_id']=explode("-",$requ['paymentMethod'])[1];
                $params['value']=$requ['devolutionValue'];
                $params['methodTy']='PUT';
                $item->setParameters($params);
            }
            $em=$this->getDoctrine()->getManager();
            $em->persist($item);
            $em->flush();

            /** test cesantCharges Email */
            $context=array(
                'emailType'=>'backMoneyRequ',
                'toEmail'=>$toEmail,
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

            return $this->redirectToRoute("show_requests_money");
        }


        if($intend=="p"){//Pagar
            $request->setMethod("GET");
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRestSecured:getPay', array("idUser" => $rUser->getId()), array('_format' => 'json'));
            if ($insertionAnswer->getStatusCode() != 200) {
                return $this->redirectToRoute("show_users_money");
            }
            $ANS=json_decode($insertionAnswer->getContent(), true);
            $ANS=json_decode($ANS, true);

            return $this->render('RocketSellerTwoPickBundle:BackOffice:requestActionMoney.html.twig', array(
                'ans' => $ANS,
            ) );

        }elseif ($intend=="r"){//Retornar Dinero
            $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $rUser->getId()), array('_format' => 'json'));
            $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true)["payment-methods"];

            return $this->render('RocketSellerTwoPickBundle:BackOffice:requestActionMoney.html.twig', array(
                'paym' => $responsePaymentsMethods,
            ) );
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:requestActionMoney.html.twig', array());

    }

    public function returnMoneyPayAction($idPOD)
    {
      $request = $this->container->get('request');
      $request->setMethod("POST");
      $request->request->add(array(
          "idPod" => $idPOD,
      ));
      $result = $this->forward('RocketSellerTwoPickBundle:BackOfficeRestSecured:postReturnMoneyPay', array('request'=>$request), array('_format' => 'json'));
      if($result->getStatusCode() == 401) {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        return $this->redirectToRoute("show_rejected_pods");
      }

      $user = $this->getUser();
      $poRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
      $realPO = $poRepo->find($idPOD);
      $userFl = false;
      if($realPO != null && $user->getId() == $realPO->getPurchaseOrders()->getIdUser()->getId()) {
          $idAuthorized=true;
          $userFl=true;
      }

      if($userFl){
          return $this->redirectToRoute("show_pod_description", array('idPOD'=>$idPOD));
      } else { // role = ROLE_BACK_OFFICE
        return $this->redirectToRoute("show_rejected_pods");
      }
    }

    public function showUsersLoginAction()
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $usersRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users= $usersRepo->findBy(array("status"=>2));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:usersBackLogin.html.twig',array('users'=>$users));

    }

    public function showUnfinishedUsersAction()
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $usersRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users= $usersRepo->findBy(array("status"=>1));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showUnfinishedUsers.html.twig',array('users'=>$users));

    }

    public function showBaseRegisterUsersAction($index)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $query */
        $query = $em->createQueryBuilder();
        $query->add('select', 'u');
        $query->from("RocketSellerTwoPickBundle:User",'u');
        $maxIndex = 1;
        $results = count($query->getQuery()->getResult());
        if($results%50!=0){
            $maxIndex = intval($results/50)+1;
        }else{
            $maxIndex = intval($results/50);
        }
        if($index==1){
            $query->setFirstResult(0);
            $query->setMaxResults(50);
            $paginator = new Paginator($query,$fetchJoinCollection = true);
            $users = $paginator->getIterator();
        }else{
            $query->setFirstResult(($index-1)*50);
            $query->setMaxResults(50);
            $paginator = new Paginator($query,$fetchJoinCollection = true);
            $users = $paginator->getIterator();
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showBaseRegisterUsers.html.twig',array(
            'users'=>$users,
            'maxIndex'=>$maxIndex,
            'index'=>$index,
        ));

    }

    public function showSuccessfulInvoicesAction($year,$month)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $usersRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users= $usersRepo->findAll();
        $efectivePurchaseOrders=new ArrayCollection();
        $dateToday=new DateTime($year."-".$month."-"."15");
        /** @var User $user */
        foreach ($users as $user) {
            $pos=$user->getPurchaseOrders();
            /** @var PurchaseOrders $po */
            foreach ($pos as $po) {
                if($po->getAlreadyRecived()==1&&$po->getPurchaseOrdersStatus()->getIdNovoPay()=="00"){
                    $datemin= new DateTime($po->getDateCreated()->format("Y-m-1"));
                    $datemax= new DateTime($po->getDateCreated()->format("Y-m-t"));
                    if($dateToday>=$datemin&&$dateToday<=$datemax)
                        $efectivePurchaseOrders->add($po);
                }
            }
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:showInvoices.html.twig',array('pos'=>$efectivePurchaseOrders));

    }

    public function addToNovoBackAction($user,$autentication)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $dm = $this->getDoctrine();
        $repo = $dm->getRepository('RocketSellerTwoPickBundle:User');
        /** @var User $user */
        $user = $repo->find($user);
        if (!$user) {
            throw $this->createNotFoundException('No demouser found!');
        }
        if($autentication==$user->getSalt()) {
            $this->addToNovo($user);
        }
        return $this->redirectToRoute("show_dashboard");


    }

    public function addToSQLEntitiesBackAction($user,$autentication, $idEhe)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $dm = $this->getDoctrine();
        $repo = $dm->getRepository('RocketSellerTwoPickBundle:User');
        /** @var User $user */
        $user = $repo->find($user);
        if (!$user) {
            throw $this->createNotFoundException('No demouser found!');
        }
        if($autentication==$user->getSalt()) {
            $repo = $dm->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
            /** @var EmployerHasEmployee $eHE */
            $eHE=$repo->find($idEhe);
            if($eHE==null){
                return false;
            }
            $actContract = null;
            /** @var Contract $c */
            foreach ($eHE->getContracts() as $c) {
                if ($c->getState() == 1) {
                    $actContract = $c;
                    break;
                }
            }
            $emEntities=$eHE->getEmployeeEmployee()->getEntities();
            $request = $this->container->get('request');
            /** @var EmployeeHasEntity $eEntity */
            foreach ($emEntities as $eEntity) {
                $entity = $eEntity->getEntityEntity();
                $eType = $entity->getEntityTypeEntityType();
                if ($eType->getPayrollCode() == "EPS" || $eType->getPayrollCode() == "ARS") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $eType->getPayrollCode() == "EPS" ? "2" : "1", //EPS ITS ALWAYS FAMILIAR SO NEVER CHANGE THIS
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
                if ($eType->getPayrollCode() == "AFP") {
                    if ($entity->getPayrollCode() == 0) {
                        $coverage = 2 ; //2 si es pensionado o  si no amporta
                    } else {
                        $coverage = 1;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $coverage, //the relation coverage from SQL
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        echo "Cago insertar entidad AFP " . $eHE->getIdEmployerHasEmployee() . " SC" . $insertionAnswer->getStatusCode();
                        die();
                        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
                        return $view;
                    }
                }
                if ($eType->getPayrollCode() == "FCES") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => "FCES",
                        "coverage_code" => 1, //DONT change this is forever and ever
                        "entity_code" => intval($entity->getPayrollCode()),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
            }
            $emEntities = $eHE->getEmployerEmployer()->getEntities();
            $flag = false;
            /** @var EmployerHasEntity $eEntity */
            foreach ($emEntities as $eEntity) {
                $entity = $eEntity->getEntityEntity();
                $eType = $entity->getEntityTypeEntityType();
                if ($eType->getPayrollCode() == "ARP") {
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => $actContract->getPositionPosition()->getPayrollCoverageCode(),
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
                if ($eType->getPayrollCode() == "PARAFISCAL") {
                    if (!$flag) {
                        $flag = true;
                    } else {
                        continue;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $eHE->getIdEmployerHasEmployee(),
                        "entity_type_code" => $eType->getPayrollCode(),
                        "coverage_code" => "1", //Forever and ever don't change this
                        "entity_code" => $entity->getPayrollCode(),
                        "start_date" => $actContract->getStartDate()->format("d-m-Y"),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddEmployeeEntity', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        return false;
                    }
                }
            }
        }
        return $this->redirectToRoute("show_dashboard");

    }

    public function addToSQLandHighTecBackAction($user,$autentication)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $dm = $this->getDoctrine();
        $repo = $dm->getRepository('RocketSellerTwoPickBundle:User');
        /** @var User $user */
        $user = $repo->find($user);
        if (!$user) {
            throw $this->createNotFoundException('No demouser found!');
        }
        if($autentication==$user->getSalt()) {
            //adding to sql
            $this->addToSQL($user);
            $ehes=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
            /** @var EmployerHasEmployee $ehe */
            foreach ( $ehes as $ehe ) {
                if($ehe->getState()>=4&&$ehe->getExistentSQL()!=1){
                    $this->addEmployeeToSQL($ehe);
                }
            }
            //adding to hightech (also creates the employer in the pila operator if needed)
            if($user->getPersonPerson()->getEmployer()->getIdHighTech()==null){
                $this->addToHighTech($user);
            }

        }
        return $this->redirectToRoute("show_dashboard");

    }

    public function demoLoginAction($user,$autentication)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $dm = $this->getDoctrine();
        $repo = $dm->getRepository('RocketSellerTwoPickBundle:User');
        /** @var User $user */
        $user = $repo->find($user);

        if (!$user) {
            throw $this->createNotFoundException('No demouser found!');
        }
        if($autentication==$user->getSalt()){
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());

            $context = $this->get('security.context');
            $context->setToken($token);

            $router = $this->get('router');
            $url = $router->generate('show_dashboard');

            return $this->redirect($url);
        }else{
          return $this->redirectToRoute("pages");
        }

    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function addToSQLAction                                       ║
     * ║ Creates employee in SQL and creates VAC actions               ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $idEmployerHasEmployee                        ║
     * ║  @param integer $procedureId                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function addToSQLAction($idEmployerHasEmployee,$procedureId){
        $em = $this->getDoctrine()->getManager();
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,"EmployerHasEmployee");
        $addToSQL = $this->addEmployeeToSQL($employerHasEmployee);
        if($addToSQL){
            $employerHasEmployee->setDateRegisterToSQL(new DateTime());
            $em->persist($employerHasEmployee);
            $em->flush();
            $this->addFlash("employee_added_to_sql", 'Exito al agregar el empleado a SQL');
            try {
                /** @var RealProcedure $procedure */
                $procedure = $this->loadClassById($procedureId,'RealProcedure');
                if($this->checkActionCompletion($employerHasEmployee,$procedure)){
                    $employerHasEmployee->setState(4);
                    $employerHasEmployee->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                    $employerHasEmployee->setDateFinished(new DateTime());
                    $em->persist($employerHasEmployee);
                    $em->flush();
                    $smailer = $this->get('symplifica.mailer.twig_swift');
                    $smailer->sendBackValidatedMessage($procedure->getUserUser(),$employerHasEmployee);
                    $contracts = $employerHasEmployee->getContracts();
                    /** @var Contract $contract */
                    foreach ($contracts as $contract) {
                        if($contract->getState()==1){
                            //we update the payroll
                            $activeP = $contract->getActivePayroll();
                            $dateNow=new DateTime();
                            if($contract->getStartDate()>$dateNow){
                                $realMonth=$contract->getStartDate()->format("m");
                                $realYear=$contract->getStartDate()->format("Y");
                                $realPeriod=intval($contract->getStartDate()->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                            }else{
                                $realMonth=$dateNow->format("m");
                                $realYear=$dateNow->format("Y");
                                $realPeriod=intval($dateNow->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                            }
                            $activeP->setMonth($realMonth);
                            $activeP->setYear($realYear);
                            $activeP->setPeriod($realPeriod);
                            $em->persist($activeP);
                            $em->flush();
                            break;
                        }
                    }
                    if($this->getNotificationByPersonAndOwnerAndDocumentType($procedure->getUserUser()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'))!= null){
                        /** @var Notification $notification */
                        $notification=$this->getNotificationByPersonAndOwnerAndDocumentType($procedure->getUserUser()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'));
                        if($notification->getAccion()=='Ver') {
                            /** @var EmployerHasEmployee $ehe */
                            $ehe = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->find(intval(explode('/', $notification->getRelatedLink())[3]));
                            if ($ehe != null and $ehe == $employerHasEmployee) {
                                if ($ehe->getExistentSQL() == 1) {
                                    /** @var Person $person */
                                    $person=$ehe->getEmployeeEmployee()->getPersonPerson();
                                    $contract = $ehe->getActiveContract();
                                    $flag = false;
                                    if ($ehe->getLegalFF() == 1) {
                                        $configurations = $ehe->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                        /** @var Configuration $config */
                                        foreach ($configurations as $config) {
                                            if ($config->getValue() == "PreLegal-SignedContract") {
                                                $flag = true;
                                                break;
                                            }
                                        }
                                    }
                                    $utils = $this->get('app.symplifica_utils');
                                    $notification->setAccion('Subir');
                                    if (!$flag) {
                                        $notification->setDownloadAction("Bajar");
                                        $notification->setDownloadLink($this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf')));
                                    }
                                    $notification->setDescription("Subir copia del contrato de " . $utils->mb_capitalize(explode(" ", $person->getNames())[0] . " " . $person->getLastName1()));
                                    $notification->setRelatedLink($this->generateUrl("documentos_employee", array('entityType' => 'Contract', 'entityId' => $contract->getIdContract(), 'docCode' => 'CTR')));
                                    $notification->activate();
                                }
                            }
                        }
                    }else{
                        $notification = $this->createNotificationByDocType($employerHasEmployee->getEmployerEmployer()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'));
                    }
                    if($employerHasEmployee->getActiveContract()->getDocumentDocument()){
                        if($employerHasEmployee->getActiveContract()->getDocumentDocument()->getMediaMedia()){
                            $notification->disable();
                            $this->addFlash("employee_contract_successfully", 'El empleado ya habia subido el contrato');
                        }else{
                            $notification->activate();
                            $this->addFlash("employee_contract_successfully", 'Éxito al generar la notificación del contrato');
                        }
                    }else{
                        $notification->activate();
                        $this->addFlash("employee_contract_successfully", 'Éxito al generar la notificación del contrato');
                    }
                    /** @var RealProcedure $vac */
                    $vac=$procedure->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
                    $vac->setProcedureStatus($this->getStatusByStatusCode('NEW'));
                    /** @var EmployerHasEntity $employerHasEntity */
                    foreach ($procedure->getEmployerEmployer()->getEntities() as $employerHasEntity) {
                        if(!$vac->getActionByEmployerHasEntity($employerHasEntity)->first()){
                            $tempAction = new Action();
                            $vac->addAction($tempAction);//adding the action to the procedure
                            $procedure->getUserUser()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                            $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                            $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                            $tempAction->setEmployerEntity($employerHasEntity);
                            if($employerHasEntity->getState()==1){
                                $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                            }else{
                                $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                            }
                            $tempAction->setUpdatedAt();//setting the action updatedAt Date
                            $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                            $em->persist($tempAction);
                            $em->flush();
                        }else{
                            $tempAction = $vac->getActionByEmployerHasEntity($employerHasEntity)->first();
                            if($employerHasEntity->getState()==1){
                                $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                            }else{
                                $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                            }
                            $tempAction->setUpdatedAt();//setting the action updatedAt Date
                            $em->persist($tempAction);
                            $em->flush();
                        }
                    }
                    /** @var EmployeeHasEntity $employeeEntity */
                    foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeEntity) {
                        if(!$vac->getActionByEmployeeHasEntity($employeeEntity)->first()){
                            $tempAction = new Action();
                            $vac->addAction($tempAction);//adding the action to the procedure
                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                            $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                            $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                            $tempAction->setEmployeeEntity($employeeEntity);
                            if($employerHasEntity->getState()==1){
                                $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                            }else{
                                $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                            }
                            $tempAction->setUpdatedAt();//setting the action updatedAt Date
                            $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                            $em->persist($tempAction);
                            $em->flush();
                        }else{
                            $tempAction = $vac->getActionByEmployeeHasEntity($employeeEntity)->first();
                            if($employerHasEntity->getState()==1){
                                $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                            }else{
                                $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                            }
                            $tempAction->setUpdatedAt();//setting the action updatedAt Date
                            $em->persist($tempAction);
                            $em->flush();
                        }
                    }
                    if(!$vac->getActionsByPersonAndActionType($employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VC'))->first()){
                        $tempAction = new Action();
                        $vac->addAction($tempAction);//adding the action to the procedure
                        $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                        $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                        $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('vc'));//setting actionType to validate entity
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $tempAction->setUpdatedAt();//setting the action updatedAt Date
                        $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                        $em->persist($tempAction);
                        $em->flush();
                    }else{
                        $tempAction = $vac->getActionsByPersonAndActionType($employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VC'))->first();
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $tempAction->setUpdatedAt();//setting the action updatedAt Date
                        $em->persist($tempAction);
                        $em->flush();
                    }
                    $em->persist($notification);
                    $em->flush();
                    $this->addFlash("employee_ended_successfully", 'Éxito al dar de alta al empleado');
	                  $this->claimPromotioByReferidor($employerHasEmployee);
                    return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
                }else{
                    $this->addFlash("employee_ended_faild", 'No se han terminado todos los tramites para este empleado.');
                }
            }catch(Exeption $e){
                $this->addFlash("employee_ended_faild", 'Ocurrio un error terminando el empleado: '. $e);
                return $this->redirectToRoute('show_procedure',array('procedureId'=>$procedureId));
            }

        }else{
            $employerHasEmployee->setDateTryToRegisterToSQL(new DateTime());
            $em->persist($employerHasEmployee);
            $em->flush();
            $this->addFlash("employee_added_to_sql_failed", 'No se pudo agregar el empleado a SQL');
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }
	
		/**
		 * give referidor money or free month
		 */
		private function claimPromotioByReferidor(EmployerHasEmployee $eHE)
		{
			$person = $eHE->getEmployerEmployer()->getPersonPerson();
			/** @var User $user */
			$user = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")
				->findOneBy(array('personPerson' => $person));
			$em = $this->getDoctrine()->getManager();
			$promoTypeRef = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCodeType")
				->findOneBy(array('shortName' => 'RF'));
			/** @var Campaign $campaing */
			$campaing = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Campaign")
				->findOneBy(array('description' => 'RefCamp'));
			if (!$user->getPromoCodeClaimedByReferidor()) {
				/** @var PromotionCode $promoCode */
				foreach ($user->getPromoCodes() as $promoCode) {
					if ($promoCode->getPromotionCodeTypePromotionCodeType() == $promoTypeRef) {
						/** @var User $userReferidor */
						$userReferidor = $promoCode->getUserUser();
						if ($campaing->getEnabled() == 1) {
							//stock in this campaing is used to have a database value of the campaing
							$userReferidor->setMoney($userReferidor->getMoney() + $campaing->getStock());
						} else {
							$userReferidor->setIsFree($userReferidor->getIsFree() + 1);
						}
						$em->persist($userReferidor);
						$user->setPromoCodeClaimedByReferidor(true);
						$em->persist($user);
						$em->flush();
						break;
					}
				}
			}
		}

    //todo old function test an remove Andres
    /**
     * Funcion que valida la informacion del empleado
     * @param $idAction
     * @return Response
     */
    public function checkRegisterAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
    	/** @var Person $person */
        $person = $action->getPersonPerson();
    	/** @var User $user */
        $user =  $action->getUserUser();

        /** @var Employee $employee */
        $employee = $person->getEmployee();
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocumentDocument();
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }

        if ($employee) {
            $employerHasEmployee = $this->loadClassByArray(
                    array(
                        "employeeEmployee" =>$employee,
                        "employerEmployer" =>$employer,
                    ),"EmployerHasEmployee");
        }else{
            $employerHasEmployee = null;
        }
        if($employerHasEmployee == null){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula));
        }else{
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkEmployee.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee,'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula));
        }
    }

    //todo old function test an remove Andres
    /**
     * Funcion para poder consultar la informacion del empleador psterior a validar la informacion de registro
     * @param $idAction
     * @return Response
     */
    public function checkInfoAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();

        /** @var Employee $employee */
        $employee = $person->getEmployee();
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();

        if ($employee) {
            $employerHasEmployee = $this->loadClassByArray(
                array(
                    "employeeEmployee" =>$employee,
                    "employerEmployer" =>$employer,
                ),"EmployerHasEmployee");
        }else{
            $employerHasEmployee = null;
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:checkInfo.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee));
    }

    //todo old function test an remove Andres
    /**
     * Funcion para consultar la informacion del empleado posterior a validar la informacion registrada
     * @param $idAction
     * @return Response
     */
    public function checkInfoEmployeeAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();

        /** @var Employee $employee */
        $employee = $person->getEmployee();
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();

        if ($employee) {
            $employerHasEmployee = $this->loadClassByArray(
                array(
                    "employeeEmployee" =>$employee,
                    "employerEmployer" =>$employer,
                ),"EmployerHasEmployee");
        }else{
            $employerHasEmployee = null;
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:checkInfoEmployee.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee));
    }

    //todo old function test an remove Andres
    /**
     * Funcion para validar los documentos del empleador
     * @param $idAction
     * @return Response
     */
    public function checkDocumentsAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();

        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocumentDocument();
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $rut = $action->getPersonPerson()->getRutDocument();
        if ($rut) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathRut = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }else{
                $pathRut = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
    }

    //todo old function test an remove Andres
    /**
     * Funcion para validar el mandato del empleador
     * @param $idAction
     * @return Response
     */
    public function validateMandatoryAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();
        /** @var Document $cedula */
        $mandato = $action->getPersonPerson()->getEmployer()->getMandatoryDocument();
        if ($mandato) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathMandato = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference');
                $nameMandato = $mandato->getMediaMedia()->getName();
            }else{
                $pathMandato = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference');
                $nameMandato = $mandato->getMediaMedia()->getName();
            }
        }else{
            $pathMandato='';
            $nameMandato='';
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateMandato.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'mandato'=>$mandato,'path_document'=>$pathMandato,'nameDoc'=>$nameMandato));
    }


    /**
     * Funcion para validar el contrato del empleado
     * @param $idAction
     * @return Response
     */
    public function validateContractAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();
        /** @var Employee $employee */
        $employee = $action->getPersonPerson()->getEmployee();
        /** @var Employer $employer */
        $employer = $action->getUserUser()->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $eHE = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array('employerEmployer'=>$employer,'employeeEmployee'=>$employee));
        /** @var Contract $contract */
        $contract = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('employerHasEmployeeEmployerHasEmployee'=>$eHE,'state'=>1));
        if($contract)
            $docContrato = $contract->getDocumentDocument();
        if ($docContrato) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathContrato = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($docContrato->getMediaMedia(), 'reference');
                $nameContrato = $docContrato->getMediaMedia()->getName();
            }else{
                $pathContrato = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($docContrato->getMediaMedia(), 'reference');
                $nameContrato = $docContrato->getMediaMedia()->getName();
            }
        }else{
            $pathContrato='';
            $nameContrato='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateContract.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'contrato'=>$docContrato,'path_document'=>$pathContrato,'nameDoc'=>$nameContrato, 'contract'=>$contract));
    }

    //todo old function test an remove Andres
    public function viewDocumentsAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();

        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocumentDocument();
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $rut = $action->getPersonPerson()->getRutDocument();
        if ($rut) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathRut = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }else{
                $pathRut = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
    }

    //todo old function test an remove Andres
    /**
     * Funcion para validar los documentos de cada empleado
     * @param $idAction
     * @return Response
     */
    public function checkEmployeeDocumentsAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Employee $employee */
        $employee = $person->getEmployee();
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocumentDocument();
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $eHE = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array('employeeEmployee'=>$employee,'employerEmployer'=>$employer));
        if($eHE)
            $carta = $eHE->getAuthDocument();
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta,'eHE'=>$eHE));
    }

    //todo old function test an remove Andres
    public function viewEmployeeDocumentsAction($idAction)
    {
        /** @var Action $action */
        $action = $this->loadClassById($idAction,"Action");
        /** @var Person $person */
        $person = $action->getPersonPerson();
        /** @var User $user */
        $user =  $action->getUserUser();
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Employee $employee */
        $employee = $person->getEmployee();
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocumentDocument();
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $eHE = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array('employeeEmployee'=>$employee,'employerEmployer'=>$employer));
        if($eHE)
            $carta = $eHE->getAuthDocument();
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = '//'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = '//' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta,'eHE'=>$eHE));
    }

    //todo old function test an remove Andres
    public function reportErrorAction($idAction,Request $request)
    {
        /** @var Action $action */
    	$action = $this->loadClassById($idAction,"Action");
    	if ($request->getMethod() == 'POST') {
    		$description = $request->request->get('description');
    		$actionError = new ActionError();
    		$actionError->setDescription($description);
            $actionError->setStatus('Sin contactar');
    		$action->addActionErrorActionError($actionError);
    		$action->setStatus("Error");
		   	$em = $this->getDoctrine()->getManager();
		    $em->persist($actionError);
		    $em->persist($action);
		    $em->flush();

		    return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    	}else{
    		return $this->render('RocketSellerTwoPickBundle:BackOffice:reportError.html.twig',array('idAction'=>$idAction));
    	}

    }

    public function registerExpressAction()
    {
        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository('RocketSellerTwoPickBundle:Role')
                ->findOneByName("ROLE_BACK_OFFICE");
        $notifications = $em->getRepository('RocketSellerTwoPickBundle:Notification')
                ->findBy(array("roleRole" => $role,
                                        "type"=>"Registro express"));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:registerExpress.html.twig',array('notifications'=>$notifications));
    }

    public function legalAssistanceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository('RocketSellerTwoPickBundle:Role')
                ->findOneByName("ROLE_BACK_OFFICE");
        $notifications = $em->getRepository('RocketSellerTwoPickBundle:Notification')
                ->findBy(array("roleRole" => $role,
                                        "type"=>"Asistencia legal"));
        return $this->render('RocketSellerTwoPickBundle:BackOffice:legalAssistance.html.twig',array('notifications'=>$notifications));
    }

    /**
     * hace un query de la clase para instanciarla
     * @param  [type] $parameter id que desea pasar
     * @param  [type] $entity    entidad a la cual hace referencia
     */
    public function loadClassById($parameter, $entity)
    {
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->find($parameter);
		return $loadedClass;
    }

    /**
     * hace un query de la clase para instanciarla
     * @param  [type] $array  array de parametros que desea pasar
     * @param  [type] $entity entidad a la cual hace referencia
     */
    public function loadClassByArray($array, $entity)
    {
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->findOneBy($array);
		return $loadedClass;
    }

    /**
     * Funcion que muestra la tabla de registrados en el landing
     * @return Response /backoffice/marketing
     */
    public function showLandingAction()
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $landings = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:LandingRegistration')
            ->findAll();
        return $this->render('RocketSellerTwoPickBundle:BackOffice:marketing.html.twig', array('landings'=>array_reverse($landings)));
    }

    public function showExpressAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $landings = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:LandingRegistration')
            ->findBy(array('type'=>'0Esfuezo'));
        if($id==-1){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:marketing.html.twig', array('landings'=>array_reverse($landings),'express'=>'active'));
        }else{
            /** @var LandingRegistration $lidRegister */
            $lidRegister = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:LandingRegistration')
                ->find($id);
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');
            $tempoUser = $userManager->findUserBy(array('username'=>$lidRegister->getEmail()));
            $em=$this->getDoctrine()->getManager();
            if($tempoUser==null){
                $user = $userManager->createUser();
                $user->setEmail($lidRegister->getEmail());
                $user->setUsername($lidRegister->getEmail());
                $user->setConfirmationToken(null);
                $user->setPlainPassword($lidRegister->getPhone());
                $user->setEnabled(true);
                $userManager->updateUser($user);
                $person = new Person();
                $explode = explode(" ",$lidRegister->getLastName());
                $person->setNames($lidRegister->getName());
                if(count($explode)>1){
                    $person->setLastName2($explode[1]);
                }
                $person->setLastName1($explode[0]);
                $phone= new Phone();
                $phone->setPhoneNumber($lidRegister->getPhone());
                $person->addPhone($phone);
                $user->setPersonPerson($person);
                $lidRegister->setState(1);
                /** @var User $user */
                $em->persist($lidRegister);
                $em->persist($person);

            }
            //TODO-falta enviar el correo



            $em->flush();
            return $this->redirectToRoute("express_back",array('id'=>-1),301);
        }

    }

    public function addPlanillaTypeToContractsBackAction($autentication)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $user = $this->getUser();

        if($autentication==$user->getSalt()) {
          $dm = $this->getDoctrine();
          $em=$this->getDoctrine()->getManager();
          $contracts = $dm->getRepository('RocketSellerTwoPickBundle:Contract')->findAll();

          $planillaTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PlanillaType');
          $calculatorConstraintsRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:CalculatorConstraints');
          $minWage = $calculatorConstraintsRepo->findOneBy(array("name" => "smmlv"));
          $minWage = $minWage ->getValue();

          foreach ($contracts as $contract) {

            if( $contract->getEmployerHasEmployeeEmployerHasEmployee()->getState() >= 4){
              $realSalary = 0;
              if($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD"){
                $realSalary = $contract->getSalary() /*/ $contract->getWorkableDaysMonth()*/;
                //$realSalary = $realSalary * (($contract->getWorkableDaysMonth() / 4) * 4.34523810);
              }

              // Logic to determine the contract planilla type
              if($contract->getTimeCommitmentTimeCommitment()->getCode() == "XD" && $contract->getSisben() == 1 && $realSalary < $minWage){
                $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "E"));
                $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
              }
              else {
                $planillaTypeToSet = $planillaTypeRepo->findOneBy(array("code" => "S"));
                $contract->setPlanillaTypePlanillaType($planillaTypeToSet);
              }

              $em->persist($contract);
            }

          }

          $em->flush();

          return $this->redirectToRoute("back_office");
        }

        return $this->redirectToRoute("show_dashboard");
    }

		public function clearDataAfterBackupAction($autentication)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $user = $this->getUser();

        if($autentication==$user->getSalt() && $this->getParameter('ambiente') == "desarrollo") {

          $dm = $this->getDoctrine();
          $em=$this->getDoctrine()->getManager();

          $workplaces = $dm->getRepository('RocketSellerTwoPickBundle:Workplace')->findAll();
          foreach ($workplaces as $index => $workplace) {
            if($workplace->getName() != ""){
              $workplace->setName("Generated Name #" . $index);
              $workplace->setMainAddress("Generated Address #" . $index);
              $em->persist($workplace);
            }
          }

          $userManager = $this->get('fos_user.user_manager');
          $allUsers = $userManager->findUsers();

          foreach( $allUsers as $index => $user){
            if($user->getUsername() != "Admin" && $user->getUsername() != "Back"){
              $newUsername = "dummy" . $index . "@fake.org";
              if($user->getUsername() != ""){
                $user->setUsername($newUsername);
                $user->setUsernameCanonical($newUsername);
                $user->setEmail($newUsername);
                $user->setEmailCanonical($newUsername);
                $user->setPlainPassword("Symplifica2016");
                $user->setFacebookId(NULL);
                $user->setGoogleId(NULL);
                $user->setLinkedinId(NULL);
                $user->setFacebookAccessToken(NULL);
                $user->setGoogleAccessToken(NULL);
                $user->setLinkedinAccessToken(NULL);
              }
              $userManager->updateUser($user, true);
            }
          }

          $pods = $dm->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findAll();
          foreach ($pods as $pod) {
            if(!is_null($pod->getProductProduct())){
              $pod->setDescription($pod->getProductProduct()->getName());
              $em->persist($pod);
            }
          }

          $promCodes = $dm->getRepository('RocketSellerTwoPickBundle:PromotionCode')->findAll();
          foreach ($promCodes as $promCode) {
            if(is_null($promCode->getUserUser()) && $promCode->getCode() != "BACKDOOR"){
              $em->remove($promCode);
            }
          }

          $phones = $dm->getRepository('RocketSellerTwoPickBundle:Phone')->findAll();
          foreach ($phones as $phone) {
            $phone->setPhoneNumber("3309999999");
            $em->persist($phone);
          }

          $persons = $dm->getRepository('RocketSellerTwoPickBundle:Person')->findAll();
          $docToStart = "";
          foreach ($persons as $index => $person) {
            $docToStart = $person->getDocument();
          }

          //If already have resetted the database, continues from the last generated document, otherwise starts at 712700
          if(abs(712700 - intval($docToStart)) > 20000){
            $docToStart = 712700;
          }

          foreach ($persons as $index => $person) {
            $newName = "Fake Name" . $index;
            $newLastName1 = "FakeLastOne" . $index;
            $newLastName2 = "FakeLastTwo" . $index;
            $person->setNames($newName);
            $person->setLastName1($newLastName1);
            $person->setLastName2($newLastName2);
            if(!is_null($person->getDocumentType())){
              $person->setDocument(strval( intval($docToStart) + $index ));
              $person->setDocumentExpeditionDate(new \DateTime('2000-01-01'));
              $person->setBirthDate(new \DateTime('1982-01-01'));
            }
            if(!is_null($person->getEmail())){
              $newMail = "dummy" . $index . "@fake.org";
              $person->setEmail($newMail);
            }
            if(!is_null($person->getMainAddress())){
              $person->setMainAddress("Generated Address #" . $index);
            }
            $em->persist($person);
          }

          $payMethods = $dm->getRepository('RocketSellerTwoPickBundle:PayMethod')->findAll();
          foreach ($payMethods as $payMethod) {
            if(!is_null($payMethod->getAccountNumber()) && strlen($payMethod->getAccountNumber()) > 0){
              $accLenght = strlen($payMethod->getAccountNumber());
              $indexToReplace = rand(0,$accLenght-1);
              $numberToSet = rand(0,9);
              $newAccountNumber = $payMethod->getAccountNumber();
              $newAccountNumber[$indexToReplace] = strval($numberToSet);
              $payMethod->setAccountNumber($newAccountNumber);
            }
            if($payMethod->getCellPhone() != "0"){
              $payMethod->setCellPhone("3209999999");
            }
            $em->persist($payMethod);
          }

          $notifications = $dm->getRepository('RocketSellerTwoPickBundle:Notification')->findAll();
          foreach ($notifications as $notification) {
            $notification->setTitle(NULL);
            $newDescription = $notification->getAccion() . " algo relacionado a " . $notification->getPersonPerson()->getNames();
            $notification->setDescription($newDescription);
            $em->persist($notification);
          }

          $referreds = $dm->getRepository('RocketSellerTwoPickBundle:Referred')->findAll();
          foreach($referreds as $referred){
            $referred->setUserId(NULL);
            $referred->setReferredUserId(NULL);
            $referred->setInvitationId(NULL);
            $em->persist($referred);
          }

          $landingRegisters = $dm->getRepository('RocketSellerTwoPickBundle:LandingRegistration')->findAll();
          foreach ($landingRegisters as $landingRegister) {
            $em->remove($landingRegister);
          }

          $invitations = $dm->getRepository('RocketSellerTwoPickBundle:Invitation')->findAll();
          foreach ($invitations as $invitation) {
            $em->remove($invitation);
          }

          $employers = $dm->getRepository('RocketSellerTwoPickBundle:Employer')->findAll();
          foreach ($employers as $employer) {
            $employer->setIdHighTech(NULL);
            $em->persist($employer);
          }

          $ehes = $dm->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findAll();
          foreach ($ehes as $ehe) {
            if( !is_null($ehe->getExistentHighTec()) ){
              $ehe->setExistentHighTec(0);
            }
            $em->persist($ehe);
          }

          $em->flush();

          $usersToHT = $dm->getRepository('RocketSellerTwoPickBundle:User')->findAll();
          foreach ($usersToHT as $singleUser) {
            if( $singleUser->getStatus() == 2){
              $this->addToHighTech($singleUser);
            }
          }

            return $this->redirect($this->generateUrl('back_office'));
        }
        return $this->redirect($this->generateUrl('show_dashboard'));
    }

    public function testEmailAction(){

        $toEmail = "alponitnatsnoc@gmail.com.com";

        /** test confirmation Email */
        $context=array(
            'emailType'=>'confirmation',
            'user'=>$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(1419),
        );
        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test welcome Email*/
//        $context = array(
//            'emailType'=>'welcome',
//            'user'=>$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(3),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test reminder Email */
//        $context=array(
//            'emailType'=>'reminder',
//            'toEmail'=>$toEmail,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test help Email */
//        $context=array(
//            'emailType'=>'help',
//            'name' => 'Andrés Felipe',
//            'subject'=>'prueba',
//            'fromEmail' =>$toEmail,
//            'message' =>'Prueba email de ayuda publico',
//            'ip'=> '127.0.0.1',
//            'phone'=>'3009999999'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test helpPrivate Email */
//        $context=array(
//            'emailType'=>'helpPrivate',
//            'name' => 'Andrés Felipe',
//            'subject'=>'prueba',
//            'fromEmail' =>$toEmail,
//            'message' =>'Prueba email de ayuda publico',
//            'ip'=> '127.0.0.1',
//            'phone'=>'3009999999'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test daviPlata Email */
//        $context = array(
//            'emailType'=>'daviplata',
//            'toEmail'=>$toEmail,
//            'user'=>$this->getUser(),
//            'subject'=>'Información Daviplata',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test oneDay Email */
//        $this->get('symplifica.mailer.twig_swift')
//            ->sendOneDayMessage(
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5),
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5)->getPersonPerson()->getEmployer()->getEmployerHasEmployees()->first());
//
//        /** test diasHabiles Email */
//        $this->get('symplifica.mailer.twig_swift')
//            ->sendDiasHabilesMessage(
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5),
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5)->getPersonPerson()->getEmployer()->getEmployerHasEmployees()->first());
//
//        /** test backval Email */
//        $this->get('symplifica.mailer.twig_swift')
//            ->sendBackValidatedMessage(
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5),
//                $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5)->getPersonPerson()->getEmployer()->getEmployerHasEmployees()->first());
//
//        /** test reminderPay Email */
//        $context = array(
//            'emailType'=>'reminderPay',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'days'=>3,
//            'isEfectivo'=>true,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test reminderPay Email */
//        $context = array(
//            'emailType'=>'reminderPay',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'days'=>2,
//            'isEfectivo'=>false,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test lastReminderPay Email */
//        $context = array(
//            'emailType'=>'lastReminderPay',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'days'=>2
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test ReminderDaviplata Email */
//        $context = array(
//            'emailType'=>'reminderDaviplata',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'employeeName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test succesRecollect Email */
//        /** @var \DateTime $date */
//        $date = new DateTime();
//        $date->setTimezone(new \DateTimeZone('America/Bogota'));
//        $params = array(
//            'ref'=> 'factura',
//            'id' => 19,
//            'type' => 'pdf',
//            'attach' => null
//        );
//        $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
//        $file =  $documentResult->getContent();
//        if (!file_exists('uploads/temp/facturas')) {
//            mkdir('uploads/temp/facturas', 0777, true);
//        }
//        $path = 'uploads/temp/facturas/'.$this->getUser()->getPersonPerson()->getIdPerson().'_tempFacturaFile.pdf';
//        file_put_contents($path, $file);
//        $context = array(
//            'emailType'=>'succesRecollect',
//            'toEmail' => $toEmail,
//            'userName' => 'Andrés Felipe',
//            'fechaRecaudo' => $date,
//            'value'=>40690.93,
//            'path'=>$path,
//            'documentName'=>'Factura '.date_format($date,'d-m-y H:i:s').'.pdf',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test failRecollect Email */
//        $context=array(
//            'emailType'=>'failRecollect',
//            'userEmail'=>'algo@alg.com',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'rejectionDate'=>new DateTime(),
//            'value' => 230750.23,
//            'phone'=>'3183941645'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test regectionCollect Email */
//        $context=array(
//            'emailType'=>'regectionCollect',
//            'userEmail'=>$this->getUser()->getEmail(),
//            'userName'=>$this->getUser()->getPersonPerson()->getFullName(),
//            'rejectionDate'=>new DateTime(),
//            'toEmail'=> $toEmail,
//            'phone'=>'3183941645',
//            'value'=>'350400'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test regectionDispersion Email */
//        $context=array(
//            'emailType'=>'regectionDispersion',
//            'userEmail'=>'algo@algo.com',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'rejectionDate'=>new DateTime(),
//            'phone'=>'3183941645',
//            'rejectedProduct'=>'Nombre del producto',
//            'idPOD'=>4,
//            'value'=>483909,23
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test succesfulDispersion Email */
//        $context=array(
//            'emailType'=>'succesDispersion',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe Ramírez',
//            'pagoPila'=>true,
//        );
//        $params = array(
//            'ref'=> 'comprobante',
//            'id' => 6,
//            'type' => 'pdf',
//            'attach' => null
//        );
//        $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
//        $file =  $documentResult->getContent();
//        if (!file_exists('uploads/temp/comprobantes')) {
//            mkdir('uploads/temp/comprobantes', 0777, true);
//        }
//        $path = 'uploads/temp/comprobantes/'.'2'.'_tempComprobanteFile.pdf';
//        file_put_contents($path, $file);
//        $context['path']=$path;
//        $context['comprobante']=true;
//        $context['documentName']='Comprobante '.date_format(new DateTime(),'d-m-y H:i:s').'.pdf';
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test failDispersion Email */
//        $context=array(
//            'emailType'=>'failDispersion',
//            'userEmail'=>'algo@algo.com',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test validatePayMethod Email */
//        $context=array(
//            'emailType'=>'validatePayMethod',
//            'payMethod'=>'Efectivo',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'starDate'=>new DateTime(),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test transactionRejected Eamil */
//        $context=array(
//            'emailType'=>'transactionRejected',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'rejectionDate'=>new DateTime(),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test liquidation Email */
//        $context=array(
//            'emailType'=>'liquidation',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'employerSociety'=>'103',
//            'documentNumber'=>'1020772509',
//            'userEmail'=>'algo@algo.com',
//            'employeeName'=>'Esteban',
//            'sqlNumber'=>'13009',
//            'phone'=>'3134338252'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test appDownload Email */
//        $context=array(
//            'emailType'=>'appDownload',
//            'toEmail'=>$toEmail,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test descubrir Email */
//        $context=array(
//            'emailType'=>'descubrir',
//            'toEmail'=>$toEmail,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test supplies Email */
//        $context=array(
//            'emailType'=>'supplies',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test waiting Email */
//        $context=array(
//            'emailType'=>'waiting',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test contractFinishReminder Email */
//        $context=array(
//            'emailType'=>'contractFinishReminder',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés',
//            'employeeName'=>'Esteban',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test cesantCharges Email */
//        $context=array(
//            'emailType'=>'cesantCharges',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés',
//            'employeeName'=>'Esteban',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test cesantPayment Email */
//        $context=array(
//            'emailType'=>'cesantPayment',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés',
//            'employeeName'=>'Esteban',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test bonus Email */
//        $context=array(
//            'emailType'=>'bonus',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés',
//            'employeeName'=>'Esteban',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test clientRecovery Email */
//        $context=array(
//            'emailType'=>'clientRecovery',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test risks Email */
//        $context=array(
//            'emailType'=>'risks',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test contractFinish Email */
//        $context=array(
//            'emailType'=>'contractFinish',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test contractAttachmentEmail Email */
//        $context=array(
//            'emailType'=>'contractAttachmentEmail',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban',
//            'docType'=>'contrato',
//            'path'=>null,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test docsValidated Email */
//        $context=array(
//            'emailType'=>'docsValidated',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test docsError Email */
//        $context=array(
//            'emailType'=>'docsError',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esteban',
//            'errors'=>array('RUT','MAND'),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test employeeDocsError Email */
//        $context=array(
//            'emailType'=>'employeeDocsError',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andres',
//            'employeeName'=>'Esteban',
//            'errors'=>array('CAS','CC'),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test employeeDocsValidated Email */
//        $context=array(
//            'emailType'=>'employeeDocsValidated',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andres',
//            'employeeName'=>'Esteban'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test notRegisteredUserApp Email */
//        $context=array(
//            'emailType'=>'notRegisteredUserApp',
//            'userEmail'=>$toEmail,
//            'name'=>'Andres',
//            'phone'=>'3134338252'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test helpTransaction Email */
//        $context=array(
//            'emailType'=>'helpTransaction',
//            'userEmail'=>$toEmail,
//            'name'=>'Andres',
//            'phone'=>'3134338252',
//            'userId'=>'10',
//            'username'=>'Andres',
//            'idPod'=>'102',
//            'idNovoPay'=>'1231',
//            'statusName'=>'algo',
//            'statusDescription'=>'descript',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//
//        /** $context must have:
//         * string name
//         * string userEmail
//         * string phone
//         * string message
//         * string subject
//         */
//
//        /** test stuckRegistration Email */
//        $context=array(
//            'emailType'=>'stuckRegistration',
//            'userEmail'=>$toEmail,
//            'name'=>'Andres',
//            'phone'=>'3134338252',
//            'message'=>'algun contenido',
//            'subject'=>'subject',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

        return $this->redirect($this->generateUrl('back_office'));
    }

    /**
     * @param string $name
     * @param string $lastName
     * @param string $documentType
     * @param string $document
     * @param string $email
     * @param string $highTech
     * @param string $sql
     * @param string $ehe
     * @param string $pay
     * @param string $phone
     * @param string $contract
     * @param string $period
     * @param string $paid
     * @param string $join
     * @param integer $index
     * @param Request $request
     * @return Response
     */
    public function userViewAction($name,$lastName,$documentType,$document,$email,$highTech,$sql,$ehe,$pay,$phone,$contract,$period,$paid,$join,$index,Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
//        $contract ='';
//        $activePayrrol ='';
        if($name == "#")$name = '';
        if($lastName =='#')$lastName = '';
        if($document =='#')$document = '';
        if($email =='#')$email = '';
        if($highTech =='#')$highTech = '';
        if($sql =='#')$sql = '';
        if($ehe =='#')$ehe = '';
        if($pay =='#')$pay = '';
        if($phone =='#')$phone = '';
        if($documentType=='#')$documentType = '';
        if($contract=='#')$contract = '';
        if($join=='#')$join = '';
        if($period=='#')$period = '';
        if($paid=='#')$paid = '';
        $form = $this->get('form.factory')->createNamedBuilder('formFilter')
            ->add('name','text',array('label'=>'Nombres:','required'=>false))
            ->add('lastName','text',array('label'=>'Apellidos:','required'=>false))
            ->add('documentType','choice', array('label'=>'Tipo Documento:','expanded'=>false,'multiple'=>false,'placeholder' => 'tipo','required'=>false,
                'choices' => array(
                    'CC'=> 'Cédula',
                    'CE' => 'Cédula de Extranjeria',
                    'PASAPORTE' => 'Pasaporte',
                )))
            ->add('document','text',array('label'=>'No. Documento:','required'=>false))
            ->add('email','text',array('label'=>'Email:','required'=>false))
            ->add('phone','text',array('label'=>'Telefono:','required'=>false))
            ->add('hightech','text',array('label'=>'Id HighTech:','required'=>false))
            ->add('sql','text',array('label'=>'Id SQL:','required'=>false))
            ->add('ehe','text',array('label'=>'Id EHE:','required'=>false))
            ->add('contract','text',array('label'=>'Id Contrato:','required'=>false))
            ->add('payMethod','choice', array('label'=>'Metodo Pago:','expanded'=>false,'multiple'=>false,'placeholder' => 'metodo','required'=>false,
                'choices' => array(
                    'EFE' => 'Efectivo',
                    'DAV' => 'Daviplata',
                    'TRA'=> 'Transferencia',
                )))
            ->add('period','choice', array('label'=>'Periodo Payroll:','expanded'=>false,'multiple'=>false,'placeholder' => 'periodo','required'=>false,
                'choices' => array(
                    '2' => 'Primera Quincena',
                    '4' => 'Segunda Quincena',
                )))
            ->add('paid','choice', array('label'=>'Payroll Pago:','expanded'=>false,'multiple'=>false,'placeholder' => 'pagado','required'=>false,
                'choices' => array(
                    '1' => 'Si',
                    '-1' => 'No',
                )))
            ->add('join','choice', array('label'=>'Forzar join:','expanded'=>false,'multiple'=>false,'placeholder' => 'forzar','required'=>false,
                'choices' => array(
                    'YES' => 'Si',
                )))
            ->add('search','submit',array('label' => 'Buscar'))->getForm();
        if($name!='')
            $form->get('name')->setData($name);
        if($lastName!='')
            $form->get('lastName')->setData($lastName);
        if($documentType!='')
            $form->get('documentType')->setData($documentType);
        if($document!='')
            $form->get('document')->setData($document);
        if($email!='')
            $form->get('email')->setData($email);
        if($highTech!='')
            $form->get('hightech')->setData($highTech);
        if($sql!='')
            $form->get('sql')->setData($sql);
        if($ehe!='')
            $form->get('ehe')->setData($ehe);
        if($pay!='')
            $form->get('payMethod')->setData($pay);
        if($phone!='')
            $form->get('phone')->setData($phone);
        if($contract!='')
            $form->get('contract')->setData($contract);
        if($period!='')
            $form->get('period')->setData($period);
        if($paid!='')
            $form->get('paid')->setData($paid);
        if($join!='')
            $form->get('join')->setData($join);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $name = $form->get('name')->getData();
            $index = 1;
            $lastName = $form->get('lastName')->getData();
            $document = $form->get('document')->getData();
            $documentType = $form->get('documentType')->getData();
            $email = $form->get('email')->getData();
            $highTech = $form->get('hightech')->getData();
            $sql = intval($form->get('sql')->getData());
            $ehe = intval($form->get('ehe')->getData());
            $contract = intval($form->get('contract')->getData());
            $pay = $form->get('payMethod')->getData();
            $phone = $form->get('phone')->getData();
            $join = $form->get('join')->getData();
            $period = $form->get('period')->getData();
            $paid = $form->get('paid')->getData();
        }

        $em = $this->getDoctrine()->getManager();
        if($name=='' and $lastName=='' and $document=='' and $documentType=='' and $email=='' and $highTech=='' and $sql=='' and $ehe == null and $pay=='' and $phone==''
            and $contract=='' and $join=='' and $period=='' and $paid==''){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:userView.html.twig',array(
                'form'=>$form->createView(),
                'users'=>$em->getRepository("RocketSellerTwoPickBundle:User")->find(0)
            ));
        }else{
            /** @var QueryBuilder $query */
            $query = $em->createQueryBuilder();
            $query->add('select', 'u');
            if($join!=''){
                $query->from("RocketSellerTwoPickBundle:User",'u')
                    ->join("u.personPerson",'pe')
                    ->join("u.realProcedures",'pr')
                    ->join('pe.employer','em')
                    ->join('pe.phones','ph')
                    ->join("em.employerHasEmployees",'ehe')
                    ->join("ehe.employeeEmployee",'ee')
                    ->join("ehe.contracts",'c')
                    ->join("c.activePayroll",'ap')
                    ->join("c.payMethodPayMethod",'pm')
                    ->join("pm.payTypePayType",'pt')
                    ->join("ee.personPerson",'ep');
            }else{
                $query->from("RocketSellerTwoPickBundle:User",'u')
                    ->leftJoin("u.personPerson",'pe')
                    ->leftJoin("u.realProcedures",'pr')
                    ->leftJoin('pe.employer','em')
                    ->leftJoin('pe.phones','ph')
                    ->leftJoin("em.employerHasEmployees",'ehe')
                    ->leftJoin("ehe.employeeEmployee",'ee')
                    ->leftJoin("ehe.contracts",'c')
                    ->leftJoin("c.activePayroll",'ap')
                    ->leftJoin("c.payMethodPayMethod",'pm')
                    ->leftJoin("pm.payTypePayType",'pt')
                    ->leftJoin("ee.personPerson",'ep');
            }
            if($name!= ''){
                $strex = explode(' ',$name);
                foreach ($strex as $str) {
                    $sbstrs = $this->get("app.symplifica_utils")->getAllStrings($str);
                    foreach ($sbstrs as $sbstr) {
                        $query->andWhere($query->expr()->orX(
                            $query->expr()->like("pe.names","?1"),
                            $query->expr()->like("ep.names","?1")
                        ))
                            ->setParameter('1',"%".$sbstr."%");
                    }
                }
            }
            if($lastName!=''){
                $strex = explode(' ',$lastName);
                foreach ($strex as $str) {
                    $sbstrs = $this->get("app.symplifica_utils")->getAllStrings($str);
                    foreach ($sbstrs as $sbstr) {
                        $query->andWhere($query->expr()->orX(
                            $query->expr()->like("pe.lastName1","?2"),
                            $query->expr()->like("ep.lastName1","?2"),
                            $query->expr()->like("pe.lastName2","?2"),
                            $query->expr()->like("ep.lastName2","?2")
                        ))
                            ->setParameter('2',"%".$sbstr."%");
                    }
                }
            }
            if($document!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.document","?3"),
                    $query->expr()->like("ep.document","?3"),
                    $query->expr()->like("pe.document","?3"),
                    $query->expr()->like("ep.document","?3")
                ))
                    ->setParameter('3',"%".$document."%");
            }
            if($highTech!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("em.idHighTech","?4")
                ))
                    ->setParameter('4',"%".$highTech."%");
            }
            if($sql!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("em.idSqlSociety","?5")
                ))
                    ->setParameter('5',"%".$sql."%");
            }
            if($ehe!=null){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("ehe.idEmployerHasEmployee","?6")
                ))
                    ->setParameter('6',"%".$ehe."%");
            }
            if($pay!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pt.simpleName","?7")
                ))
                    ->andWhere($query->expr()->eq("c.state",1))
                    ->setParameter('7',"%".$pay."%");
            }
            if($email!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("u.email","?8")
                ))
                    ->setParameter('8',"%".$email."%");
            }
            if($phone!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("ph.phoneNumber","?9")
                ))
                    ->setParameter('9',"%".$phone."%");
            }
            if($documentType!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.documentType","?10"),
                    $query->expr()->like("ep.documentType","?10")
                ))
                    ->setParameter('10',"%".$documentType."%");
            }
            if($contract!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->eq("c.idContract","?11")
                ))
                    ->setParameter('11',$contract);
            }
            if($period!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->eq("ap.period","?12")
                ))
                    ->setParameter('12',$period);
            }
            if($paid!='') {
                if($paid==1){
                    $query->andWhere($query->expr()->orX(
                        $query->expr()->eq("ap.paid", "?13")
                    ))
                        ->setParameter('13', $paid);
                }else{
                    $query->andWhere($query->expr()->orX(
                        $query->expr()->eq("ap.paid", "?13")
                    ))
                        ->setParameter('13', 0);
                }

            }
            $query->addOrderBy('u.id','ASC');
            $maxIndex = 1;
            $results = count($query->getQuery()->getResult());
            if($results%20!=0){
                $maxIndex = intval($results/20)+1;
            }else{
                $maxIndex = intval($results/20);
            }
            if($index==1){
                $query->setFirstResult(0);
                $query->setMaxResults(20);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $users = $paginator->getIterator();
            }else{
                $query->setFirstResult(($index-1)*20);
                $query->setMaxResults(20);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $users = $paginator->getIterator();
            }
            if($results > 20){
                $this->addFlash('success',"La query obtuvo ".$results." resultados.");
            }
            return $this->render('RocketSellerTwoPickBundle:BackOffice:userView.html.twig',array(
                'form'=>$form->createView(),
                'users'=>$users,
                'index'=>intval($index),
                'name'=>$name,
                'lastName'=>$lastName,
                'document'=>$document,
                'documentType'=>$documentType,
                'email'=>$email,
                'highTech'=>$highTech,
                'sql'=>$sql,
                'ehe'=>$ehe,
                'pay'=>$pay,
                'phone'=>$phone,
                'contract'=>$contract,
                'period'=>$period,
                'paid'=>$paid,
                'join'=>$join,
                'maxIndex'=>intval($maxIndex),
                ));
        }
    }

	public function userBackOfficeStateAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$em = $this->getDoctrine()->getManager();
		$procedureRepo = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findBy(array('procedureTypeProcedureType'=>$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ProcedureType')->findOneBy(array('code'=>'REE'))));

		return $this->render('RocketSellerTwoPickBundle:BackOffice:backOfficeStatus.html.twig',array('procedures'=>$procedureRepo));
	}

	public function addToSQLPendingVacationsAction($idEmployerHasEmployee,$pendingDays){

		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$request = $this->container->get('request');
		$request->setMethod("POST");
		$request->request->add(array(
			"employee_id" => $idEmployerHasEmployee,
			"pending_days" => $pendingDays,
		));

		$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddPendingVacationDays', array('_format' => 'json'));

		return $this->redirectToRoute('back_office');
	}

	public function eheEntitiesViewAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$criteria = new \Doctrine\Common\Collections\Criteria();
		$criteria->where($criteria->expr()->gt('state', 3));

		$em = $this->getDoctrine()->getManager();
		$eheRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
		$filteredEheRepo = $eheRepo->matching($criteria);

		return $this->render('RocketSellerTwoPickBundle:BackOffice:entitiesView.html.twig', array('ehes' => $filteredEheRepo));

	}

	public function notPaidViewAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$product = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName"=>"PN"));
		$product2 = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName"=>"PP"));
		$product3 = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName"=>"PRM"));

		$podNomina = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product->getIdProduct()));
		$podPila = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product2->getIdProduct()));
		$podPrima = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product3->getIdProduct()));

		//Now Pod has all the products nomina on the database.
		return $this->render('RocketSellerTwoPickBundle:BackOffice:payState.html.twig', array('podsN' => $podNomina, 'podsP' => $podPila, 'podsPr' => $podPrima));
	}

	public function payTypeInfoViewAction()
	{
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$criteria = new \Doctrine\Common\Collections\Criteria();
		$criteria->where($criteria->expr()->gt('state', 3));

		$em = $this->getDoctrine()->getManager();
		$eheRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
		$filteredEheRepo = $eheRepo->matching($criteria);

		$userRepo = $em->getRepository('RocketSellerTwoPickBundle:User');
		$personRepo = $em->getRepository('RocketSellerTwoPickBundle:Person');

		$userArray = array();

		/** @var EmployerHasEmployee $ehe */
		foreach ($filteredEheRepo as $ehe) {
			$personId = $ehe->getEmployerEmployer()->getPersonPerson()->getIdPerson();
			$personFound = $personRepo->find($personId);
			/** @var User $userFound */
			$userFound = $userRepo->findOneBy(array('personPerson' => $personFound));
			array_push($userArray, $userFound->getEmail());
		}

		return $this->render('RocketSellerTwoPickBundle:BackOffice:payTypeInfoView.html.twig', array('ehes' => $filteredEheRepo, 'usersEmail' => $userArray));
	}

	public function checkPilaOperatorStateAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$users = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->findAll();

		$employers = array();
		foreach ($users as $user){
			//If the user is already on the stage where it should be added to pila Operator
			if($user->getStatus() == 2){
				array_push($employers, $user->getPersonPerson()->getEmployer());
			}
		}

		return $this->render('RocketSellerTwoPickBundle:BackOffice:pilaOperatorState.html.twig', array('employers' => $employers));
	}

	public function updateStateRegistrationPilaOperatorAction($idEmployer){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$em = $this->getDoctrine()->getManager();

		$employerRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
		/** @var Employer $employer */
		$employer = $employerRepo->find($idEmployer);

		$request = $this->container->get('request');
		$request->setMethod("POST");
		$request->request->add(array(
			"radicateNumber" => $employer->getRadicatedNumberPila(),
		));

		$answer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postCheckStateRegisterEmployerPilaOperator', array('request'=>$request), array('_format' => 'json'));

		return $this->redirectToRoute('back_pila_operator_state_view');
	}

	public function exportPilaOperatorAfiliationErrorAction($idTransaction){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		/** @var Transaction $transaction */
		$transaction = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Transaction")->find($idTransaction);

		$utils = $this->get('app.symplifica_utils');
		$filePath = $utils->getDocumentPath($transaction->getTransactionState()->getDocument());

		header("Content-disposition: attachment; filename=$filePath");
    header('Content-type: application/zip');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($filePath);
    ignore_user_abort(true);

	}

	public function fixPODPilaAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$em=$this->getDoctrine()->getManager();

		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(array("period" => 4, "month" => "01", "year" => 2017, "paid" => 1));
		$pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array("idNovoPay" => "P1"));
		$product = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array("simpleName" => "PP"));

		foreach($payrolls as $payroll){
			$pilaPOD = $payroll->getPila();

			if($pilaPOD!=null&&$pilaPOD->getProductProduct() == NULL){

				$pilaPOD->setPurchaseOrdersStatus($pos);
				$pilaPOD->setProductProduct($product);
				$pilaPOD->setDescription("Pago de Aportes a Seguridad Social mes Enero");

				$poList = $payroll->getPurchaseOrdersDescription();

				/** @var PurchaseOrdersDescription $singlePod */
				foreach ($poList as $singlePod){
					$pilaPOD->setPurchaseOrders($singlePod->getPurchaseOrders());
					break;
				}

				$totalValue = 0;
				$payrollsPila = $pilaPOD->getPayrollsPila();

				/** @var Payroll $singlePayroll */
				foreach ($payrollsPila as $singlePayroll){
					$pilaDetails = $singlePayroll->getPilaDetails();

					/** @var PilaDetail $singleDetail */
					foreach ($pilaDetails as $singleDetail){
						$totalValue = $totalValue + $singleDetail->getSqlValueCia() + $singleDetail->getSqlValueEmp();
					}
				}

				$pilaPOD->setValue($totalValue);
				$em->persist($pilaPOD);
				$em->flush();
			}
		}

		return $this->redirectToRoute('back_office');
	}

	public function addEmployerToEnlaceOperativoBackAction($idEmployer){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$em=$this->getDoctrine()->getManager();

		$employer = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer')->find($idEmployer);

		if($employer->getExistentPila() == NULL && $employer->getIdHighTech() != NULL){

			$request = $this->container->get('request');
			$request->setMethod("POST");
			$request->request->add(array(
				"GSCAccount" => $employer->getIdHighTech()
			));

			$transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'IPil'));

			$transaction = new Transaction();
			$transaction->setTransactionType($transactionType);

			$pilaRegistrationAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterEmployerToPilaOperator', array('_format' => 'json'));

			if($pilaRegistrationAnswer->getStatusCode() == 200){
				//Received succesfully
				$radicatedNumber = json_decode($pilaRegistrationAnswer->getContent(), true)["numeroRadicado"];
				$transaction->setRadicatedNumber($radicatedNumber);
				$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-InsEnv'));
			}
			else{
				//If some kind of error
				$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-ErrSer'));
			}

			$transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
			$em->persist($transaction);
			$em->flush();
			$employer->setExistentPila($transaction->getIdTransaction());
			$employer->addTransaction($transaction);

			$em->persist($employer);
			$em->flush();

		}

		return $this->redirectToRoute('back_office');
	}

	public function sendPlanillaFileToEnlaceOperativoBackAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$request = $this->container->get('request');
		$conta = 0;

		$em=$this->getDoctrine()->getManager();

		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(array("period" => 4, "month" => "01", "year" => 2017, "paid" => 1));

		foreach($payrolls as $payroll){
			$podPila = $payroll->getPila();

			if($podPila->getUploadedFile() == NULL){

				$payrollsPila = $podPila->getPayrollsPila();
				$haveNovelties = false;

				$payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");

				/** @var Payroll $payrollPila */
				foreach ( $payrollsPila as $payrollPila ){
					if( count($payrollPila->getNovelties()) > 0){
						$haveNovelties = true;
						break;
					}
					//If is Quincenal we need to check the first payroll of the month to see If we have novelties
					if($payrollPila->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q"){
						$singlePayroll = $payrollRepo->findOneBy(array('contractContract' => $payrollPila->getContractContract() , 'period' => 2 , 'year' => $payrollPila->getYear() , 'month' => $payrollPila->getMonth()) );
						if($singlePayroll != NULL){
							if( count($singlePayroll->getNovelties()) > 0){
								$haveNovelties = true;
								break;
							}
						}
					}
				}

				$transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'CPla'));

				$transaction = new Transaction();
				$transaction->setTransactionType($transactionType);

				if($haveNovelties == false) {
					$request->setMethod("GET");
					$insertionAnswerTextFile = $this->forward('RocketSellerTwoPickBundle:PilaPlainTextRest:getMonthlyPlainText', array('podId' => $podPila->getIdPurchaseOrdersDescription(), 'download' => 'generate'), array('_format' => 'json'));

					$request->setMethod("POST");
					$request->request->add(array(
						"GSCAccount" => $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdHighTech(),
						"FileToUpload" => json_decode($insertionAnswerTextFile->getContent(), true)['fileToSend']
					));
					$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postUploadFileToPilaOperator', array('request' => $request) ,  array('_format' => 'json'));
					if ($insertionAnswer->getStatusCode() == 200) {
						//Received succesfully
						$radicatedNumber = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
						$transaction->setRadicatedNumber($radicatedNumber);
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaEnv'));
					} else {
						//If some kind of error
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrSer'));
					}
				}
				else {
					$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrNov'));
				}

				$em = $this->getDoctrine()->getManager();
				$transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
				$em->persist($transaction);
				$em->flush();
				$podPila->setUploadedFile($transaction->getIdTransaction());
				$podPila->addTransaction($transaction);
				$em->persist($podPila);
				$em->flush();

				$conta = $conta + 1;

			}

			if($conta == 10){
				return $this->redirectToRoute('back_office');
			}
		}

		return $this->redirectToRoute('back_office');
	}

	public function highTechCheckAction(){

		$empRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->findAll();

		/** @var User $sE */
		foreach ($empRepo as $sE){

			try{
				$answ = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array("idUser" => $sE->getId()));
			}catch(Exception $e){
				continue;
			}

			if($answ->getStatusCode() != 404){
				$cA = json_decode($answ->getContent(), true );

				var_dump($sE->getPersonPerson()->getFullName());
				var_dump($cA);
				var_dump("-------------");
			}
		}
	}

    public function fixNotificationPayNotificationIdAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $notifications = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification")->findAll();
        $em = $this->getDoctrine()->getManager();

        foreach ($notifications as $notification) {
            $relatedLink = $notification->getRelatedLink();
            if(strpos($relatedLink, "/payroll") == 0 &&
                ($notification->getAccion() == "pagar" || $notification->getAccion() == "Pagar"))
            {
                $notification->setRelatedLink("/payroll/" . $notification->getId());
                $em->persist($notification);
                $em->flush();
            }
        }
        return $this->redirectToRoute('back_office');
    }

    public function setReferidosCodeActionAlreadyInPromoCodeAction() {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $usersRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $promoCodeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCode");
        $em = $this->getDoctrine()->getManager();
        $file = fopen('public/docs/referidos.csv', 'r');
        $codes = array();
        while (($line = fgetcsv($file)) !== FALSE) {
            $codes[$line[0]] = $line[1];
            /** @var User $user */
            $user = $usersRepo->findOneBy(array('username' => $line[0]));
            if($user) {
                $user->setCode($line[1]);
                $em->persist($user);
                /** @var PromotionCode $promoCode */
                $promoCode = $promoCodeRepo->findOneBy(array('code' => $line[1]));
                $promoCode->setUserUser($user);
            }
        }
        $em->flush();
        fclose($file);
        return $this->redirectToRoute('back_office');
    }

    public function setReferidosCodeAction() {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $file = fopen('public/docs/referidos.csv', 'r');
        $codes = array();
        $mapaIndicesCode = array();
        /* @var $utils UtilsController */
        $utils = $this->get('app.symplifica_utils');
        while (($line = fgetcsv($file)) !== FALSE) {
            $codes[$line[0]] = $line[1];
            $mapaIndicesCode[$utils->mb_normalize($line[1])] = 1;
        }
        fclose($file);

        $usersRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $promoTypeRef = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCodeType")->find(6);
        $em = $this->getDoctrine()->getManager();

        $users = $usersRepo->findAll();

        $cont = 0;
        /** @var User $user */
        foreach ($users as $user) {
            if(array_key_exists($user->getUsername(), $codes)) {
                continue;
            } else {
                $names = explode(' ', $user->getPersonPerson()->getNames());
                $userFirstName = $utils->normalizeAccentedChars($names[0]);
                $lastNames = explode(' ', $user->getPersonPerson()->getLastName1());
                $userLastName = $utils->normalizeAccentedChars($lastNames[0]);
                $userFirstName = $utils->mb_normalize($userFirstName);
                $userLastName = $utils->mb_capitalize($utils->mb_normalize($userLastName));

                $refCode = $userFirstName . $userLastName;
                if($refCode == '') continue;

                if(array_key_exists($utils->mb_normalize($refCode),$mapaIndicesCode)) {
                    $num = $mapaIndicesCode[$utils->mb_normalize($refCode)]++;
                    $refCode .= $num;
                } else {
                    $mapaIndicesCode[$utils->mb_normalize($refCode)] = 1;
                }
                $user->setCode($refCode);
                $em->persist($user);

                /** @var PromotionCode $promoCode */
                $promoCode = new PromotionCode();
                $promoCode->setCode($refCode);
                $promoCode->setPromotionCodeTypePromotionCodeType($promoTypeRef);
                $promoCode->setUserUser($user);
                $em->persist($promoCode);
                if($cont++ % 100 == 0)
                    $em->flush();
            }
        }
        $em->flush();
        return $this->redirectToRoute('back_office');
    }

    public function personalInfoViewAction()
		{
			$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

			$criteria = new \Doctrine\Common\Collections\Criteria();
			$criteria->where($criteria->expr()->gt('state', 2));

			$em = $this->getDoctrine()->getManager();
			$eheRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
			$filteredEheRepo = $eheRepo->matching($criteria);

			$userRepo = $em->getRepository('RocketSellerTwoPickBundle:User');
			$personRepo = $em->getRepository('RocketSellerTwoPickBundle:Person');

			$userArray = array();
			$phoneArray = array();
            $codeArray = array();

			/** @var EmployerHasEmployee $ehe */
			foreach ($filteredEheRepo as $ehe) {
				$personId = $ehe->getEmployerEmployer()->getPersonPerson()->getIdPerson();
				$personFound = $personRepo->find($personId);

				/** @var User $userFound */
				$userFound = $userRepo->findOneBy(array('personPerson' => $personFound));
				array_push($userArray, $userFound->getEmail());

                array_push($codeArray, $userFound->getCode());

				/** @var Phone $personP */
				$personP = $personFound->getPhones()->first();
				array_push($phoneArray, $personP ? $personP->getPhoneNumber(): "");

			}

			return $this->render('RocketSellerTwoPickBundle:BackOffice:personalInfoView.html.twig', array('ehes' => $filteredEheRepo, 'usersEmail' => $userArray, 'usersPhone' => $phoneArray, 'usersCode' => $codeArray));
		}
	
	public function uploadFileUsingPilaBotAction($idPod){
		
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
		
		$request = new Request();
			
		$em = $this->getDoctrine()->getManager();
		$podRepo = $em->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription');
		$singlePod = $podRepo->find($idPod);
		
		$payrolls = $singlePod->getPayrollsPila();
		
		foreach($payrolls as $payroll){
			$podPila = $payroll->getPila();
		
			$payrollsPila = $podPila->getPayrollsPila();
			$haveNovelties = false;
			
			$payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
			
			/** @var Payroll $payrollPila */
			foreach ( $payrollsPila as $payrollPila ){
				if( count($payrollPila->getNovelties()) > 0){
					$haveNovelties = true;
					break;
				}
				//If is Quincenal we need to check the first payroll of the month to see If we have novelties
				if($payrollPila->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q"){
					$singlePayroll = $payrollRepo->findOneBy(array('contractContract' => $payrollPila->getContractContract() , 'period' => 2 , 'year' => $payrollPila->getYear() , 'month' => $payrollPila->getMonth()) );
					if($singlePayroll != NULL){
						if( count($singlePayroll->getNovelties()) > 0){
							$haveNovelties = true;
							break;
						}
					}
				}
			}
			
			$transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'CPla'));
			
			$transaction = new Transaction();
			$transaction->setTransactionType($transactionType);
			
			if($haveNovelties == false) {
				$request->setMethod("GET");
				$insertionAnswerTextFile = $this->forward('RocketSellerTwoPickBundle:PilaPlainTextRest:getMonthlyPlainText', array('podId' => $podPila->getIdPurchaseOrdersDescription(), 'download' => 'generate'), array('_format' => 'json'));
				
				$request->setMethod("POST");
				$request->request->add(array(
					"GSCAccount" => $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdHighTech(),
					"FileToUpload" => json_decode($insertionAnswerTextFile->getContent(), true)['fileToSend']
				));
				$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postUploadFileToPilaOperator', array('request' => $request) , array('_format' => 'json'));
				if ($insertionAnswer->getStatusCode() == 200) {
					//Received succesfully
					$radicatedNumber = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
					$transaction->setRadicatedNumber($radicatedNumber);
					$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaEnv'));
				} else {
					//If some kind of error
					$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrSer'));
				}
			}
			else {
				$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrNov'));
			}
			
			$em = $this->getDoctrine()->getManager();
			$transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
			$em->persist($transaction);
			$em->flush();
			$podPila->setUploadedFile($transaction->getIdTransaction());
			$podPila->addTransaction($transaction);
			$em->persist($podPila);
			$em->flush();
			
		}
		
		return $this->redirectToRoute('show_pilas');
	}
	
	public function downloadPlanillaLogAction($podId)
	{
		
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
		
		$em = $this->getDoctrine()->getManager();
		$podRepo = $em->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription');
		$singlePod = $podRepo->find($podId);
		
		$tranId = $singlePod->getUploadedFile();
		
		$tranRepo = $em->getRepository('RocketSellerTwoPickBundle:Transaction');
		$singleTran = $tranRepo->find($tranId);
		
		$document = $singleTran->getTransactionState()->getDocument();
		$media = $document->getMediaMedia();
		
		$utils = $this->get('app.symplifica_utils');
		
		if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
			$docUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
		}
		
		$docName = $document->getDocumentTypeDocumentType()->getName().'.'.$media->getExtension();
		$zip = new ZipArchive();
		$localFile = "Log visual HT ID $podId.zip";
		
		if ($zip->open($localFile,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
			# loop through each file
			$zip->addFile($docUrl,$docName);
			# close zip
			if($zip->close()!==TRUE)
				echo "no permisos";
			# send the file to the browser as a download
			header("Content-disposition: attachment; filename=$localFile");
			header('Content-type: application/zip');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			ob_clean();
			flush();
			readfile($localFile);
			ignore_user_abort(true);
			unlink($localFile);
		}
		
		return $this->redirectToRoute('ajax', array(), 301);
		
	}
	
	public function fixBrokenPilaBotAction(){
		
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
		
		$em = $this->getDoctrine()->getManager();
		
		$podRepo = $em->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription');
		$brokenPods = $podRepo->findBy(array('enlaceOperativoFileName' => "", 'uploadedFile' => -1));
		
		$request = new Request();
		
		foreach ($brokenPods as $singlePod){
			$payrolls = $singlePod->getPayrollsPila();
			
			foreach($payrolls as $index => $payroll) {
				/** @var PurchaseOrdersDescription $podPila */
				$podPila = $payroll->getPila();
				
				$payrollsPila = $podPila->getPayrollsPila();
				$haveNovelties = false;
				
				$payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
				
				/** @var Payroll $payrollPila */
				foreach ($payrollsPila as $payrollPila) {
					if (count($payrollPila->getNovelties()) > 0) {
						$haveNovelties = true;
						break;
					}
					//If is Quincenal we need to check the first payroll of the month to see If we have novelties
					if ($payrollPila->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q") {
						$singlePayroll = $payrollRepo->findOneBy(array('contractContract' => $payrollPila->getContractContract(), 'period' => 2, 'year' => $payrollPila->getYear(), 'month' => $payrollPila->getMonth()));
						if ($singlePayroll != NULL) {
							if (count($singlePayroll->getNovelties()) > 0) {
								$haveNovelties = true;
								break;
							}
						}
					}
				}
				
				$transactionType = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:TransactionType')->findOneBy(array('code' => 'CPla'));
				
				$transaction = new Transaction();
				$transaction->setTransactionType($transactionType);
				
				if ($haveNovelties == false) {
					$request->setMethod("GET");
					$insertionAnswerTextFile = $this->forward('RocketSellerTwoPickBundle:PilaPlainTextRest:getMonthlyPlainText', array('podId' => $podPila->getIdPurchaseOrdersDescription(), 'download' => 'generate'), array('_format' => 'json'));
					
					$request->setMethod("POST");
					$request->request->add(array(
						"GSCAccount" => $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdHighTech(),
						"FileToUpload" => json_decode($insertionAnswerTextFile->getContent(), true)['fileToSend']
					));
					$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postUploadFileToPilaOperator', array('request' => $request), array('_format' => 'json'));
					if ($insertionAnswer->getStatusCode() == 200) {
						//Received succesfully
						$radicatedNumber = json_decode($insertionAnswer->getContent(), true)["numeroRadicado"];
						$transaction->setRadicatedNumber($radicatedNumber);
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaEnv'));
					} else {
						//If some kind of error
						$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrSer'));
					}
				} else {
					$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-ErrNov'));
				}
				
				$em = $this->getDoctrine()->getManager();
				$transaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
				$em->persist($transaction);
				$em->flush();
				$podPila->setUploadedFile($transaction->getIdTransaction());
				$podPila->addTransaction($transaction);
				$podPila->setEnlaceOperativoFileName("NULL");
				$em->persist($podPila);
				$em->flush();
				
				if($index == 10){
					return $this->redirectToRoute('show_pilas');
				}
			}
		}
		
		return $this->redirectToRoute('show_pilas');
	}

    /**
     * @return Response
     */
    public function showPostRegisterEmployeesAction(){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
	    $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $query */
        $query = $em->createQueryBuilder();
        $query->add('select', 'ehe');
        $query->from("RocketSellerTwoPickBundle:EmployerHasEmployee",'ehe')
            ->where($query->expr()->eq('ehe.isPostRegister',true));
        $ehes = $query->getQuery()->getResult();
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showPostRegister.html.twig',array(
            'ehes'=>$ehes,
            'type'=>$em->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=>'REE'))));
    }

    public function setSupplyNotificationsAction($month, $year) {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $eHERepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");

        $comprobanteDotType = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentType")
                                ->findOneBy(array('docCode' => 'CPRDOT'));
        $eHEs = $eHERepo->findAll();
        /** @var EmployerHasEmployee $eHE */
        foreach ($eHEs as $eHE) {
            if($eHE->getState() < 4) continue;

            $activeContract = $eHE->getActiveContract();
            $supply = new Supply();
            $supply->setMonth($month);
            $supply->setYear($year);
            $supply->setContractContract($activeContract);
            $em->persist($supply);
            $em->flush();

            $personEmployer = $eHE->getEmployerEmployer()->getPersonPerson();
            $personEmployee = $eHE->getEmployeeEmployee()->getPersonPerson();
            $notification = new Notification();
            $notification->setPersonPerson($personEmployer);
            $notification->setDocumentTypeDocumentType($comprobanteDotType);
            $notification->setType('alert');
            $notification->setStatus(1);
            $uploadurl = $this->generateUrl('documentos_employee', array('entityType'=>"Supply",'entityId'=>$supply->getIdSupply(),'docCode'=>"CPRDOT"));
            $notification->setRelatedLink($uploadurl);
            $routeDownload = $this->generateUrl("download_documents", array('ref'=>"comprobante-dotacion",'id'=>$supply->getIdSupply() ,'type'=>"pdf"));
            $notification->setDownloadLink($routeDownload);
            $notification->setAccion('Subir');
            $notification->setDownloadAction('Bajar');
            $notification->setDescription('Subir copia comprobante de dotación de ' . $personEmployee->getNames() .
                                            ' ' . $personEmployee->getLastName1());

            $em->persist($notification);
            $em->flush();
        }

        return $this->redirectToRoute('back_office');
    }
	
	public function primaViewAction(){
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
		
		$em = $this->getDoctrine()->getManager();
		
		$criteria = new \Doctrine\Common\Collections\Criteria();
		$criteria->where($criteria->expr()->gt('state', 3));
		
		$eheRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
		$activeEhe = $eheRepo->matching($criteria);
		
		$payrollArr = array();
		$diasArr = array();
        $menosDiasArr = array();
		$otrosSalArr = array();
		$totalPagoArr = array();
		
		/** @var EmployerHasEmployee $ehe */
		foreach ($activeEhe as $index => $ehe) {
			
			$comparativePayroll = new Payroll();
			$comparativePayroll->setYear("2020");
			$comparativePayroll->setMonth("12");
			$comparativePayroll->setPeriod("4");
			
			$totalDias = 0;
            $menosDías = 0;
			$totalOtrosSalariales = 0;
			$totalPago = 0;
			
			/** @var Payroll $payroll */
			foreach ($ehe->getActiveContract()->getPayrolls() as $payroll) {
				
				//Finds the oldest payroll (since we fixed the DB they are not stored in order)
				if( (int)$payroll->getYear() < (int)$comparativePayroll->getYear() ){
					$comparativePayroll->setYear($payroll->getYear());
					$comparativePayroll->setMonth($payroll->getMonth());
					$comparativePayroll->setPeriod($payroll->getPeriod());
				}
				elseif( (int)$payroll->getYear() == (int)$comparativePayroll->getYear() ){
					if( (int)$payroll->getMonth() < (int)$comparativePayroll->getMonth() ){
						$comparativePayroll->setYear($payroll->getYear());
						$comparativePayroll->setMonth($payroll->getMonth());
						$comparativePayroll->setPeriod($payroll->getPeriod());
					}
					elseif ((int)$payroll->getMonth() == (int)$comparativePayroll->getMonth()){
						if( (int)$payroll->getPeriod() < (int)$comparativePayroll->getPeriod() ){
							$comparativePayroll->setYear($payroll->getYear());
							$comparativePayroll->setMonth($payroll->getMonth());
							$comparativePayroll->setPeriod($payroll->getPeriod());
						}
					}
				}
				
				//There are novelties missing, but so far the clients have used the ones in the ifs
				//If the payroll belongs to the 2nd half of the 2016 we need to get the values
				if((int)$payroll->getYear() == 2016 && (int)$payroll->getMonth() >= 7 && (int)$payroll->getMonth() <= 11 ){
					if( (int)$payroll->getYear() == 2016 && (int)$payroll->getMonth() == 11 ){
						$multiplier = 2;
					}
					else{
						$multiplier = 1;
					}
					
					/** @var Novelty $novelty */
					foreach ($payroll->getSqlNovelties() as $novelty){
						//Sueldo
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "1"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalPago = $totalPago + (int)$novelty->getSqlValue() * $multiplier;
						}
						
						//Bonificacion
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "285"){
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Vacaciones
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "145"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Subsidio de transporte
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "120"){
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() * $multiplier;
						}
						
						//Hora extra festiva diurna
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "65"){
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Incapacidad laboral
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "28"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Incapacidad general
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "15"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Licencia remunerada
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "23"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Licencia No remunerada
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "3120"){
							$totalDias = $totalDias - (int)$novelty->getUnits() ;
							$totalOtrosSalariales = $totalOtrosSalariales - (int)$novelty->getSqlValue();
                            $menosDías +=   (int)$novelty->getUnits();
						}
						
						//Licencia maternidad
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "25"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
						
						//Gasto de incapacidad
						if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode() == "20"){
							$totalDias = $totalDias + (int)$novelty->getUnits() * $multiplier;
							$totalOtrosSalariales = $totalOtrosSalariales + (int)$novelty->getSqlValue() ;
						}
					}
				}
			}
			
			$totalPago = $totalPago + $totalOtrosSalariales;
			
			array_push($payrollArr,$comparativePayroll);
			array_push($diasArr, $totalDias);
			array_push($menosDiasArr, $menosDías);
			array_push($otrosSalArr, $totalOtrosSalariales);
			array_push($totalPagoArr,$totalPago);
			
		}
		
		return $this->render('RocketSellerTwoPickBundle:BackOffice:primaView.html.twig',
			array('ehes' => $activeEhe, 'payrolls' => $payrollArr, 'days' => $diasArr, 'minusDays' => $menosDiasArr, 'otrosSalariales' => $otrosSalArr, 'totalPago' => $totalPagoArr));
	}


    public function emailGroupViewAction(Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();

        $formEmailGroups= $this->createForm(new addDocument());
        $formEmailGroups
            ->add('upload',"submit",array("label"=>"Importar"));
        $formEmailGroups['type']->setData('EmailGroup');
        $formEmailGroups->handleRequest($request);

        if ($formEmailGroups->isValid() and $formEmailGroups->isSubmitted()) {
            if($this->checkFile($formEmailGroups->get('document')->getData(),'emailGroup')['response']){
                $this->addFlash('success_import','Se importo correctamente el archivo.');
            }else{
                $this->addFlash('fail_import','Ocurrió un error o el archivo no cumple con el formato requerido.');
            }
        }

//        $emailGroups=$em->getRepository("RocketSellerTwoPickBundle:EmailGroup")->findAll();
        $formChoice = $this->get("form.factory")->createNamedBuilder("formChoiceGroup")
            ->add("groups","entity",array(
                'class'=>"RocketSellerTwoPickBundle:EmailGroup",
                'choice_label'=>'name',
                'multiple'=>false,
                'expanded'=>false,
                'placeholder'=>'seleccione un grupo'))
            ->add('emailTypes',"entity",array(
                'class'=>"RocketSellerTwoPickBundle:EmailType",
                'choice_label'=>'name',
                'multiple'=>false,
                'expanded'=>false,
                'placeholder'=>'Seleccione un correo'))
            ->add('submit','submit',array('label'=>'Enviar'))
            ->getForm();
        $formChoice->handleRequest($request);
        if($formChoice->isValid() and $formChoice->isSubmitted()){
            /** @var EmailGroup $group */
            $group = $formChoice->get("groups")->getData();
            /** @var EmailType $emailType */
            $emailType = $formChoice->get("emailTypes")->getData();
            $infoEmails = $em->getRepository("RocketSellerTwoPickBundle:EmailInfo")->findBy(array('emailGroup'=>$group));
            if(count($infoEmails)>0){
                $toEmail = array();
                /** @var EmailInfo $infoEmail */
                foreach ($infoEmails as $infoEmail) {
                    $toEmail[$infoEmail->getEmail()]=$infoEmail->getName();
                }
                $context["emailType"]=$emailType->getEmailType();
                $context["toEmail"]=$toEmail;
                $send = $this->get("symplifica.mailer.twig_swift")->sendMultipleRecipientsEmailByType($context);
                if($send){
                    $this->addFlash('success_import','Se envió correctamente el correo.');
                }else{
                    $this->addFlash('fail_import','Ocurrió un error enviando el correo.');
                }
            }
        }

        $emailsInfo=$em->getRepository("RocketSellerTwoPickBundle:EmailInfo")->findAll();
        return $this->render("RocketSellerTwoPickBundle:BackOffice:EmailInfoGroup.html.twig",array(
           'emailsInfo'=>$emailsInfo,
            'formEmailGroups'=>$formEmailGroups->createView(),
            'formChoiceGroup'=>$formChoice->createView(),
        ));
    }

    private function checkFile($file,$tipe){
        if (!file_exists('uploads/Files/TempFiles')) {
            mkdir('uploads/Files/TempFiles', 0777, true);
        }
        $errors = array();
        $errors["ehes"]=array();
        $errors["contracts"]=array();
        $change = false;
        $em = $this->getDoctrine()->getManager();
        $absPath = getcwd();
        $tempFile = $file;
        $fileName = md5(uniqid()).'.'.$tempFile->guessExtension();
        $tempFile->move('uploads/Files/Tempfiles',$fileName);
        $inputFileType = \PHPExcel_IOFactory::identify('uploads/Files/Tempfiles'. '/' . $fileName);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        /** @var \PHPExcel $obj */
        $obj = $objReader->load('uploads/Files/Tempfiles' . '/' . $fileName);
        /** @var \PHPExcel_Worksheet $worksheet */
        foreach ($obj->getWorksheetIterator() as $worksheet) {
            /** @var \PHPExcel_Worksheet_Row $row */
            foreach ($worksheet->getRowIterator() as $row) {
                $rowCount= $row->getRowIndex();
                if($worksheet->getCellByColumnAndRow(0,$rowCount)->getValue()!=null and $worksheet->getCellByColumnAndRow(0,$rowCount)->getValue()!=''){
                    switch ($tipe){
                        case 'emailGroup':
                            if($rowCount==2){
                                if(!($worksheet->getCellByColumnAndRow(0, $rowCount)->getValue() == 'ID' and
                                    $worksheet->getCellByColumnAndRow(1, $rowCount)->getValue() == 'GRUPO' and
                                    $worksheet->getCellByColumnAndRow(2, $rowCount)->getValue() == 'NOMBRE' and
                                    $worksheet->getCellByColumnAndRow(3, $rowCount)->getValue() == 'TIPO_DOC' and
                                    $worksheet->getCellByColumnAndRow(4, $rowCount)->getValue() == 'DOCUMENTO' and
                                    $worksheet->getCellByColumnAndRow(5, $rowCount)->getValue() == 'EMAIL')){
                                    unlink('uploads/Files/Tempfiles'.'/'.$fileName);
                                    return array('response'=>false,'errors'=>$errors);
                                }
                            }
                            if ($rowCount > 2) {
                                $groupName = $worksheet->getCellByColumnAndRow(1, $rowCount)->getValue();
                                $fullName = $worksheet->getCellByColumnAndRow(2, $rowCount)->getValue();
                                $docType = $worksheet->getCellByColumnAndRow(3, $rowCount)->getValue();
                                $docNum = $worksheet->getCellByColumnAndRow(4, $rowCount)->getValue();
                                $email = $worksheet->getCellByColumnAndRow(5, $rowCount)->getValue();
                                $group = $em->getRepository('RocketSellerTwoPickBundle:EmailGroup')->findOneBy(array('name'=>$groupName));
                                if(!$group){
                                    $group = new EmailGroup();
                                    $group->setName($groupName);
                                    $em->persist($group);
                                    $em->flush();
                                }
                                $emailInfo = $em->getRepository("RocketSellerTwoPickBundle:EmailInfo")->findOneBy(array("documentType"=>$docType,"document"=>$docNum,"emailGroup"=>$group));
                                if(!$emailInfo){
                                    $emailInfo = new EmailInfo();
                                    $emailInfo->setName($fullName);
                                    $emailInfo->setDocumentType($docType);
                                    $emailInfo->setDocument($docNum);
                                    $emailInfo->setEmail($email);
                                    $emailInfo->setEmailGroup($group);
                                    $em->persist($emailInfo);
                                    $em->flush();
                                }
                            }
                            break;
                        case 'fullTimeCalendar':
                            if($rowCount==2){
                                if(!($worksheet->getCellByColumnAndRow(0, $rowCount)->getValue() == 'Nº' and
                                    $worksheet->getCellByColumnAndRow(1, $rowCount)->getValue() == 'NOMBRE_EMPLEADOR' and
                                    $worksheet->getCellByColumnAndRow(2, $rowCount)->getValue() == 'TELEFONO' and
                                    $worksheet->getCellByColumnAndRow(3, $rowCount)->getValue() == 'TIPO_DOC' and
                                    $worksheet->getCellByColumnAndRow(4, $rowCount)->getValue() == 'DOCUMENTO' and
                                    $worksheet->getCellByColumnAndRow(5, $rowCount)->getValue() == 'NOMBRE_EMPLEADO' and
                                    $worksheet->getCellByColumnAndRow(6, $rowCount)->getValue() == 'TIPO_DOC_EMPLEADO' and
                                    $worksheet->getCellByColumnAndRow(7, $rowCount)->getValue() == 'DOCUMENTO_EMPLEADO' and
                                    $worksheet->getCellByColumnAndRow(8, $rowCount)->getValue() == 'SABADO')){
                                    unlink('uploads/Files/Tempfiles'.'/'.$fileName);
                                    return array('response'=>false,'errors'=>$errors);
                                }
                            }
                            if ($rowCount > 2) {
                                $docType = $worksheet->getCellByColumnAndRow(3, $rowCount)->getValue();
                                $docNum = $worksheet->getCellByColumnAndRow(4, $rowCount)->getValue();
                                $eDocType = $worksheet->getCellByColumnAndRow(6, $rowCount)->getValue();
                                $eDocNum = $worksheet->getCellByColumnAndRow(7, $rowCount)->getValue();
                                $saturday = $worksheet->getCellByColumnAndRow(8, $rowCount)->getValue();
                                $person = $em->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array(
                                    'documentType'=>$docType,
                                    'document'=>$docNum,));
                                $ePerson = $em->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array(
                                    'documentType'=>$eDocType,
                                    'document'=>$eDocNum,
                                ));
                                $employer = $em->getRepository("RocketSellerTwoPickBundle:Employer")->findOneBy(array(
                                    'personPerson'=>$person));
                                $employee = $em->getRepository("RocketSellerTwoPickBundle:Employee")->findOneBy(array(
                                    'personPerson'=>$ePerson));
                                $criteria =Criteria::create()->where(Criteria::expr()->andX(
                                    Criteria::expr()->eq('employerEmployer',$employer),
                                    Criteria::expr()->eq('employeeEmployee',$employee),
                                    Criteria::expr()->gte('state',4)
                                ));
                                /** @var EmployerHasEmployee $ehe */
                                $ehe = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->matching($criteria)->first();
                                if($ehe){
                                    /** @var Contract $activeContract */
                                    $activeContract = $ehe->getActiveContract();
                                    $workSaturday = $activeContract->getWorksSaturday();
                                    if($saturday=='S'){
                                        $activeContract->setWorksSaturday(1);
                                    }else{
                                        $activeContract->setWorksSaturday(0);
                                    }
                                    if($workSaturday!=$activeContract->getWorksSaturday()){
                                        $calType = ($activeContract->getWorksSaturday()==1)? 2 : 1;
                                        $request = new Request();
                                        $request->setMethod('POST');
                                        $request->request->add(array(
                                            'cal_type'=>$calType,
                                            'employee_id'=>$ehe->getIdEmployerHasEmployee()
                                        ));
                                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyEmployee',
                                            array('request' => $request ), array('_format' => 'json'));
                                        if ($insertionAnswer->getStatusCode() != 200) {
                                            $errors['contracts'][]=array(
                                                'contract'=>$activeContract->getIdContract(),
                                                'ehe'=>$activeContract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee(),
                                                );
                                        }else{
                                            $change = true;
                                            $em->persist($activeContract);
                                        }
                                    }
                                }else{
                                    if($employee and $employer){
                                        $errors['ehes'][]=array(
                                            'personID'=>$person->getIdPerson(),
                                            'ePersonID'=>$ePerson->getIdPerson(),
                                            'employerID'=>$employer->getIdEmployer(),
                                            'employeeID'=>$employee->getIdEmployee());
                                    } else {
                                        $errors['ehes'][]=array(
                                            'personID'=>$person->getIdPerson(),
                                            'ePersonID'=>$ePerson->getIdPerson());
                                    }
                                }
                            }
                            break;
                    }

                }

            }
            if($change) $em->flush();
            unlink('uploads/Files/Tempfiles'.'/'.$fileName);
            return array('response'=>true,'errors'=>$errors);
        }
    }

    public function fullTimeCalendarViewAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();

        $formFullTimeCalendar= $this->createForm(new addDocument());
        $formFullTimeCalendar
            ->add('upload',"submit",array("label"=>"Importar"));
        $formFullTimeCalendar['type']->setData('fullTimeCalendar');
        $formFullTimeCalendar->handleRequest($request);

        if ($formFullTimeCalendar->isValid() and $formFullTimeCalendar->isSubmitted()) {
            $ans = $this->checkFile($formFullTimeCalendar->get('document')->getData(),'fullTimeCalendar');
            if($ans['response']){
                $this->addFlash('success_import','Se importo correctamente el archivo.');
                foreach ($ans["errors"]['ehes'] as $error) {
                    $this->addFlash('fail_import','Error ehe con idPerson: '.$error['personID'].' y idEPerson: '.$error['ePersonID']);
                }
                foreach ($ans['errors']['contracts'] as $errorContract) {
                    $this->addFlash('fail_import', "Fallo modificando el contrato: " . $errorContract['contract'] . " con ehe: " . $errorContract['ehe']);
                }
            }else{
                $this->addFlash('fail_import','Ocurrió un error o el archivo no cumple con el formato requerido.');

            }
        }
        /** @var QueryBuilder $query */
        $query = $em->createQueryBuilder();
        $query->add('select','con');
        $query->from("RocketSellerTwoPickBundle:Contract",'con')
            ->join("con.employerHasEmployeeEmployerHasEmployee",'ehe')
            ->join("ehe.employerEmployer",'er')
            ->join("ehe.employeeEmployee",'ee')
            ->join("ee.personPerson",'eep')
            ->join('er.personPerson','erp')
            ->join("con.timeCommitmentTimeCommitment",'tc')
            ->join("RocketSellerTwoPickBundle:User",'u','WITH','u.personPerson=erp.idPerson')
            ->where("ehe.state>=4")
            ->andWhere("con.state=1")
            ->andWhere("tc.code='TC'")
            ->orderBy("u.id",'ASC');
        $contracts = $query->getQuery()->getResult();
        return $this->render("RocketSellerTwoPickBundle:BackOffice:fullTimeCalendarInfo.html.twig",array(
            'formFullTimeCalendar'=>$formFullTimeCalendar->createView(),
            'contracts'=>$contracts
        ));
    }

    public function modifyNoveltiesInfoAction($name,$lastName,$contract,$documentType,$document,$email,$index,Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        if($name == "#")$name = '';
        if($lastName == "#")$lastName = '';
        if($contract == "#")$contract = '';
        if($documentType =='#')$documentType = '';
        if($document =='#')$document = '';
        if($email =='#')$email = '';
        $form = $this->get('form.factory')->createNamedBuilder('formFilter')
            ->add('name','text',array('label'=>'Nombres:','required'=>false))
            ->add('lastName','text',array('label'=>'Apellidos:','required'=>false))
            ->add('contract','text',array('label'=>'Id Contrato:','required'=>false))
            ->add('documentType','choice', array('label'=>'Tipo Documento:','expanded'=>false,'multiple'=>false,'placeholder' => 'tipo','required'=>false,
                'choices' => array(
                    'CC'=> 'Cédula',
                    'CE' => 'Cédula de Extranjeria',
                    'PASAPORTE' => 'Pasaporte',
                )))
            ->add('document','text',array('label'=>'No. Documento:','required'=>false))
            ->add('email','text',array('label'=>'Email:','required'=>false))
            ->add('search','submit',array('label' => 'Buscar'))->getForm();
        if($name!='')
            $form->get('name')->setData($name);
        if($lastName!='')
            $form->get('lastName')->setData($lastName);
        if($contract!='')
            $form->get('contract')->setData($contract);
        if($documentType!='')
            $form->get('documentType')->setData($documentType);
        if($document!='')
            $form->get('document')->setData($document);
        if($email!='')
            $form->get('email')->setData($email);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $index = 1;
            $name = $form->get('name')->getData();
            $lastName = $form->get('lastName')->getData();
            $contract = intval($form->get('contract')->getData());
            $document = $form->get('document')->getData();
            $documentType = $form->get('documentType')->getData();
            $email = $form->get('email')->getData();
        }
        $em = $this->getDoctrine()->getManager();
        if($name=='' and $lastName=='' and $contract=='' and $document=='' and $documentType=='' and $email==''){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:modifyNoveltiesView.html.twig',array(
                'form'=>$form->createView(),
                'contracts'=>array(),
                'maxIndex'=>intval(1)));
        }else{
            /** @var QueryBuilder $query */
            $query = $em->createQueryBuilder();
            $query->add('select', 'c');

            $query->from("RocketSellerTwoPickBundle:Contract",'c')
                ->leftJoin("c.payrolls",'p')
                ->leftJoin("p.sqlNovelties",'nov')
                ->leftJoin("p.purchaseOrdersDescription",'pod')
                ->leftJoin("c.employerHasEmployeeEmployerHasEmployee",'ehe')
                ->leftJoin("ehe.employerEmployer",'er')
                ->leftJoin("ehe.employeeEmployee",'ee')
                ->leftJoin("er.personPerson",'pe')
                ->leftJoin("ee.personPerson",'ep')
                ->leftJoin("RocketSellerTwoPickBundle:User",'u','WITH','pe.idPerson = u.personPerson');
            if($name!= ''){
                $strex = explode(' ',$name);
                foreach ($strex as $str) {
                    $sbstrs = $this->get("app.symplifica_utils")->getAllStrings($str);
                    foreach ($sbstrs as $sbstr) {
                        $query->andWhere($query->expr()->orX(
                            $query->expr()->like("pe.names","?1"),
                            $query->expr()->like("ep.names","?1")
                        ))
                            ->setParameter('1',"%".$sbstr."%");
                    }
                }
            }
            if($lastName!=''){
                $strex = explode(' ',$lastName);
                foreach ($strex as $str) {
                    $sbstrs = $this->get("app.symplifica_utils")->getAllStrings($str);
                    foreach ($sbstrs as $sbstr) {
                        $query->andWhere($query->expr()->orX(
                            $query->expr()->like("pe.lastName1","?2"),
                            $query->expr()->like("ep.lastName1","?2"),
                            $query->expr()->like("pe.lastName2","?2"),
                            $query->expr()->like("ep.lastName2","?2")
                        ))
                            ->setParameter('2',"%".$sbstr."%");
                    }
                }
            }
            if($contract!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->eq("c.idContract","?1")
                ))
                    ->setParameter('1',$contract);
            }
            if($document!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.document","?2"),
                    $query->expr()->like("ep.document","?2"),
                    $query->expr()->like("pe.document","?2"),
                    $query->expr()->like("ep.document","?2")
                ))
                    ->setParameter('2',"%".$document."%");
            }
            if($email!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("u.email","?3")
                ))
                    ->setParameter('3',"%".$email."%");
            }
            if($documentType!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.documentType","?4"),
                    $query->expr()->like("ep.documentType","?4")
                ))
                    ->setParameter('4',"%".$documentType."%");
            }
            $query->addOrderBy('c.idContract','ASC');
            $maxIndex = 1;
            $results = count($query->getQuery()->getResult());
            if($results==0){
                $this->addFlash('fail',"No se encontraron resultados para esta busqueda.");
            }
            if($results%10!=0){
                $maxIndex = intval($results/10)+1;
            }else{
                $maxIndex = intval($results/10);
            }
            if($index==1){
                $query->setFirstResult(0);
                $query->setMaxResults(10);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $contracts = $paginator->getIterator();
            }else{
                $query->setFirstResult(($index-1)*10);
                $query->setMaxResults(10);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $contracts = $paginator->getIterator();
            }
            if($results > 10)
                $this->addFlash('success',"La query obtuvo ".$results." resultados.");

            return $this->render("RocketSellerTwoPickBundle:BackOffice:modifyNoveltiesView.html.twig",array(
                'form'=>$form->createView(),
                'contracts'=>$contracts,
                'index'=>intval($index),
                'name'=>$name,
                'lastName'=>$lastName,
                'contract'=>$contract,
                'document'=>$document,
                'documentType'=>$documentType,
                'email'=>$email,
                'maxIndex'=>intval($maxIndex),
            ));
        }
    }

    public function modifyPayrollAction($idPayroll,$noveltiesHadChanged = false,$executed = false,Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        if($idPayroll==0)
            return $this->createNotFoundException();
        $em = $this->getDoctrine()->getManager();
        /** @var Payroll $payroll */
        $payroll = $em->getRepository("RocketSellerTwoPickBundle:Payroll")->find($idPayroll);
        $novelties = $payroll->getSqlNovelties();
        $noveltiesForms = array();
        $noveltiesFormsViews = array();
        /** @var Novelty $novelty */
        foreach ($novelties as $novelty) {
            $noveltyForm = $this->get("form.factory")->createNamedBuilder("form_edit_novelty_".$novelty->getIdNovelty())
                ->add('idNovelty','text',array(
                    'required'=>true,
                    'disabled'=>true,
                    'label'=>false,
                ))
                ->add('noveltyType','entity',array(
                    'label'=>false,
                    'placeholder'=>'Seleccionar',
                    'class'=>'RocketSellerTwoPickBundle:NoveltyType',
                    'choice_label'=>'name',
                    'multiple'=>false,
                    'expanded'=>false,
                    'required'=>true,
                ))
                ->add('units','text',array(
                    'label'=>false,
                    'required'=>true,
                ))
                ->add('value','text',array(
                    'label'=>false,
                    'required'=>true,
                ))
                ->add('submit','submit',array(
                    'label'=>'Guardar'
                ))
                ->getForm();
            $noveltyForm->get("idNovelty")->setData($novelty->getIdNovelty());
            $noveltyForm->get("noveltyType")->setData($novelty->getNoveltyTypeNoveltyType());
            $noveltyForm->get("units")->setData(intval($novelty->getUnits()));
            $noveltyForm->get("value")->setData(floatval($novelty->getSqlValue()));

            $noveltiesForms[$novelty->getIdNovelty()]=$noveltyForm;
        }
        foreach ($noveltiesForms as $noveltyForm) {
            $noveltyForm->handleRequest($request);
            if($noveltyForm->isValid() and $noveltyForm->isSubmitted()){
                $noveltyHasChanged = false;
                $idNovelty = $noveltyForm->get("idNovelty")->getData();
                /** @var Novelty $actualNovelty */
                $actualNovelty = $em->getRepository("RocketSellerTwoPickBundle:Novelty")->find($idNovelty);
                /** @var NoveltyType $noveltyType */
                $noveltyType = $noveltyForm->get("noveltyType")->getData();
                $units = intval($noveltyForm->get("units")->getData());
                $value = floatval($noveltyForm->get("value")->getData());
                if($actualNovelty->getNoveltyTypeNoveltyType()!=$noveltyType){
                    $log =  new Log($this->getUser(),"Novelty",'noveltyType',$idNovelty,$actualNovelty->getNoveltyTypeNoveltyType()->getIdNoveltyType(),$noveltyType->getIdNoveltyType(),"Se cambió el tipo de novedad");
                    $actualNovelty->setNoveltyTypeNoveltyType($noveltyType);
                    $em->persist($actualNovelty);
                    $em->persist($log);
                    $em->flush();
                    $noveltyHasChanged = true;
                    $noveltiesHadChanged = true;
                }
                if($actualNovelty->getUnits()!=$units){
                    $log =  new Log($this->getUser(),"Novelty",'units',$idNovelty,$actualNovelty->getUnits(),$units,"Se cambiaron las unidades de una novedad");
                    $actualNovelty->setUnits($units);
                    $em->persist($actualNovelty);
                    $em->persist($log);
                    $em->flush();
                    $noveltyHasChanged = true;
                    $noveltiesHadChanged = true;
                }
                if($actualNovelty->getSqlValue()!=$value){
                    $log =  new Log($this->getUser(),"Novelty",'sqlValue',$idNovelty,$actualNovelty->getSqlValue(),$value,"Se cambió el valor de una novedad");
                    $actualNovelty->setSqlValue($value);
                    $em->persist($actualNovelty);
                    $em->persist($log);
                    $em->flush();
                    $noveltyHasChanged = true;
                    $noveltiesHadChanged = true;
                }
                if($noveltyHasChanged){
                    $this->addFlash('success',"Se modificó correctamente la novedad ".$actualNovelty->getIdNovelty().".");
                }
            }
            $noveltiesFormsViews[$noveltyForm->get("idNovelty")->getData()]=$noveltyForm->createView();
        }
        $newNoveltyForm = $this->get("form.factory")->createNamedBuilder("new_novelty")
            ->add('noveltyType','entity',array(
                'label'=>false,
                'placeholder'=>'Seleccionar',
                'class'=>'RocketSellerTwoPickBundle:NoveltyType',
                'choice_label'=>'name',
                'multiple'=>false,
                'expanded'=>false,
                'required'=>true,
            ))
            ->add('units','text',array(
                'label'=>false,
                'required'=>true,
            ))
            ->add('value','text',array(
                'label'=>false,
                'required'=>true,
            ))
            ->add('submit','submit',array(
                'label'=>'Guardar'
            ))
            ->getForm();
        $newNoveltyForm->handleRequest($request);
        if($newNoveltyForm->isSubmitted() and $newNoveltyForm->isValid()){
            if($payroll->getPaid()==0){
                /** @var User $backUser */
                $backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>'backofficesymplifica@gmail.com'));
                $request  = new Request();
                $request->setMethod("PUT");
                $request->request->add(array(
                    "month" => $payroll->getMonth(),
                    "year"=>$payroll->getYear(),
                    "period" => $payroll->getPeriod(),
                    "day"=> ($payroll->getPeriod()==2)?16:26,
                    "token"=>$backUser->getSalt(),
                    "idpayroll" => $payroll->getIdPayroll()
                ));
                $response = $this->forward("RocketSellerTwoPickBundle:PayrollMethodRest:putAutoLiquidatePayroll",array("request"=>$request),array('_format'=>'json'));
                if($response->getStatusCode()!=200){
                    $this->addFlash('fail',"Error Congelando la nómina.");
                }else{
                    $newNovelty = new Novelty();
                    $newNovelty->setNoveltyTypeNoveltyType($newNoveltyForm->get("noveltyType")->getData());
                    $newNovelty->setName($newNoveltyForm->get("noveltyType")->getData()->getName());
                    $newNovelty->setUnits(intval($newNoveltyForm->get("units")->getData()));
                    $newNovelty->setSqlValue(floatval($newNoveltyForm->get("value")->getData()));
                    $newNovelty->setSqlPayrollPayroll($payroll);
                    $payroll->addSqlNovelty($newNovelty);
                    $em->persist($newNovelty);
                    $em->flush();
                    $this->addFlash('success',"Se congeló correctamente la nómina.");
                    $this->addFlash('success',"Se creo correctamente la novedad.");
                }
            }else{
                $newNovelty = new Novelty();
                $newNovelty->setNoveltyTypeNoveltyType($newNoveltyForm->get("noveltyType")->getData());
                $newNovelty->setName($newNoveltyForm->get("noveltyType")->getData()->getName());
                $newNovelty->setUnits(intval($newNoveltyForm->get("units")->getData()));
                $newNovelty->setSqlValue(floatval($newNoveltyForm->get("value")->getData()));
                $newNovelty->setSqlPayrollPayroll($payroll);
                $payroll->addSqlNovelty($newNovelty);
                $em->persist($newNovelty);
                $em->flush();
                $this->addFlash('success',"Se creo correctamente la novedad.");
            }
            return $this->redirectToRoute("modify_payroll",array('idPayroll'=>$payroll->getIdPayroll(),'noveltiesHadChanged'=>true,'executed'=>true),302);
        }
        if($noveltiesHadChanged){
            $error = false;
            if($payroll->getPaid()==0 and !$executed){
                /** @var User $backUser */
                $backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>'backofficesymplifica@gmail.com'));
                $request  = new Request();
                $request->setMethod("PUT");
                $request->request->add(array(
                    "month" => $payroll->getMonth(),
                    "year"=>$payroll->getYear(),
                    "period" => $payroll->getPeriod(),
                    "day"=> ($payroll->getPeriod()==2)?16:26,
                    "token"=>$backUser->getSalt(),
                    "idpayroll" => $payroll->getIdPayroll()
                ));
                $response = $this->forward("RocketSellerTwoPickBundle:PayrollMethodRest:putAutoLiquidatePayroll",array("request"=>$request),array('_format'=>'json'));
                if($response->getStatusCode()!=200){
                    $this->addFlash('fail',"Error Congelando la nómina.");
                    $error = true;
                }else{
                    $this->addFlash('success',"Se congeló correctamente la nómina.");
                    return $this->redirectToRoute("modify_payroll",array('idPayroll'=>$payroll->getIdPayroll(),'noveltiesHadChanged'=>true,'executed'=>true),302);
                }
            }
            if(!$error){
                $value = 0;
                /** @var Novelty $novelty */
                foreach ($payroll->getSqlNovelties() as $novelty) {
                    if($novelty->getNoveltyTypeNoveltyType()->getNaturaleza() == 'DEV'){
                        $value += $novelty->getSqlValue();
                    }else{
                        $value -= $novelty->getSqlValue();
                    }
                }
                /** @var PurchaseOrdersDescription $pod */
                $pod = $payroll->getPurchaseOrdersDescription()->first();
                if($pod!=null){
                    $log = new Log($this->getUser(),'PurchaseOrdersDescription','value',$pod->getIdPurchaseOrdersDescription(),$pod->getValue(),$value,"Se modificó el valor del purchaseOrderDescription de la nomina con payroll ".$payroll->getIdPayroll().".");
                    $pod->setValue($value);
                    $em->persist($pod);
                    $em->persist($log);
                    $em->flush();
                }
            }
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:modifyPayrollView.html.twig',array(
            'payroll'=>$payroll,
            'noveltiesForms'=>$noveltiesFormsViews,
            'noveltiesHadChanged'=>$noveltiesHadChanged,
            'newNoveltyForm'=>$newNoveltyForm->createView(),
            'pods'=>$payroll->getPurchaseOrdersDescription(),
        ));
    }

    public function modifyPilaDetailsAction($idPila,Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $podPila = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->find($idPila);
        if($podPila == null){
            $this->createNotFoundException();
        }
        $payrolls = $podPila->getPayrollsPila();
        $pilasForms = array();
        /** @var Payroll $payroll */
        foreach ($payrolls as $payroll) {
            $pilas = $payroll->getPilaDetails();
            /** @var PilaDetail $pila */
            foreach ($pilas as $pila) {
                $pilaForm = $this->get("form.factory")->createNamedBuilder("form_edit_pila_".$pila->getIdPilaDetail())
                    ->add('empValue','number',array(
                        'label'=>false,
                        'required'=>true,
                    ))
                    ->add('ciaValue','number',array(
                        'label'=>false,
                        'required'=>true,
                    ))
                    ->add('submit','submit',array(
                        'label'=>'Guardar'
                    ))
                    ->getForm();
                $pilaForm->get("empValue")->setData($pila->getSqlValueEmp());
                $pilaForm->get("ciaValue")->setData($pila->getSqlValueCia());
                $pilaForm->handleRequest($request);
                $pilasForms[$pila->getIdPilaDetail()]=$pilaForm->createView();
                if($pilaForm->isValid() and $pilaForm->isSubmitted()){
                    $error = false;
                    if($pila->getPayrollPayroll()->getPaid()==0){
                        /** @var User $backUser */
                        $backUser = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>'backofficesymplifica@gmail.com'));
                        $request  = new Request();
                        $request->setMethod("PUT");
                        $request->request->add(array(
                            "month" => $payroll->getMonth(),
                            "year"=>$payroll->getYear(),
                            "period" => $payroll->getPeriod(),
                            "day"=> ($payroll->getPeriod()==2)?16:26,
                            "token"=>$backUser->getSalt(),
                            "idpayroll" => $payroll->getIdPayroll()
                        ));
                        $response = $this->forward("RocketSellerTwoPickBundle:PayrollMethodRest:putAutoLiquidatePayroll",array("request"=>$request),array('_format'=>'json'));
                        if($response->getStatusCode()!=200){
                            $this->addFlash('fail',"Error Congelando la nómina.");
                            $error = true;
                        }else{
                            $this->addFlash('success',"Se congeló correctamente la nómina.");
                        }
                    }
                    if(!$error){
                        $change = false;
                        $empVal = intval($pilaForm->get("empValue")->getData());
                        $ciaVal = intval($pilaForm->get("ciaValue")->getData());
                        if($empVal<0 or $ciaVal <0){
                            $this->addFlash('fail',"Los valores no pueden ser negativos.");
                        }else{
                            if($pila->getSqlValueEmp()!=$empVal){
                                $log = new Log($this->getUser(),'PilaDetail','SqlValueEmp',$pila->getIdPilaDetail(),$pila->getSqlValueEmp(),$empVal,"Se modificó el valor del empleado de la pila con id: ".$pila->getIdPilaDetail().".");
                                $pila->setSqlValueEmp($empVal);
                                $em->persist($pila);
                                $em->persist($log);
                                $em->flush();
                                $change =true;
                            }
                            if($pila->getSqlValueCia()!=$ciaVal){
                                $log = new Log($this->getUser(),'PilaDetail','SqlValueCia',$pila->getIdPilaDetail(),$pila->getSqlValueCia(),$ciaVal,"Se modificó el valor del empleador de la pila con id: ".$pila->getIdPilaDetail().".");
                                $pila->setSqlValueCia($ciaVal);
                                $em->persist($pila);
                                $em->persist($log);
                                $em->flush();
                                $change =true;
                            }
                        }
                        if($change){
                            if($this->updatePilaPurchaseOrderValue($podPila)){
                                $this->addFlash('success',"Se modificó correctamente la pila");
                            }else{
                                $this->addFlash('fail',"Sucedió un error actualizando el valor de la pila");
                            }

                        }
                    }
                }
            }
        }
        return $this->render('RocketSellerTwoPickBundle:BackOffice:modifyPilaView.html.twig',array(
            'podPila'=>$podPila,
            'payrolls'=>$payrolls,
            'pilasForms'=>$pilasForms,
        ));
    }

    public function updatePilaPurchaseOrderValue(PurchaseOrdersDescription $podPila){
        try{
            $em = $this->getDoctrine()->getManager();
            $payrolls = $podPila->getPayrollsPila();
            $value = 0;
            /** @var Payroll $payroll */
            foreach ($payrolls as $payroll){
                $pilaDetails = $payroll->getPilaDetails();
                /** @var PilaDetail $pilaDetail */
                foreach ($pilaDetails as $pilaDetail) {
                    $value+= $pilaDetail->getSqlValueCia();
                    $value+= $pilaDetail->getSqlValueEmp();
                }
            }
            $log = new Log($this->getUser(),'PurchaseOrderDescription','Value',$podPila->getIdPurchaseOrdersDescription(),$podPila->getValue(),$value,"Se modificó el valor de la purchase order description".$podPila->getIdPurchaseOrdersDescription().".");
            $podPila->setValue($value);
            $em->persist($log);
            $em->persist($podPila);
            $em->flush();
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    public function deleteNoveltyAction($idPayroll, $idNovelty){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $novelty = $em->getRepository("RocketSellerTwoPickBundle:Novelty")->find($idNovelty);
        if($novelty->getSqlPayrollPayroll()->getIdPayroll()==$idPayroll){
            $em->remove($novelty);
            $em->flush();
            $this->addFlash('success',"Se eliminó correctamente la novedad.");
            return $this->redirectToRoute("modify_payroll",array('idPayroll'=>$idPayroll,'noveltiesHadChanged'=>true),302);
        }
        return $this->redirectToRoute("modify_payroll",array('idPayroll'=>$idPayroll,'noveltiesHadChanged'=>false),302);
    }

    public function modifyContractsViewAction($contract,$documentType,$document,$email,$index,Request $request){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        if($contract == "#")$contract = '';
        if($documentType =='#')$documentType = '';
        if($document =='#')$document = '';
        if($email =='#')$email = '';

        $form = $this->get('form.factory')->createNamedBuilder('formFilter')
            ->add('contract','text',array('label'=>'Id Contrato:','required'=>false))
            ->add('documentType','choice', array('label'=>'Tipo Documento:','expanded'=>false,'multiple'=>false,'placeholder' => 'tipo','required'=>false,
                'choices' => array(
                    'CC'=> 'Cédula',
                    'CE' => 'Cédula de Extranjeria',
                    'PASAPORTE' => 'Pasaporte',
                )))
            ->add('document','text',array('label'=>'No. Documento:','required'=>false))
            ->add('email','text',array('label'=>'Email:','required'=>false))
            ->add('search','submit',array('label' => 'Buscar'))->getForm();

        if($contract!='')
            $form->get('contract')->setData($contract);
        if($documentType!='')
            $form->get('documentType')->setData($documentType);
        if($document!='')
            $form->get('document')->setData($document);
        if($email!='')
            $form->get('email')->setData($email);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $index = 1;
            $contract = intval($form->get('contract')->getData());
            $document = $form->get('document')->getData();
            $documentType = $form->get('documentType')->getData();
            $email = $form->get('email')->getData();
        }

        $em = $this->getDoctrine()->getManager();
        if($contract=='' and $document=='' and $documentType=='' and $email==''){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:modifyContractsView.html.twig',array(
                'form'=>$form->createView(),
                'users'=>$em->getRepository("RocketSellerTwoPickBundle:User")->find(0),
                'maxIndex'=>intval(1)));
        }else{
            /** @var QueryBuilder $query */
            $query = $em->createQueryBuilder();
            $query->add('select', 'u');

            $query->from("RocketSellerTwoPickBundle:User",'u')
                ->leftJoin("u.personPerson",'pe')
                ->Join("u.realProcedures",'pr')
                ->join('pe.employer','em')
                ->leftJoin('pe.phones','ph')
                ->leftJoin("em.employerHasEmployees",'ehe')
                ->leftJoin("ehe.employeeEmployee",'ee')
                ->leftJoin("ehe.contracts",'c')
                ->leftJoin("c.activePayroll",'ap')
                ->leftJoin("c.payMethodPayMethod",'pm')
                ->leftJoin("pm.payTypePayType",'pt')
                ->leftJoin("ee.personPerson",'ep');

            if($contract!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->eq("c.idContract","?1")
                ))
                    ->setParameter('1',$contract);
            }
            if($document!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.document","?2"),
                    $query->expr()->like("ep.document","?2"),
                    $query->expr()->like("pe.document","?2"),
                    $query->expr()->like("ep.document","?2")
                ))
                    ->setParameter('2',"%".$document."%");
            }
            if($email!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("u.email","?3")
                ))
                    ->setParameter('3',"%".$email."%");
            }
            if($documentType!=''){
                $query->andWhere($query->expr()->orX(
                    $query->expr()->like("pe.documentType","?4"),
                    $query->expr()->like("ep.documentType","?4")
                ))
                    ->setParameter('4',"%".$documentType."%");
            }
            $query->addOrderBy('u.id','ASC');
            $maxIndex = 1;
            $results = count($query->getQuery()->getResult());
            if($results==0){
                $this->addFlash('fail',"El usuario tiene errores, no se puede mostrar la información.");
            }
            if($results%3!=0){
                $maxIndex = intval($results/3)+1;
            }else{
                $maxIndex = intval($results/3);
            }
            if($index==1){
                $query->setFirstResult(0);
                $query->setMaxResults(3);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $users = $paginator->getIterator();
            }else{
                $query->setFirstResult(($index-1)*3);
                $query->setMaxResults(3);
                $paginator = new Paginator($query,$fetchJoinCollection = true);
                $users = $paginator->getIterator();
            }
            if($results > 3){
                $this->addFlash('success',"La query obtuvo ".$results." resultados.");
            }
            $editFormViews = array();
            $today = new DateTime();
            /** @var User $user */
            foreach ($users as $user) {
                $editForm = $this->get("form.factory")->createNamedBuilder("formEditUser".$user->getId())
                    ->add('email','email',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('enabled','choice',array(
                        'label'=>false,
                        'choices'=>array(
                            '0'=>'No',
                            '1'=>'Si',
                            'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        ),
                        'placeholder'=>'Seleccionar',
                        'required'=>false,
                    ))
                    ->add('code','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_SUPER_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('dataCredit','number',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('isFree','number',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('legalFlag','number',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('pay','number',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('names','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('lastName1','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('lastName2','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('documentType','choice',array(
                        'label'=>false,
                        'choices'=>array(
                            'CC'=>'Cédula',
                            'CE'=>'Cédula de Extranjería',
                            'PASAPORTE'=>'Pasaporte',
                        ),
                        'placeholder'=>'Seleccionar',
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('document','text',array(
                        'label'=>false,
                        'required'=>false,
                    ))
                    ->add('expeditionDate', 'date', array(
                        'label'=>false,
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),intval($today->format("Y"))-100),
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('expeditionPlace','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('birthDate', 'date', array(
                        'label'=>false,
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),intval($today->format("Y"))-100),
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('gender','choice',array(
                        'label'=>false,
                        'choices'=>array(
                            'MAS'=>'Masculino',
                            'FEM'=>'Femenino',
                        ),
                        'placeholder'=>'Seleccionar',
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add("mainAddress",'text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add("phone",'text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('registerState','text',array(
                        'label'=>false,
                        'disabled'=>($this->isGranted("ROLE_SUPER_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('documentStatus','entity',array(
                        'label'=>false,
                        'placeholder'=>'Seleccionar',
                        'class'=>'RocketSellerTwoPickBundle:DocumentStatusType',
                        'choice_label'=>'name',
                        'multiple'=>false,
                        'expanded'=>false,
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('documentsDate', 'date', array(
                        'label'=>false,
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),intval($today->format("Y"))-5),
                        'disabled'=>($this->isGranted("ROLE_BACK_OFFICE"))? false : true,
                        'required'=>false,
                    ))
                    ->add('save','submit',array('label'=>'Guardar'))
                    ->getForm();
                $editForm->get("email")->setData($user->getEmail());
                $editForm->get("enabled")->setData(($user->isEnabled())?1:0);
                $editForm->get("code")->setData($user->getCode());
                $editForm->get("dataCredit")->setData($user->getDataCreditStatus());
                $editForm->get("isFree")->setData($user->getIsFree());
                $editForm->get("legalFlag")->setData($user->getLegalFlag());
                $editForm->get("pay")->setData($user->getPaymentState());
                $editForm->get("names")->setData($user->getPersonPerson()->getNames());
                $editForm->get("lastName1")->setData($user->getPersonPerson()->getLastName1());
                $editForm->get("lastName2")->setData($user->getPersonPerson()->getLastName2());
                $editForm->get("documentType")->setData($user->getPersonPerson()->getDocumentType());
                $editForm->get("document")->setData($user->getPersonPerson()->getDocument());
                $editForm->get("expeditionDate")->setData($user->getPersonPerson()->getDocumentExpeditionDate());
                $editForm->get("expeditionPlace")->setData($user->getPersonPerson()->getDocumentExpeditionPlace());
                $editForm->get("birthDate")->setData($user->getPersonPerson()->getBirthDate());
                $editForm->get("gender")->setData($user->getPersonPerson()->getGender());
                $editForm->get("mainAddress")->setData($user->getPersonPerson()->getMainAddress());
                $editForm->get("phone")->setData($user->getPersonPerson()->getPhones()->first()->getPhoneNumber());
                $editForm->get("registerState")->setData($user->getPersonPerson()->getEmployer()->getRegisterState());
                $editForm->get("documentStatus")->setData($user->getPersonPerson()->getEmployer()->getDocumentStatus());
                $editForm->get("documentsDate")->setData($user->getPersonPerson()->getEmployer()->getAllDocsReadyAt());

                $editFormViews[$user->getId()]['userForm']=$editForm->createView();

                $editForm->handleRequest($request);

//                if($editForm->isSubmitted() and $editForm->isValid()){
//                    dump($editForm->get("documentStatus")->getData());
//                }
//
//                /** @var UtilsController $utils */
//                $utils = $this->get("app.symplifica_utils");
//                $name = $utils->mb_capitalize($user->getPersonPerson()->getNames());
//                $otherName = $utils->mb_normalize($user->getPersonPerson()->getNames());
//                $otherNames = $utils->normalizeAccentedChars($user->getPersonPerson()->getNames());
//                dump($name);
//                dump($otherName);
//                dump($otherNames);
//                $ehes = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
//                /** @var EmployerHasEmployee $ehe */
//                foreach ($ehes as $ehe) {
//                    if($ehe->getState()<4) continue;
//                    $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getEmployeeEntity",array('employeeId'=>$ehe->getIdEmployerHasEmployee()),array('_format'=>'json'));
//                    if ($response->getStatusCode() != 200) {
//                        dump("not so good man");
//                    } else {
//                        $data = json_decode($response->getContent(), true);
//                        dump($data);
//                    }
//                    $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getEmployee",array('employeeId'=>$ehe->getIdEmployerHasEmployee()),array('_format'=>'json'));
//                    if ($response->getStatusCode() != 200) {
//                        dump("not so good man");
//                    } else {
//                        $data = json_decode($response->getContent(), true);
//                        dump($data);
//                    }
//                    $nit = $ehe->getEmployerEmployer()->getPersonPerson()->getDocumentType().$ehe->getEmployerEmployer()->getPersonPerson()->getDocument();
//                    $doc = $ehe->getEmployerEmployer()->getPersonPerson()->getDocument();
//                    $response = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getSociety",array('societyNit'=>$nit),array('_format'=>'json'));
//                    if ($response->getStatusCode() != 200) {
//                        dump( "not so good man");
//                    } else {
//                        if($response->getContent() == '[]'){
//                            if($this->correctSociety($user)){
//                                $newResponse = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getSociety",array('societyNit'=>$doc),array('_format'=>'json'));
//                                if($newResponse->getStatusCode() != 200){
//                                    dump( "not so good man");
//                                }else {
//                                    $data = json_decode($response->getContent(), true);
//                                    dump($data);
//                                }
//                            }else{
//                                dump( "not so good man");
//                            }
//                        }else{
//                            $data = json_decode($response->getContent(), true);
//                            dump($data);
//                        }
//
//                    }
//                }

            }




            return $this->render("RocketSellerTwoPickBundle:BackOffice:modifyContractsView.html.twig",array(
                'form'=>$form->createView(),
                'users'=>$users,
                'index'=>intval($index),
                'contract'=>$contract,
                'document'=>$document,
                'documentType'=>$documentType,
                'email'=>$email,
                'maxIndex'=>intval($maxIndex),
                'editFormViews'=>$editFormViews,
            ));
        }
    }


    /**
     * @param User $user
     * @return bool
     */
    private function correctSociety(User $user){
        $request = $this->container->get("request");
        $request->setMethod("POST");
        $person = $user->getPersonPerson();
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var UtilsController $utils */
        $utils = $this->get("app.symplifica_utils");
        $nit = $person->getDocumentType().$person->getDocument();
        $request->request->add(array(
            "society_id" => $employer->getIdSqlSociety(),
            "society_start_date"=>$user->getDateCreated()->format("d-m-Y"),
            "society_name" => $utils->mb_capitalize($user->getPersonPerson()->getNames()),
            "society_nit"=> $nit,
            "society_mail" => $utils->generateRandomEmail()
        ));
        $insertionAnswer = $this->forward("RocketSellerTwoPickBundle:PayrollRest:postModifySociety",array('_format'=>'json'));
        if($insertionAnswer->getStatusCode() != 200){
           return false;
        }
        return true;
    }

    public function generateReportByTypeAction($type){
        $this->denyAccessUnlessGranted("ROLE_BACK_OFFICE", null, 'Unable to access this page!');
        switch($type){
            case "severances":
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
                //setting some properties
                $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
                    ->setLastModifiedBy("Symplifica-Bot")
                    ->setTitle("severances report")
                    ->setSubject("Details")
                    ->setDescription("generated document with severances information")
                    ->setKeywords("severances employees")
                    ->setCategory("Information");
                //setting the active sheet and changing name
                $phpExcelObject->setActiveSheetIndex(0)->setTitle('Información Fondos de Cesantías');
                $outlineBorderTitleStyle= array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FFFFFFFF'),
                        'bold' => true,
                        'size' => 12,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FF818181'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'center',
                        'vertical'=>'center',
                    ),
                );
                $allBordersContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'bold' => true,
                        'size' => 11,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFDBDBDB'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                    ),
                );
                $allBordersNoContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'size' => 10,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFFFFFFF'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                    ),

                );
                $sheet = $phpExcelObject->getActiveSheet();
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(11);
                $sheet->getColumnDimension('C')->setWidth(13);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(11);
                $sheet->getColumnDimension('F')->setWidth(13);
                $sheet->getColumnDimension('G')->setWidth(28);
                $sheet->getColumnDimension('H')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(8);
                $sheet->getColumnDimension('J')->setWidth(10);
                $sheet->getColumnDimension('K')->setWidth(30);
                $sheet->getRowDimension(1)->setRowHeight(17);
                $sheet->getRowDimension(2)->setRowHeight(36);
                $row=1;
                /** @var \PHPExcel_Cell $cell */
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $cell->setValue('INFORMACIÓN FONDOS DE CESANTÍAS');
                $row++;
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $iniCol = $cell->getColumn();
                $cell->setValue('Nº');
                $cell = $sheet->getCellByColumnAndRow(1,$row);
                $cell->setValue('TIPO DOC EMPLEADOR');
                $cell = $sheet->getCellByColumnAndRow(2,$row);
                $cell->setValue('DOCUMENTO EMPLEADOR');
                $cell = $sheet->getCellByColumnAndRow(3,$row);
                $cell->setValue('NOMBRE EMPLEADOR');
                $cell = $sheet->getCellByColumnAndRow(4,$row);
                $cell->setValue('TIPO DOC EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(5,$row);
                $cell->setValue('DOCUMENTO EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(6,$row);
                $cell->setValue('NOMBRE EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(7,$row);
                $cell->setValue('IBC');
                $cell = $sheet->getCellByColumnAndRow(8,$row);
                $cell->setValue('NÚMERO DÍAS');
                $cell = $sheet->getCellByColumnAndRow(9,$row);
                $cell->setValue('VALOR');
                $cell = $sheet->getCellByColumnAndRow(10,$row);
                $cell->setValue('FONDO DE SECANTÍAS');
                $sheet->mergeCells($iniCol.($row-1).':'.$cell->getColumn().($row-1));
                $sheet->getStyle($iniCol.($row-1).':'.$cell->getColumn().($row-1))->applyFromArray($outlineBorderTitleStyle);
                $sheet->getStyle($iniCol.$row.':'.$cell->getColumn().$row)->applyFromArray($allBordersContentStyle);
                $sheet->getStyle("A2:K2")->getAlignment()->setWrapText(true);
                $row++;
                $iniRow = $row;
                $em = $this->getDoctrine()->getManager();
                $criteria = Criteria::create()->where(Criteria::expr()->gte('state',4));
                $ehes = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->matching($criteria);
                $count = 1;
                /** @var EmployerHasEmployee $ehe */
                foreach ($ehes as $ehe) {
//                    if($ehe->getIdEmployerHasEmployee()!=13002) continue;
                    $error = false;
                    $employerDocumentType = $ehe->getEmployerEmployer()->getPersonPerson()->getDocumentType();
                    $employerDocumentNumber = $ehe->getEmployerEmployer()->getPersonPerson()->getDocument();
                    $employeeDocumentType = $ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType();
                    $employeeDocumentNumber = $ehe->getEmployeeEmployee()->getPersonPerson()->getDocument();
                    $employerName = $ehe->getEmployerEmployer()->getPersonPerson()->getFullName();
                    $employeeName = $ehe->getEmployeeEmployee()->getPersonPerson()->getFullName();
                    $severanceEntity = null;
                    $entities = $ehe->getEmployeeEmployee()->getEntities();
                    /** @var EmployeeHasEntity $entity */
                    foreach ($entities as $entity) {
                        if($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == 'FCES'){
                            $severanceEntity = $entity->getEntityEntity();
                        }
                    }
                    $request = new Request();
                    $request->setMethod("GET");
                    $insertionAnswer = $this->forward("RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll",array('employeeId'=>$ehe->getIdEmployerHasEmployee()),array('_format'=>'json'));
                    if($insertionAnswer->getStatusCode() != 200){
                        $error = true;
                    }else{
                        $data = json_decode($insertionAnswer->getContent(), true);
                        $exist =  false;
                        foreach ($data as $item) {
                            if (!is_array($item)) continue;
                            if(!array_key_exists('CON_CODIGO',$item))continue;
                            if (intval($item['CON_CODIGO']) == 181 ){
                                $exist = true;
                                $IBC = intval($item["NOMI_BASE"]);
                                $numDias = intval($item["NOMI_UNIDADES"]);
                                $valueSeverances = intval($item["NOMI_VALOR_LOCAL"]);
                            }
                        }
                    }
                    $col = 0;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($count);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employerDocumentType);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employerDocumentNumber);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employerName);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employeeDocumentType);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employeeDocumentNumber);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($employeeName);
                    $col++;
                    if(!$error and $exist){
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($IBC);
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($numDias);
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($valueSeverances);
                        $col++;
                    }elseif(!$exist){
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("NO EXISTE");
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("NO EXISTE");
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("NO EXISTE");
                        $col++;
                    }else{
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("ERROR");
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("ERROR");
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue("ERROR");
                        $col++;
                    }
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($severanceEntity->getName());
                    $row++;
                    $count++;
                }
                $sheet->getStyle($iniCol.$iniRow.':'.$cell->getColumn().($row-1))->applyFromArray($allBordersNoContentStyle);
                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'Severances_report_'.date('d-m-y').'.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);
                return $response;
                break;
        }
    }
}


