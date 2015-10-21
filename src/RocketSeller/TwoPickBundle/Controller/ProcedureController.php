<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\Country;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class ProcedureController extends Controller
{
    public function indexAction()
    {        
        return new Response('<html><body>Hello!</body></html>');
    }
    /**
     * estructura de tramite para generar vueltas y tramites
     * @param  $id $id_employer       id del empleador que genera el tramite
     * @param  $id $id_procedure_type id del tipo de tramite a realizar
     * @param  Array() $employees      arreglo de empleados con:
     *                               ->id_employee
     *                          	 ->id_contrato
     *                               ->Array docs
     *                               ->Array entidades
     *                                 		->id_entidad
     *                                 		->id_tipo_vuelta
     *                                 		->sort_order
     * @return integer $priority       prioridad del empleador (vip, regular)
     */
    public function validateAction($id)
    {		
		    $employee = $this->getDoctrine()
		        ->getRepository('RocketSellerTwoPickBundle:Employee')
		        ->find($id);

		    if (!$employee) {
    			/*$country = new Country();
				$country->setName('otrolandia'.$id_employee);

				$em = $this->getDoctrine()->getManager();

				$em->persist($country);
				$em->flush();*/
				return new Response('No se encontro el empleado');
		    }
		    else{
		    	return new Response('imported employee named: '.$employee->getpersonPerson()->getNames());
		    }
    		
    }
}
