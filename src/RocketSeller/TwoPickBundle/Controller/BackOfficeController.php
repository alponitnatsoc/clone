<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\DateTime;


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
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }

    public function generateCodesAction($amount)
    {

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
            //adding to hightec
            if($user->getPersonPerson()->getEmployer()->getIdHighTech()!=null){
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
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
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
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee,'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula));
        }else{
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkEmployee.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee,'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula));
        }
    }

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
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $rut = $action->getPersonPerson()->getDocByType("Rut");
        if ($rut) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathRut = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }else{
                $pathRut = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
    }

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
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $mandato = $action->getPersonPerson()->getDocByType("Mandato");
        if ($mandato) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathMandato = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference');
                $nameMandato = $mandato->getMediaMedia()->getName();
            }else{
                $pathMandato = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference');
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
        /** @var Employer $employer */
        $employer = $user->getPersonPerson()->getEmployer();
        /** @var Document $cedula */
        $contrato= $action->getPersonPerson()->getDocByType('Contrato',$user->getPersonPerson()->getEmployer()->getIdEmployer());
        if ($contrato) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathContrato = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($contrato->getMediaMedia(), 'reference');
                $nameContrato = $contrato->getMediaMedia()->getName();
            }else{
                $pathContrato = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($contrato->getMediaMedia(), 'reference');
                $nameContrato = $contrato->getMediaMedia()->getName();
            }
        }else{
            $pathContrato='';
            $nameContrato='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateContract.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'contrato'=>$contrato,'path_document'=>$pathContrato,'nameDoc'=>$nameContrato));
    }

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
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $rut = $action->getPersonPerson()->getDocByType("Rut");
        if ($rut) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathRut = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }else{
                $pathRut = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
    }

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
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $carta = $action->getPersonPerson()->getDocByType("Carta autorización Symplifica",$user->getPersonPerson()->getEmployer()->getIdEmployer());
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta));
    }

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
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                $pathCedula = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $carta = $action->getPersonPerson()->getDocByType("Carta autorización Symplifica",$user->getPersonPerson()->getEmployer()->getIdEmployer());
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = 'https://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path_document'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta));
    }


    public function addToSQLAction($idEmployerHasEmployee,$procedureId){
        $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,"EmployerHasEmployee");
        $addToSQL = $this->addEmployeeToSQL($employerHasEmployee);
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }


    public function reportErrorAction($idAction,Request $request)
    {
    	$action = $this->loadClassById($idAction,"Action");
    	if ($request->getMethod() == 'POST') {
    		$description = $request->request->get('description');
    		$actionError = new ActionError();
    		$actionError->setDescription($description);
            $actionError->setStatus('Sin contactar');
    		$action->setActionErrorActionError($actionError);
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
        return $this->render('RocketSellerTwoPickBundle:BackOffice:marketing.html.twig', array('landings'=>$landings));
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
                $realSalary = $contract->getSalary() / $contract->getWorkableDaysMonth();
                $realSalary = $realSalary * (($contract->getWorkableDaysMonth() / 4) * 4.34523810);
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
          foreach ($persons as $index => $person) {
            $newName = "Fake Name" . $index;
            $newLastName1 = "FakeLastOne" . $index;
            $newLastName2 = "FakeLastTwo" . $index;
            $person->setNames($newName);
            $person->setLastName1($newLastName1);
            $person->setLastName2($newLastName2);
            if(!is_null($person->getDocumentType())){
              $person->setDocument("702667" + $index);
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

          return $this->redirectToRoute("back_office");
        }

        return $this->redirectToRoute("show_dashboard");
    }
}
