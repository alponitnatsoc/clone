<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\PilaDetail;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use DateTime;
use Doctrine\ORM\QueryBuilder;


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

    public function showBaseRegisterUsersAction()
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $usersRepo= $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users= $usersRepo->findAll();
        return $this->render('RocketSellerTwoPickBundle:BackOffice:showBaseRegisterUsers.html.twig',array('users'=>$users));

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

    /**
     * Funcion para hacer las consultas
     * @return Response /backoffice/request
     */
    public function showRequestAction(){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        return $this->render('@RocketSellerTwoPick/BackOffice/request.html.twig');
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

//        /** test welcome Email*/
//        $context = array(
//            'emailType'=>'welcome',
//            'user'=>$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(3),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);


        $this->get('symplifica.mailer.twig_swift')->sendBackValidatedMessage($this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5),$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(5)->getPersonPerson()->getEmployer()->getEmployerHasEmployees()->first());

//        /** test confirmation Email */
//        $context=array(
//            'emailType'=>'confirmation',
//            'user'=>$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User')->find(3),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);


//        /** test reminder Email */
//        $context=array(
//            'emailType'=>'reminder',
//            'toEmail'=>$toEmail,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

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

//        /** test daviPlata Email */
//        $context = array(
//            'emailType'=>'daviplata',
//            'toEmail'=>$toEmail,
//            'user'=>$this->getUser(),
//            'subject'=>'Información Daviplata',
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test reminderPay Email */
//        $context = array(
//            'emailType'=>'reminderPay',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'days'=>3
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test lastReminderPay Email */
//        $context = array(
//            'emailType'=>'lastReminderPay',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe',
//            'days'=>2
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test succesRecollect Email */
//        /** @var \DateTime $date */
//        $date = new DateTime();
//        $date->setTimezone(new \DateTimeZone('America/Bogota'));
//        $params = array(
//            'ref'=> 'factura',
//            'id' => 3,
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

//        /** test succesfulDispersion Eamil */
//        $context=array(
//            'emailType'=>'succesDispersion',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe Ramírez',
//        );
//        $params = array(
//            'ref'=> 'comprobante',
//            'id' => 4,
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

//        /** test failDispersion Eamil */
//        $context=array(
//            'emailType'=>'failDispersion',
//            'userEmail'=>'algo@algo.com',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
//
//        /** test addPayMethod */
//        $context = array(
//            'emailType'=>'validatePayMethod',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andrés Felipe Ramírez',
//            'starDate'=>new DateTime(),
//            'payMethod'=>'Tarjeta de Credito'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test backWarning Email */
//        $context = array(
//            'emailType'=>'backWarning',
//            'toEmail'=>$toEmail,
//            'idPod'=>1,
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test liquidation Email */
//        $context=array(
//            'emailType'=>'liquidation',
//            'toEmail'=>$toEmail,
//            'userName'=>'Esto es una prueba para daniel',
//            'employerSociety'=> '123123',
//            'documentNumber'=>'1020772509',
//            'userEmail'=>'algo@algo.com',
//            'phone'=>'5138283475',
//            'employeeName'=>'Empleado Prueba',
//            'sqlNumber'=>'101201'
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

//        /** test transactionAcepted Email */
//        $context = array(
//            'emailType'=>'transactionAcepted',
//            'toEmail'=>$toEmail,
//            'userName'=>'Andres felipe',
//            'po'=>$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find(132),
//        );
//        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

        return $this->redirect($this->generateUrl('back_office'));
    }

    public function userViewAction(){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('RocketSellerTwoPickBundle:User')->findAll();

        return $this->render('RocketSellerTwoPickBundle:BackOffice:userView.html.twig',array('users'=>$userRepo));
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

		$podNomina = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product->getIdProduct()));
		$podPila = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product2->getIdProduct()));

		//Now Pod has all the products nomina on the database.
		return $this->render('RocketSellerTwoPickBundle:BackOffice:payState.html.twig', array('podsN' => $podNomina, 'podsP' => $podPila));
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

		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(array("period" => 4, "month" => 10, "year" => 2016, "paid" => 1));
		$pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array("idNovoPay" => "P1"));
		$product = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array("simpleName" => "PP"));

		foreach($payrolls as $payroll){
			$pilaPOD = $payroll->getPila();

			if($pilaPOD->getProductProduct() == NULL){

				$pilaPOD->setPurchaseOrdersStatus($pos);
				$pilaPOD->setProductProduct($product);
				$pilaPOD->setDescription("Pago de Aportes a Seguridad Social mes Octubre");

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

		$payrolls = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findBy(array("period" => 4, "month" => 10, "year" => 2016, "paid" => 1));

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
		$em = $this->getDoctrine()->getManager();
		$podRepo = $em->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription');
		$singlePod = $podRepo->find($podId);
		
		$tranId = $singlePod->getUploadedFile();
		
		$tranRepo = $em->getRepository('RocketSellerTwoPickBundle:Transaction');
		$singleTran = $tranRepo->find($tranId);
		
		$document = $singleTran->getTransactionState()->getDocument();
		$media = $document->getMediaMedia();
		
		if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
			$docUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
		}
		$docName = $document->getDocumentTypeDocumentType()->getName().'.'.$media->getExtension();
		$docExportName = "Log visual HT ID $podId.zip";
		
		# send the file to the browser as a download
		header("Content-disposition: attachment; filename=$docExportName");
		header('Content-type: application/zip');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		ob_clean();
		flush();
		readfile($docUrl . $docName);
		ignore_user_abort(true);
	
		return $this->redirectToRoute('ajax', array(), 301);
	}
}
