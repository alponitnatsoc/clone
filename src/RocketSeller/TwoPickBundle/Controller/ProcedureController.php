<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\Country;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
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
     * @param  $id $id_user 		   usuario que va a realizar el tramite
     * @param  Array() $employees      arreglo de empleados con:
     *                               ->id_employee
     *                          	 ->id_contrato
     *                               ->Array docs
     *                               ->Array entidades
     *                               		->id_employee_has_entity
     *                                 		->id_action_type
     *                                 		->sort_order
     * @return integer $priority       prioridad del empleador (vip, regular)
     */
    public function validateAction($id_employer, $id_procedure_type, $priority, $id_user, $employees, $id_action_type)
    {
    		$employerSearch = $this->loadClassById($id_employer,"Employer");
    		$userSearch = $this->loadClassById($id_user,"User");
    		$actionTypeSearch = $this->loadClassById($id_action_type,"ActionType");
    		$em = $this->getDoctrine()->getManager();
    		foreach ($employees as $employee) {
    			foreach ($employee["entities"] as $entity) {
    				$actionTypeFound = $this->loadClassById($entity["id_action_type"],"ActionType");
		    		$employeeFound = $this->loadClassById($employee["id_employee"],"Employee");
    				$employeeHasEntityFound = $this->loadClassByArray(array("employeeEmployee" => $employeeFound),"EmployeeHasEntity");
				    	if($employeeHasEntityFound && $employeeHasEntityFound->getEntityEntity()->getEntityTypeEntityType()->getIdEntityType() == $entity["id_entity"]){
				    		echo "el empleado con id:". $employee["id_employee"]." esta afiliado a la entidad: ".$employeeHasEntityFound->getEntityEntity()->getEntityTypeEntityType()->getName().  "<br></br>";
				    	}else{
		    				$action = new Action();
				            $action->setUserUser($userSearch);
				            $action->setActionTypeActionType($actionTypeSearch);
				            $action->setPersonPerson($employeeFound->getPersonPerson());
				            $em->persist($action);
				            $em->flush();
				            echo "el empleado con el id: " .$employee["id_employee"]. " no esta afiliado a la entidad con id: ". $entity["id_entity"]. "<br></br>";
				    	}
	    				$action = new Action();
			            $action->setUserUser($userSearch);
			            $action->setActionTypeActionType($actionTypeFound);
			            $action->setPersonPerson($employeeFound->getPersonPerson());
			            $em->persist($action);
			            $em->flush();
    			}
    		}
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
     * metodo que llama el metodo validate con las variables inicializadas
     * tambien se describe la estructura que debe de tener el array de employees
     */
    public function testValidateAction()
    {
    		$id_employer =1;
    		$id_procedure_type = "Inscripcion";
    		$priority = 1;
    		$id_user = 1;
    		$id_contrato = 1; //preguntar para que el contrato?
    		$id_action_type = 1; //inscripción
    		$employees = array(
    			array(
    				'id_employee' => 1,
    				'id_contrato' => 1,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			"entities" => array(
			    				array(
				    					'id_entity' => 1,
				    					'id_action_type' => 2,
				    					),
			    				array(
				    					'id_entity' => 2,
				    					'id_action_type' => 2,
				    					)
		    				)
    				),
    			array(
    				'id_employee' => 2,
    				'id_contrato' => 2,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			"entities" => array(
				    				array(
					    					'id_entity' => 1,
					    					'id_action_type' => 2,
					    					),
				    				array(
					    					'id_entity' => 2,
					    					'id_action_type' => 2,
					    					)
			    				)
		    				)
    			);
    		$this->validateAction($id_employer, $id_procedure_type, $priority, $id_user, $employees, $id_action_type);

    }

}
