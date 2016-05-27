<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;


class BackOfficeController extends Controller
{
    use SubscriptionMethodsTrait;

    /**
     * Funcion que carga la pagina de inicio del Back Office muestra un acceso rapido a:
     *      Tramites
     *      Consulta
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


	public function checkRegisterAction($idPerson,$idAction)
    {    	
    	$person = $this->loadClassById($idPerson,"Person");    	
    	$user =  $this->loadClassByArray(array('personPerson'=>$person),"User");
       

    	$action = $this->loadClassById($idAction,"Action");

        $employee = $person->getEmployee();
        $employer = $action->getRealProcedureRealProcedure()->getEmployerEmployer();

        if ($employee) {
            $employerHasEmployee = $this->loadClassByArray(
                    array(
                        "employeeEmployee" =>$employee,
                        "employerEmployer" =>$employer,
                    ),"EmployerHasEmployee");  
        }else{
            $employerHasEmployee = null;
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action,'employerHasEmployee'=>$employerHasEmployee));
    }
    public function addToSQLAction($idEmployerHasEmployee){
        $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,"EmployerHasEmployee");
        $addToSQL = $this->addEmployeeToSQL($employerHasEmployee);

        return $this->redirectToRoute("back_office");
    }
    public function makeAfiliationAction($idAction)
    {        
        /** @var Action $action */
    	$action = $this->loadClassById($idAction,"Action"); 
        $cedula = $action->getPersonPerson()->getDocByType("Cedula");
        $pathCedula = $this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
        $nameCedula = $cedula->getMediaMedia()->getName();
        $rut = $action->getPersonPerson()->getDocByType("Rut");
        $pathRut = $this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
        $nameRut = $rut->getMediaMedia()->getName();
        $prevPath = $actual_link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $str = substr($prevPath,7,strlen($prevPath)-12);
        return $this->render('RocketSellerTwoPickBundle:BackOffice:exportDocuments.html.twig',array('action'=>$action,'cedulaPath'=>$str.$pathCedula,
            'cedulaName'=>$nameCedula,'rutPath'=>$str.$pathRut,'rutName'=>$nameRut));
    }
    public function callPersonAction($idAction)
    {
    	$action = $this->loadClassById($idAction,"Action"); 

        return $this->render('RocketSellerTwoPickBundle:BackOffice:callPerson.html.twig',array('action'=>$action));
    }
	public function callEntityAction($idAction)
    {    	
    	$action = $this->loadClassById($idAction,"Action"); 

        return $this->render('RocketSellerTwoPickBundle:BackOffice:callEntity.html.twig',array('action'=>$action));
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
    
    public function showRequestAction(){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        return $this->render('@RocketSellerTwoPick/BackOffice/request.html.twig');
    }
}