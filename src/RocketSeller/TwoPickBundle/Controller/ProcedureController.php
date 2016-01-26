<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\Country;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\Action;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\ProcedureType;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProcedureController extends Controller
{
    public function indexAction()
    {   
    	$procedures = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:RealProcedure')
		->findAll();
        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:procedures.html.twig',array('procedures'=>$procedures)
        );
    }
    public function procedureByIdAction($procedureId)
    {
    	$procedure = $this->loadClassById($procedureId,'RealProcedure');    	
    	return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',array('procedure'=>$procedure));

    }

    public function procedureAction($employerId,$idProcedureType)
    {
    	$em = $this->getDoctrine()->getManager();
    	$em2 = $this->getDoctrine()->getManager();
    	$employerSearch = $this->loadClassById($employerId,"Employer");
    	$procedureType =  $this->loadClassById($idProcedureType,"ProcedureType");
    	$employerHasEmployees = $employerSearch->getEmployerHasEmployees();
    	if ($procedureType->getName() == "Registro empleador y empleados") {
			$procedure = new RealProcedure();
			$procedure->setCreatedAt(new \DateTime());
			$procedure->setProcedureTypeProcedureType($procedureType);
			$procedure->setEmployerEmployer($employerSearch);			
			$em2->persist($procedure);

				$action = new Action();	            
	            $action->setStatus('Nuevo');
	            $action->setRealProcedureRealProcedure($procedure);	            
	            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'Revisar registro'),"ActionType"));
	            $action->setPersonPerson($employerSearch->getPersonPerson());
	            $em->persist($action);
	            $em->flush();

				$action = new Action();	            
	            $action->setStatus('Nuevo');
	            $action->setRealProcedureRealProcedure($procedure);	            
	            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'Llamar cliente'),"ActionType"));
	            $action->setPersonPerson($employerSearch->getPersonPerson());
	            $em->persist($action);
	            $em->flush();						
			foreach ($employerSearch->getEntities() as $entities) {

				$action = new Action();	            
	            $action->setStatus('Nuevo');
	            $action->setRealProcedureRealProcedure($procedure);
	            $action->setEntityEntity($entities->getEntityEntity());
	            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'Llamar entidad'),"ActionType"));
	            $action->setPersonPerson($employerSearch->getPersonPerson());
	            $em->persist($action);
	            $em->flush();

				$action = new Action();	            
	            $action->setStatus('Nuevo');
	            $action->setRealProcedureRealProcedure($procedure);
	            $action->setEntityEntity($entities->getEntityEntity());
	            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'inscripcion'),"ActionType"));
	            $action->setPersonPerson($employerSearch->getPersonPerson());
	            $em->persist($action);
	            $em->flush();
             	$procedure->addAction($action);
			}
         	foreach ($employerHasEmployees as $employerHasEmployee) {         		
					$action = new Action();	            
		            $action->setStatus('Nuevo');
		            $action->setRealProcedureRealProcedure($procedure);
		            //$action->setEntityEntity($entities->getEntityEntity());
		            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'Revisar registro'),"ActionType"));
		            $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
		            $em->persist($action);
		            $em->flush();
	             	$procedure->addAction($action);
		        foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $EmployeeHasEntity) {
 						$action = new Action();	            
			            $action->setStatus('Nuevo');
			            $action->setRealProcedureRealProcedure($procedure);
			            $action->setEntityEntity($EmployeeHasEntity->getEntityEntity());
			            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'Llamar entidad'),"ActionType"));
			            $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
			            $em->persist($action);
			            $em->flush();
		             	$procedure->addAction($action);

 						$action = new Action();	            
			            $action->setStatus('Nuevo');
			            $action->setRealProcedureRealProcedure($procedure);
			            $action->setEntityEntity($EmployeeHasEntity->getEntityEntity());
			            $action->setActionTypeActionType($this->loadClassByArray(array('name'=>'inscripcion'),"ActionType"));
			            $action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
			            $em->persist($action);
			            $em->flush();
		             	$procedure->addAction($action);
		        }
         	}
			$em2->flush();
    	}else{
    		$em2->remove($procedure);
			$em2->flush();
    	}
    	return true;
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

    public function validateAction($id_employer, $id_procedure_type, $priority, $id_user, $employees)
    {		  		
		$entityInscription = 9;
		$em = $this->getDoctrine()->getManager();	
		$employerSearch = $this->loadClassById($id_employer,"Employer");
		$userSearch = $this->loadClassById($id_user,"User");
		$procedureTypeSearch = $this->loadClassById($id_procedure_type, "ProcedureType");

		$procedure = new RealProcedure();
		$procedure->setUserUser($userSearch);
		$procedure->setCreatedAt(new \DateTime());
		$procedure->setProcedureTypeProcedureType($procedureTypeSearch);
		$procedure->setEmployerEmployer($employerSearch);
		$em->persist($procedure);
			    											
    		foreach ($employees as $employee) {
    			$entities = array();
    			foreach ($employee["entities"] as $entity) {
    				$actionTypeFound = $this->loadClassById($entity["id_action_type"],"ActionType");
		    		$employeeFound = $this->loadClassById($employee["id_employee"],"Employee");
		    		$entityFound = $this->loadClassById($entity["id_entity"],"Entity");
    				$employeeHasEntityFound = $this->loadClassByArray(array("employeeEmployee" => $employeeFound, "entityEntity"=>$entityFound),"EmployeeHasEntity");				    					    				    				    	
				    	if($employeeHasEntityFound){				    		
				    	}else{				    		
				    		if($this->loadClassByArray(array(
				    			"personPerson" => $employeeFound->getPersonPerson(),
				    			"actionTypeActionType" => $this->loadClassById(
				            	$entityInscription,"ActionType"),
				            	"entityEntity" =>$this->loadClassById($entity["id_entity"],"Entity")
				    		),"Action")){
				    			//se verifica que no hallan actions repetidos de inscripcion
				    		}else{
			    				$action = new Action();
					            $action->setUserUser($userSearch);
					            $action->setStatus('Nuevo');
					            $action->setRealProcedureRealProcedure($procedure);
					            $action->setEntityEntity($this->loadClassById($entity["id_entity"],"Entity"));
					            $action->setActionTypeActionType($this->loadClassById(
					            	$entityInscription,"ActionType"));
					            $action->setPersonPerson($employeeFound->getPersonPerson());
					            $em->persist($action);
					            $em->flush();
				             	$procedure->addAction($action);	
				    		}				    		
				    	}
				    	//se verifica que no hallan actions iguales.
				    	if($this->loadClassByArray(array(
				    			"personPerson" => $employeeFound->getPersonPerson(),
				    			"actionTypeActionType" => $actionTypeFound
				    		),"Action")){

				    	}else{
				    		$action = new Action();
				            $action->setUserUser($userSearch);
				            $action->setStatus('Nuevo');
				            $action->setRealProcedureRealProcedure($procedure);
				            $action->setEntityEntity($this->loadClassById($entity["id_entity"],"Entity"));
				            $action->setActionTypeActionType($actionTypeFound);
				            $action->setPersonPerson($employeeFound->getPersonPerson());
				            $em->persist($action);
				            $em->flush();
				            $procedure->addAction($action);
				    	}	    								
    			}
    		}
    		$em->flush();
        return $procedure;
        
    }
    public function changeVueltaStateAction($procedureId,$actionId,$status)
    {	
    	$em = $this->getDoctrine()->getManager();	
    	$action = $this->loadClassById($actionId,"Action");
    	$action->setStatus($status);
    	$em->persist($action);
    	$em->flush();
    	return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
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
    		$id_employer =21;
    		$id_procedure_type = 3;
    		$priority = 1;
    		$id_user = 36;
    		$id_contrato = 1; //preguntar para que el contrato?
    		$employees = array(
    			array(
    				'id_employee' => 4,
    				'id_contrato' => 1,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			"entities" => array(
			    				array(
				    					'id_entity' => 61,
				    					'id_action_type' => 5,
				    					),
			    				array(
				    					'id_entity' => 62,
				    					'id_action_type' => 7,
				    					)
		    				)
    				),
    			array(
    				'id_employee' => 5,
    				'id_contrato' => 2,
	    			'docs'  =>	array(
		    					'id_doc1' => 'documento 1',
		    					'id_doc2' => 2
		    					),
	    			"entities" => array(
				    				array(
					    					'id_entity' => 63,
					    					'id_action_type' => 5,
					    					),
				    				array(
					    					'id_entity' => 65,
					    					'id_action_type' => 6,
					    					)
			    				)
		    				)
    			);
    		//$procedures = $this->validateAction($id_employer, $id_procedure_type, $priority, $id_user, $employees);
	        $procedure = $this->procedureAction($id_employer, $id_procedure_type);
	        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',
            array(
            		'procedures' => $procedures
            	));

    }

}
