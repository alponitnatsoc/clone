<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Log;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProcedureController extends Controller
{
	use EmployeeMethodsTrait;


    /**
     * Funcion que carga la pagina de tramites para el backoffice
     * Muestra un acceso directo a tramites pendientes de:
     * 		Registro empleador Empleados
     * 		//otros tramites futuros
     * @param string $orderType order type for the switch
     * @param string $order order ASC or DESC
     * @param Request $request
     * @return Response /backoffice/procedures
     */
    public function indexAction($orderType, $order, Request $request)
    {
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        /** @var QueryBuilder $query */
        $query = $this->getDoctrine()->getManager()->createQueryBuilder();
        switch($orderType){
            case 'name':
                $query
                    ->add('select','p')
                    ->from('RocketSellerTwoPickBundle:RealProcedure','p')
                    ->join('RocketSellerTwoPickBundle:Employer','em','WITH','p.employerEmployer = em.idEmployer')
                    ->join('RocketSellerTwoPickBundle:Person','pe','WITH','em.personPerson = pe.idPerson')
                    ->orderBy('pe.names',$order)
                    ->addOrderBy('pe.lastName1',$order)
                    ->addOrderBy('pe.lastName2',$order);
                break;
            case 'document':
                $query
                    ->add('select','p')
                    ->from('RocketSellerTwoPickBundle:RealProcedure','p')
                    ->join('RocketSellerTwoPickBundle:Employer','em','WITH','p.employerEmployer = em.idEmployer')
                    ->join('RocketSellerTwoPickBundle:Person','pe','WITH','em.personPerson = pe.idPerson')
                    ->orderBy('pe.document',$order);
                break;
            case 'date':
                $query
                    ->add('select','p')
                    ->from('RocketSellerTwoPickBundle:RealProcedure','p')
                    ->orderBy('p.createdAt',$order);
                break;
        }
        $procedures = $query->getQuery()->getResult();
        /** @var RealProcedure $procedure */
        foreach ($procedures as $procedure){
            $actions = $procedure->getAction();
            $new = true;
            $completado = true;
            $error = false;
            $corregido = false;
            $eCon = false;
            $cContract = false;
            $comContract = true;
            $valCon = false;
            /** @var Action $action */
            foreach ($actions as $action){
                if($action->getActionTypeActionType()->getCode()!='VC'){
                    if($action->getStatus()=='Error'){
                        $error = true;
                    }
                    if($action->getStatus()=='Corregido'){
                        $corregido=true;
                    }
                    if($action->getStatus()!='Nuevo')
                        $new = false;
                    if($action->getStatus()!='Completado')
                        $completado=false;
                }else{
                    $valCon = true;
                    /** @var EmployerHasEmployee $EHE */
                    $EHE = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array(
                        'employerEmployer'=>$action->getUserUser()->getPersonPerson()->getEmployer(),
                        'employeeEmployee'=>$action->getPersonPerson()->getEmployee()
                    ));
                    /** @var Contract $contract */
                    $contract = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract')->findOneBy(array(
                        'employerHasEmployeeEmployerHasEmployee'=>$EHE,
                        'state'=>1
                    ));
                    if($contract->getDocumentDocument()){
                        if($action->getStatus()=='Error'){
                            $eCon = true;
                        }
                        if($action->getStatus()=='Corregido'){
                            $cContract=true;
                        }
                        if($action->getStatus()!='Completado'){
                            $comContract=false;
                        }
                    }
                }

            }
            if($new){
                $procedure->getEmployerEmployer()->setStatus(0);
            }elseif($error or $eCon){
                $procedure->getEmployerEmployer()->setStatus(2);
            }elseif($corregido or $cContract){
                $procedure->getEmployerEmployer()->setStatus(3);
            }elseif($completado and !$comContract){
                $procedure->getEmployerEmployer()->setStatus(4);
            }elseif($completado and $comContract){
                $procedure->getEmployerEmployer()->setStatus(5);
            }else{
                $procedure->getEmployerEmployer()->setStatus(1);
            }
            $this->getDoctrine()->getManager()->persist($procedure);
            $this->getDoctrine()->getManager()->flush();


        }

        $form = $this->createFormBuilder()
            ->add('documento','text',array('label'=>'Numero de documento:','required'=>false,'attr'=>array('class'=>'documentNumberInput','style'=>'width: 90%;margin-left: 2px;'),'label_attr'=>array('class'=>'documenNumberLabel','style'=>'margin-left: 2px;')))
            ->add('nombre','text',array('label'=>'Nombre del empleador:','required'=>false,'attr'=>array('class'=>'nameInput','style'=>'width: 90%;margin-left: 2px;'),'label_attr'=>array('class'=>'nameLabel','style'=>'margin-left: 2px;')))
            ->add('estado','choice', array('label'=>'Estado','expanded'=>false,'multiple'=>false,'placeholder' => 'Seleccionar estado','required'=>false,
                'choices' => array(
                    1 => 'Nuevo',
                    2 => 'En tramite',
                    3 => 'Error',
                    4 => 'Corregido',
                    5 => 'Terminado',
                    6 => 'Contrato Validado',
                    7 => 'Todos',
                )))
            ->add('buscar', 'submit', array('label' => 'Buscar'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $docNum = false;
            $name = false;
            $status = false;
            if ($form->get('documento')->getData()) {
                $docNum = true;
            }
            if ($form->get('nombre')->getData()) {
                $name = true;
            }
            if ($form->get('estado')->getData()){
                if($form->get('estado')->getData()!=7)
                    $status = true;
            }
            $query2 = $this->getDoctrine()->getManager()->createQueryBuilder();
            $query2
                ->add('select', 'p')
                ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                ->join('RocketSellerTwoPickBundle:Employer', 'em', 'WITH', 'p.employerEmployer = em.idEmployer')
                ->join('RocketSellerTwoPickBundle:Person', 'pe', 'WITH', 'em.personPerson = pe.idPerson');

            try {
                if($docNum){
                    $query2
                        ->where($query->expr()->orX(
                            $query->expr()->eq('pe.document', '?1'),
                            $query->expr()->like('pe.document', '?1 ')
                        ))
                        ->setParameter('1', '%' . $form->get('documento')->getData() . '%');
                }
                if($name){
                    $query2
                        ->where($query->expr()->orX(
                            $query->expr()->eq('pe.names', '?1'),
                            $query->expr()->like('pe.names', '?1 '),
                            $query->expr()->eq('pe.lastName1', '?1'),
                            $query->expr()->like('pe.lastName1', '?1 '),
                            $query->expr()->eq('pe.lastName2', '?1'),
                            $query->expr()->like('pe.lastName2', '?1 ')
                        ))
                        ->setParameter('1', '%' . $form->get('nombre')->getData() . '%');
                }
                if($status){
                    $query2
                        ->where($query->expr()->orX(
                            $query->expr()->eq('em.status', '?1')
                        ))
                        ->setParameter('1',$form->get('estado')->getData()-1);

                }
                $query2
                ->orderBy('pe.names', $order)
                ->addOrderBy('pe.lastName1', $order)
                ->addOrderBy('pe.lastName2', $order)
                ->addOrderBy('em.status','DESC');

                $procedures = $query2->getQuery()->getResult();
            } catch (Exception $e) {
                dump($e);
            }
            return $this->render('@RocketSellerTwoPick/BackOffice/procedures.html.twig',array('procedures'=>$procedures,'order'=>$order,'form' => $form->createView()));
        }

		return $this->render(
            '@RocketSellerTwoPick/BackOffice/procedures.html.twig',array('procedures'=>$procedures,'order'=>$order,'form' => $form->createView())
        );
    }

	/**
	 * Funcion que carga la informacion de un tramite por su id
	 * muestra accesos directos a:
	 * 		Revisar informacion del empleador y empledos
	 * 		validar documentos
	 * 		inscribir entidades
	 * 		validar entidades
	 *
	 * @param Integer $procedureId ID del real procedure que llega de la pagina de tramites
	 * @return Response /backoffice/procedure/{procedureId}
     */
	public function procedureByIdAction($procedureId)
    {
    	$procedure = $this->loadClassById($procedureId,'RealProcedure');
    	$employer = $procedure->getEmployerEmployer();
    	$employerHasEmployees =  $employer->getEmployerHasEmployees();
    	return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig'
			,array('procedure'=>$procedure, 'employerHasEmployees'=>$employerHasEmployees,));

    }


    /**
     * Función para verificar el estado de los tramites de un empleado
     * @param Integer $idEHE id del EmployerHasEmployee
     * @param Integer $idProc id del RealProcedure
     * @return bool true cuando estan completadas todas las acciones
     */
    public function checkActionCompletation($idEHE, $idProc)
    {
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:RealProcedure")->find($idProc);
        /** @var EmployerHasEmployee $eHE */
        $eHE = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($idEHE);
        $actions = $eHE->getEmployeeEmployee()->getPersonPerson()->getAction();
        /** @var Action $action */
        foreach ($actions as $action){
            if($action->getRealProcedureRealProcedure()==$procedure and $action->getStatus()!="Completado"){
                return false;
            }
        }
        return true;

    }

    /**
     * Funcion para cambiar el estado de backoffice de un employerHasEmployee
     * @param Integer $procedureId
     * @param Integer $idEmployerHasEmployee
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeEmployeeStatusAction($procedureId, $idEmployerHasEmployee)
    {
    	try {
            $em = $this->getDoctrine()->getManager();
            /** @var EmployerHasEmployee $employerHasEmployee */
            $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,'EmployerHasEmployee');
            //$actComplete = $this->checkActionCompletation($idEmployerHasEmployee,$procedureId);
            $employerHasEmployee->setState(4);
            $em->persist($employerHasEmployee);
            $em->flush();
            $smailer = $this->get('symplifica.mailer.twig_swift');
            $smailer->sendBackValidatedMessage($this->getUser(),$employerHasEmployee);
            $this->addFlash("employee_ended_successfully", 'Exito al terminar los tramites del empleado');
            $contracts = $employerHasEmployee->getContracts();
            /** @var Contract $contract */
            foreach ($contracts as $contract) {
                if($contract->getState()==1){
                    //we update the payroll
                    $activeP = $contract->getActivePayroll();
                    $dateNow=new DateTime();
                    if($contract->getStartDate()>$dateNow){
                        $realMonth=$contract->getStartDate()->format("m");
                        $realYear=$contract->getStartDate()->format("Y");
                        $realPeriod=intval($contract->getStartDate()->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                    }else{
                        $realMonth=$dateNow->format("m");
                        $realYear=$dateNow->format("Y");
                        $realPeriod=intval($dateNow->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                    }
                    $activeP->setMonth($realMonth);
                    $activeP->setYear($realYear);
                    $activeP->setPeriod($realPeriod);
                    $em->persist($activeP);
                    $em->flush();
                    break;
                }
            }
            return $this->redirectToRoute('show_procedure',array('procedureId'=>$procedureId));
        }catch(Exeption $e){
            $this->addFlash("employee_ended_faild", 'Ocurrio un error terminando el empleado: '. $e);
            return $this->redirectToRoute('show_procedure',array('procedureId'=>$procedureId));
        }
    }

    /**
     * Funcion que crea las acciones y los real procedure para un usuario y sus empleados
     * @param $employerId id del empleado al que se le crea el procedure
     * @param $idProcedureType tipo del procedure que debe crearse
     * @return bool 
     */
    public function procedureAction($employerId, $idProcedureType)
    {
    	$em = $this->getDoctrine()->getManager();
    	$em2 = $this->getDoctrine()->getManager();
		/** @var Employer $employerSearch */
    	$employerSearch = $this->loadClassById($employerId,"Employer");
		//OJO
        //se agrega por el momento el usuario de backoffice que sera el encargado de todos los realProcedures
        $idPerson =$employerSearch->getPersonPerson()->getIdPerson();
		// $this->loadClassByArray(array('names'=>'Back'),"Person");
		/** @var User $userSearch */
        $userSearch = $this->loadClassByArray(array('personPerson'=>$idPerson),"User");
        //fin de la busqueda del usuario de backoffice
        
        //se crea el procedure
        $procedureType =  $this->loadClassById($idProcedureType,"ProcedureType");
        $procedure = new RealProcedure();
        $procedure->setCreatedAt(new \DateTime());
        $procedure->setProcedureTypeProcedureType($procedureType);
        $employerSearch->addRealProcedure($procedure);
        $userSearch->addRealProcedure($procedure);
        $em2->persist($procedure);
        switch($idProcedureType){
			// registro empleador y empleados
            case 1:
				// se crea la accion para validar la informacion registrada por el empleador
				$action = new Action();
				$action->setStatus('Nuevo');
				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VER'),"ActionType"));
                $procedure->addAction($action);
                $employerSearch->getPersonPerson()->addAction($action);
                $userSearch->addAction($action);
				$em->persist($action);

				// se crea la accion para validar documentos del empleador
				$action = new Action();
				$action->setStatus('Nuevo');
				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
                $procedure->addAction($action);
                $employerSearch->getPersonPerson()->addAction($action);
                $userSearch->addAction($action);
				$em->persist($action);

				$action = new Action();
				$action->setStatus('Nuevo');
				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VM'),"ActionType"));
                $procedure->addAction($action);
                $employerSearch->getPersonPerson()->addAction($action);
                $userSearch->addAction($action);
				$em->persist($action);

				// se obtienen las entidades del empleador
				/** @var EmployerHasEntity $entities */
				foreach ($employerSearch->getEntities() as $entities) {
					if ($entities->getState()>=0) {
						//se crea la accion para la entidad del empleador
						$action = new Action();
						$action->setStatus('Nuevo');
                        $procedure->addAction($action);
                        $userSearch->addAction($action);
                        $employerSearch->getPersonPerson()->addAction($action);
						$action->setEmployerEntity($entities);
                        //si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
                        if ($entities->getState()===0){
                            $action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
                            $em->persist($action);
                            //si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
                        }elseif($entities->getState()===1){
                            $action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
                            $em->persist($action);
                        }
                    }

				}
				//se obtienen todos los emleados del empleador
				/** @var EmployerHasEmployee $employerHasEmployee */
				foreach ($employerSearch->getEmployerHasEmployees() as $employerHasEmployee) {
					if ($employerHasEmployee->getState()>=2){
						//si el empleado no tiene acciones creadas es decir no es empleado de algun otro empleador
						if ($employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getAction()->isEmpty()){
							//se crea la accion para validar la informacion del empleado
							$action = new Action();
							$action->setStatus('Nuevo');
							$procedure->addAction($action);
                            $userSearch->addAction($action);
                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEE'),"ActionType"));
							$em->persist($action);

							//se crea la accion para validar documentos y generar contrato
							$action = new Action();
							$action->setStatus('Nuevo');
                            $procedure->addAction($action);
                            $userSearch->addAction($action);
                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
							$em->persist($action);

							//se obtienen las entidades del empleado
							/** @var EmployeeHasEntity $employeeHasEntity */
							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
								if ($employeeHasEntity->getState()>=0) {
									//se crea a accion para las entidades del empleado
									$action = new Action();
									$action->setStatus('Nuevo');
                                    $procedure->addAction($action);
                                    $userSearch->addAction($action);
                                    $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
									$action->setEmployeeEntity($employeeHasEntity);

									//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
									if ($employeeHasEntity->getState()===0){
										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
										$em->persist($action);
										//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
									}elseif($employeeHasEntity->getState()===1){
										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
										$em->persist($action);
									}
								}
							}
							//si el empleado es antiguo (ya inicio labores) se crea el tramite de validar contrato
							if($employerHasEmployee->getLegalFF()==1){
								$actionV = new Action();
								$actionV->setStatus('Nuevo');
                                $procedure->addAction($action);
                                $userSearch->addAction($action);
                                $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
								$actionV->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
								$em->persist($actionV);
								//se agrega la accion al procedimiento
								$action->getRealProcedureRealProcedure()->addAction($actionV);
							}
                        //si el empleado ya es empleado de alguien mas solo se validan las entidades ya existentes
						}else{
							//se crea la accion de informacion del empleado validada
							$action = new Action();
							$action->setStatus('Completado');
                            $procedure->addAction($action);
                            $userSearch->addAction($action);
                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEE'),"ActionType"));
							$em->persist($action);

							//se crea la accion para validar documentos y generar contrato
							$action = new Action();
							$action->setStatus('Nuevo');
                            $procedure->addAction($action);
                            $userSearch->addAction($action);
                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
							$em->persist($action);

                            //si el empleado ya es empleado de alguien se crean los tramites ya completados
							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
								if ($employeeHasEntity->getState()>=0) {
									//se crea a accion para las entidades del empleado
									$action = new Action();
									$action->setStatus('Completado');
                                    $procedure->addAction($action);
                                    $userSearch->addAction($action);
                                    $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
									$action->setEmployeeEntity($employeeHasEntity);

									//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
									if ($employeeHasEntity->getState()===0){
										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
										$em->persist($action);
										//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
									}elseif($employeeHasEntity->getState()===1){
										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
										$em->persist($action);
									}
									//se agrega la accion al procedimiento
									$procedure->addAction($action);
								}
							}
							//si el empleado es antiguo (ya inicio labores) se crea el tramite de validar contrato
							if($employerHasEmployee->getLegalFF()==1){
								$action = new Action();
								$action->setStatus('Nuevo');
                                $procedure->addAction($action);
                                $userSearch->addAction($action);
                                $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
								$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
								$em->persist($action);
							}
						}
					}
				}
                $em->flush();
				$em2->flush();
                break;
            case 2:

                break;
            case 3:
                break;
			// registro empleado
            case 4:
				/** @var EmployerHasEmployee $employerHasEmployee */
				foreach ($employerSearch->getEmployerHasEmployees() as $employerHasEmployee) {
					if ($employerHasEmployee->getState()>2){
						//si el empleado no tiene acciones creadas es decir no es empleado de algun otro empleador
						if ($employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getAction()->isEmpty()) {
							//se crea la accion para validar la informacion del empleado
							$action = new Action();
							$action->setStatus('Nuevo');
							$action->setRealProcedureRealProcedure($procedure);
							$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'VEE'), "ActionType"));
							$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
							$action->setUserUser($userSearch);
							$em->persist($action);
							$em->flush();
							//se agrega la accion al procedimiento
							$procedure->addAction($action);

							$action = new Action();
							$action->setStatus('Nuevo');
							$action->setRealProcedureRealProcedure($procedure);
							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
							$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
							$action->setUserUser($userSearch);
							$em->persist($action);
							$em->flush();
							//se agrega la accion al procedimiento
							$procedure->addAction($action);

							//se obtienen las entidades del empleado
							/** @var EmployeeHasEntity $employeeHasEntity */
							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
								//se crea a accion para las entidades del empleado
								$action = new Action();
								$action->setStatus('Nuevo');
								$action->setRealProcedureRealProcedure($procedure);
								$action->setEntityEntity($employeeHasEntity->getEntityEntity());
								//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
								if ($employeeHasEntity->getState() === 0) {
									$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'VEN'), "ActionType"));
									$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
									$action->setUserUser($userSearch);
									$em->persist($action);
									$em->flush();
									//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
								} elseif ($employeeHasEntity->getState() === 1) {
									$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'IN'), "ActionType"));
									$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
									$action->setUserUser($userSearch);
									$em->persist($action);
									$em->flush();
								}
								//se agrega la accion al procedimiento
								$procedure->addAction($action);
							}
						}
					}

				}
                break;
            default:
				$em2->remove($procedure);
				$em2->flush();
                break;
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


    /**
     * Funcion que cambia el estado de una accion y crea notificaciones al empleador
     * @param Integer $procedureId
     * @param Integer $actionId
     * @param String $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeVueltaStateAction($procedureId, $actionId, $status)
    {

    	$em = $this->getDoctrine()->getManager();
		/** @var Action $action */
		$action = $this->loadClassById($actionId,"Action");
		//adding verification to check if the actions is validate documents employee
		if($action->getActionTypeActionType()->getCode()=="VDC" and $status!='Error'){
			$employee=$action->getPersonPerson()->getEmployee();
			if($employee!=null){
				/** @var User $user */
				$user=$action->getRealProcedureRealProcedure()->getUserUser();
				$ehes=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
				$realEhe=null;
				/** @var EmployerHasEmployee $eHE */
				foreach ($ehes as $eHE) {
					if($eHE->getEmployeeEmployee()->getIdEmployee()==$employee->getIdEmployee()){
						$realEhe=$eHE;
					}
				}
				if($realEhe!=null){
					$realContract=null;
					$contracts=$realEhe->getContracts();
					/** @var Contract $contract */
					foreach ($contracts as $contract) {
						if($contract->getState()==1){
							$realContract=$contract;
							break;
						}
					}
					if($contract!=null){
						//first create the notification
						$utils = $this->get('app.symplifica_utils');
						$dAction="Bajar";
						$dUrl = $this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
						$msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$realEhe->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $realEhe->getEmployeeEmployee()->getPersonPerson()->getLastName1());
						$nAction="Subir";
						/** @var DocumentType $documentType */
						$documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array("docCode"=>'CTR'));
						$url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId' =>$contract->getIdContract(), 'docCode' =>'CTR'));
						$notifications=$realEhe->getEmployerEmployer()->getPersonPerson()->getNotifications();
						$urlToFind=$this->generateUrl("view_document_contract_state", array("idEHE"=>$realEhe->getIdEmployerHasEmployee()));
						//searching the notification of the state of the contract to replace its content
						$notification=null;
						/** @var Notification $not */
						foreach ($notifications as $not ) {
							if($not->getRelatedLink()==$urlToFind){
								$notification=$not;
							}
						}
						if($notification==null)
							$notification = new Notification();

						//se envia emai de validacion 3 días
                        $smailer = $this->get('symplifica.mailer.twig_swift');
                        $smailer->sendDiasHabilesMessage($user,$realEhe);
						//se crea la accion de validar contrato si no habia iniciado labores
						if($realEhe->getLegalFF()!=1){
							$actionV = new Action();
							$actionV->setStatus('Nuevo');
							$actionV->setRealProcedureRealProcedure($action->getRealProcedureRealProcedure());
							$actionV->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
							$actionV->setPersonPerson($realEhe->getEmployeeEmployee()->getPersonPerson());
							$actionV->setUserUser($user);
							$em->persist($actionV);
							$em->flush();
							//se agrega la accion al procedimiento
							$action->getRealProcedureRealProcedure()->addAction($actionV);
						}


						//then check if changing the start date is necessary
						if($realEhe->getLegalFF()==0){
							//ademas agrego la notificacion

							$notification->setPersonPerson($user->getPersonPerson());
							$notification->setStatus(1);
							$notification->setDocumentTypeDocumentType($documentType);
							$notification->setType('alert');
							$notification->setDescription($msj);
							$notification->setRelatedLink($url);
							$notification->setAccion($nAction);
							$notification->setDownloadAction($dAction);
							$notification->setDownloadLink($dUrl);
							$em->persist($notification);

							$todayPlus = new DateTime();
							$request = $this->container->get('request');
							$request->setMethod("GET");
							$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysToDate',array('dateStart'=>$todayPlus->format("Y-m-d"),'days'=>3), array('_format' => 'json'));
							if ($insertionAnswer->getStatusCode() != 200) {
								return false;
							}
							$permittedDate=new DateTime(json_decode($insertionAnswer->getContent(),true)['date']);
							if($contract->getStartDate()<$permittedDate){
								$contract->setStartDate($permittedDate);
								$em->persist($contract);
							}
						}
						$em->flush();
					}else{
						$notification2 = new Notification();
						$notification2->setPersonPerson($user->getPersonPerson());
						$notification2->setStatus(1);
						$notification2->setDocumentTypeDocumentType(null);
						$notification2->setType('alert');
						$notification2->setDescription("Tu empleado ".$realEhe->getEmployerEmployer()->getPersonPerson()->getFullName()." a sido correctamente validado");
						$notification2->setRelatedLink("/notifications/change/".$notification2->getId()."/0");
						$notification2->setAccion("Cerrar");
						$notification2->setDownloadAction(null);
						$notification2->setDownloadLink(null);
						$em->persist($notification2);
					}
					$em->flush();
				}

			}
		}
    	$action->setStatus($status);
    	$em->persist($action);
    	$em->flush();
    	return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }
    public function changeErrorStatusAction($procedureId,$actionError,$status)
    {	
    	
    	$em = $this->getDoctrine()->getManager();	
    	$actionError = $this->loadClassById($actionError,"ActionError");
    	$actionError->setStatus($status);
    	$em->persist($actionError);
    	$em->flush();
    	if ($status == "Corregido") {
    		$action = $this->loadClassByArray(array('actionErrorActionError'=> $actionError),'Action');
    		$action->setStatus("Completado");
    		$em->persist($action);
    		$em->flush();	    		
    	}
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
    		$id_employer =26;
    		$id_procedure_type = 3;
    		$priority = 1;
    		$id_user = 1;
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
	        return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',
            array(
            		'procedures' => $procedures
            	));

    }

}
