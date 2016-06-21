<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


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
        dump('llamo');
        /** @var Document $cedula */
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        dump('salio');
        dump($cedula->getMediaMedia()->getProviderReference());
        if ($cedula) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                dump('es host');
                $pathCedula = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }else{
                dump('es dev o alpha');
                
                if($this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(),'reference')){
                    dump($this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(),'reference'));
                }

                $pathCedula = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            dump('no encontro cedula');
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
        dump('cedula path: '.$pathCedula);
        if($employerHasEmployee == null){
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee,'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula));
        }else{
            return $this->render('RocketSellerTwoPickBundle:BackOffice:checkEmployee.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee,'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula));
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
                $pathCedula = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
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
                $pathRut = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }
        
        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
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
                $pathCedula = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
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
                $pathRut = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                $nameRut = $rut->getMediaMedia()->getName();
            }
        }else{
            $pathRut='';
            $nameRut='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula ,'rut'=>$rut,'pathRut'=>$pathRut,'nameRut'=>$nameRut));
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
                $pathCedula = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $carta = $action->getPersonPerson()->getDocByType("Carta autorización Symplifica");
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ValidateEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta));
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
                $pathCedula = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                $nameCedula = $cedula->getMediaMedia()->getName();
            }
        }else{
            $pathCedula='';
            $nameCedula='';
        }
        $carta = $action->getPersonPerson()->getDocByType("Carta autorización Symplifica");
        if ($carta) {
            if($_SERVER['HTTP_HOST'] =='127.0.0.1:8000'){
                $pathCarta = 'http://'.'127.0.0.1:8000' . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }else{
                $pathCarta = 'http://' . $actual_link = $_SERVER['HTTP_HOST'] . $this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                $nameCarta = $carta->getMediaMedia()->getName();
            }
        }else{
            $pathCarta='';
            $nameCarta='';
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:ViewEmployeeDocuments.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action, 'cedula'=>$cedula,'path'=>$pathCedula,'nameDoc'=>$nameCedula ,'carta'=>$carta,'pathCarta'=>$pathCarta,'nameCarta'=>$nameCarta));
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
}