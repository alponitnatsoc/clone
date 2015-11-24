<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use RocketSeller\TwoPickBundle\Entity\Log;

class LogController extends Controller
{
    public function indexAction()
    {
    	/*$person = $this->getUser()->getPersonPerson();
    	$array = array(
    		'sent' => 'mande cualqueir cosa',
    		'receive' => 'No recibi esto'
    		);
    	$data = json_encode($array);
    	$log = $this->createLog($person,"Otro",$data);*/    
    	$logs = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:Log')
		->findAll();
		$datas = array();
		foreach ($logs as $log) {		
			$log->setData(json_decode($log->getData()));
		}

        return $this->render('RocketSellerTwoPickBundle:Default:log.html.twig',array(
        	'logs'=>$logs,
        	'datas'=>$datas
        	));
    }

    /**
     * Metodo para crear un log
     * @param  personPerson $person person que genero el log puede ser null
     * @param  string $type   descripcion de que paso 
     * @param  json array $data   arreglo con la estructura de que se envio y que se recibio 
     * @return Log         retorna un log 
     */
    public function createLog($person ,$type , $data)
    {
    	$em = $this->getDoctrine()->getManager();
    	$log = new Log();
    	$log->setPersonPerson($person);
    	$log->setType($type);
    	$log->setData($data);
    	$em->persist($log);
    	$em->flush();	
    	return $log;
    }
}
