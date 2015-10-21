<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\Country;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Procedure;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProcedureController extends Controller
{
    public function indexAction($name)
    {
        return $this->render(
            'RocketSeller:TwoPickBundle:Default:index.html.twig',
            array('name' => $name)
        );
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
    public function validateAction()
    {		
    		//datos de prueba
    		$id_employer =1;
    		$id_procedure_type = "Inscripción";
    		$priority = 1;
    		$employes = array(
    			array(
    				'id_employee' => 1,
    				'id_contrato' => 1,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			'entities' => array(
			    					'id_entidad' => 1,
			    					'id_tipo_vuelta' => 1,
			    					)	
    				),
    			array(
    				'id_employee' => 2,
    				'id_contrato' => 2,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			'entities' => array(
			    					'id_entidad' => 1,
			    					'id_tipo_vuelta' => 1,
			    					)	
    				)
    			);


    		$employerSearch = $this->getDoctrine()
    		->getRepository('RocketSellerTwoPickBundle:Employer')
    		->find($id_employer);
    		echo "El empleador". $employerSearch->getpersonPerson()->getNames(). '<br></br>';
    		foreach ($employes as $employee) {
			    $employeeSearch = $this->getDoctrine()
		        ->getRepository('RocketSellerTwoPickBundle:Employee')
		        ->find($employee["id_employee"]);
    			if(!$employeeSearch){
    				echo "No se encontro el empleado con id: ". $employee["id_employee"] . '<br></br>' ;
    			}else{
    				echo "si se encontro el empleado: ". $employeeSearch->getpersonPerson()->getNames() . '<br></br>';
    			}
    		}		    
    }
}
