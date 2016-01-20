<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;

class BackOfficeController extends Controller
{

    public function indexAction()
    {
        return $this->render('RocketSellerTwoPickBundle:BackOffice:index.html.twig');
    }
	public function checkRegisterAction($idEmployer)
    {    	
    	$employer = $this->loadClassById($idEmployer,"Employer");
    	$person = $employer->getPersonPerson();
    	$user =  $this->loadClassByArray(array('personPerson'=>$person),"User");
        return $this->render('RocketSellerTwoPickBundle:BackOffice:checkRegister.html.twig',array('user'=>$user , 'employer'=>$employer));
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