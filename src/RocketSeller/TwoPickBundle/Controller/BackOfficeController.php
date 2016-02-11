<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\ActionError;

class BackOfficeController extends Controller
{

    public function indexAction()
    {
        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }
	public function checkRegisterAction($idPerson,$idAction)
    {    	
    	$person = $this->loadClassById($idPerson,"Person");    	
    	$user =  $this->loadClassByArray(array('personPerson'=>$person),"User");
    	$action = $this->loadClassById($idAction,"Action");
        return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'person'=>$person,'action'=>$action));
    }
    public function makeAfiliationAction($idAction)
    {        
    	$action = $this->loadClassById($idAction,"Action"); 

        return $this->render('RocketSellerTwoPickBundle:BackOffice:exportDocuments.html.twig',array('action'=>$action));	
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
        $employers = $em->getRepository('RocketSellerTwoPickBundle:Employer')
                ->findByRegisterExpress(1);               
        return $this->render('RocketSellerTwoPickBundle:BackOffice:registerExpress.html.twig',array('employers'=>$employers));    
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
}