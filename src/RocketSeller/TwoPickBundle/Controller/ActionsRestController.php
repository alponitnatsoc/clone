<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\ProcedureType;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\StatusTypes;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Component\Config\Definition\Exception\Exception;

class ActionsRestController extends FOSRestController
{
    use EmployeeMethodsTrait;

    /**
     * recreate actionTypes table
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "recreates actionTypes in the DB",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postRecreateActionTypesAction(){
        $response = "Comienza<br>";
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:Action")->findAll() as $action){
            $em->remove($action);
        }
        $em->flush();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findAll() as $actionType){
            $em->remove($actionType);
        }
        $em->flush();
        $response .= "ActionTypes Borrados<br>";
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Creating ActionTypes                             ║
         * ╚══════════════════════════════════════════════════╝
         */
        $actionType = new ActionType('Validar Informacion del Empleador','VER');
        $actionType->setIdActionType(1);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Documento Empleador",'VDDE');
        $actionType->setIdActionType(2);
        $em->persist($actionType);
        $actionType = new ActionType("Validar RUT Empleador",'VRTE');
        $actionType->setIdActionType(3);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Registro Civil Empleador",'VRCE');
        $actionType->setIdActionType(4);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Mandato",'VM');
        $actionType->setIdActionType(5);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Entidad Empleador",'VENE');
        $actionType->setIdActionType(6);
        $em->persist($actionType);
        $actionType = new ActionType("Inscripción Empleador",'INE');
        $actionType->setIdActionType(7);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Información del Empleado",'VEE');
        $actionType->setIdActionType(8);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Documento Empleado",'VDD');
        $actionType->setIdActionType(9);
        $em->persist($actionType);
        $actionType = new ActionType("Validar RUT Empleado",'VRT');
        $actionType->setIdActionType(10);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Carta Autorización Empleado",'VCAT');
        $actionType->setIdActionType(11);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Registro Civil Empleado",'VRC');
        $actionType->setIdActionType(12);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Entidad Empleado",'VEN');
        $actionType->setIdActionType(13);
        $em->persist($actionType);
        $actionType = new ActionType("Inscripcion Empleado",'IN');
        $actionType->setIdActionType(14);
        $em->persist($actionType);
        $actionType = new ActionType("Inscripcion Beneficiario Empleado",'IBN');
        $actionType->setIdActionType(15);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Contrato Empleado",'VC');
        $actionType->setIdActionType(16);
        $em->persist($actionType);
        $actionType = new ActionType("LLevar Documentos a Entidad",'LDE');
        $actionType->setIdActionType(17);
        $em->persist($actionType);
        $actionType = new ActionType("Subir Radicados Entidad",'SDE');
        $actionType->setIdActionType(18);
        $em->persist($actionType);
        $actionType = new ActionType("Llamar Cliente",'CCL');
        $actionType->setIdActionType(19);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Documentos Empleador",'VDCE');
        $actionType->setIdActionType(20);
        $em->persist($actionType);
        $actionType = new ActionType("Validar Documentos Empleado",'VDC');
        $actionType->setIdActionType(21);
        $em->persist($actionType);
        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($actionType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "ActionTypes Creados<br>";
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * recreate ProcedureTypes in the DB
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "recreates ProcedureTypes in the DB",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postRecreateProceduresTypesAction(){
        $response = "Comienza<br>";
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll() as $procedure){
            $em->remove($procedure);
        }
        $em->flush();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findAll() as $procedureType){
            $em->remove($procedureType);
        }
        $em->flush();
        $response .= "ProcedureTypes Borrados<br>";
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Creating ProcedureTypes                          ║
         * ╚══════════════════════════════════════════════════╝
         */
        $procedureType = new ProcedureType("Registro empleador y empleados",'REE');
        $procedureType->setIdProcedureType(1);
        $em->persist($procedureType);
        $procedureType = new ProcedureType("Pago de pila",'PPL');
        $procedureType->setIdProcedureType(2);
        $em->persist($procedureType);
        $procedureType = new ProcedureType("Validar Contrato",'VAC');
        $procedureType->setIdProcedureType(3);
        $em->persist($procedureType);
        $procedureType = new ProcedureType("Subir Planillas",'SPL');
        $procedureType->setIdProcedureType(4);
        $em->persist($procedureType);
        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($procedureType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "ProcedureTypes Creados<br>";
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * create or recreate StatusTypes in the DB
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "creates or recreates StatusTypes in the DB",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postRecreateStatusTypesAction(){
        $response = "Comienza<br>";
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findAll() as $statusType){
            $em->remove($statusType);
        }
        $response .= "StatusTypes Borrados<br>";
        $em->flush();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findAll() as $documentStatusType){
            $em->remove($documentStatusType);
        }
        $response .= "DocumentStatusTypes Borrados<br>";
        $em->flush();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ContractDocumentStatusType")->findAll() as $contractDocumentStatusType){
            $em->remove($contractDocumentStatusType);
        }
        $response .= "ContractDocumentStatusTypes Borrados<br>";
        $em->flush();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Creating StatusTypes                             ║
         * ╚══════════════════════════════════════════════════╝
         */
        $response .= "Creando StatusTypes<br>";

        $statusType = new StatusTypes( "Desabilitado" , "DIS" );
        $statusType->setIdStatusType(1);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Documentos pendientes" , "DCPE" );
        $statusType->setIdStatusType(2);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Nuevo" , "NEW" );
        $statusType->setIdStatusType(3);
        $em->persist( $statusType );

        $statusType = new StatusTypes( "En tramite" , "STRT" );
        $statusType->setIdStatusType(4);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Error" , "ERRO" );
        $statusType->setIdStatusType(5);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Corregido" , "CORT" );
        $statusType->setIdStatusType(6);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Terminado" , "FIN" );
        $statusType->setIdStatusType(7);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Contrato pendiente" , "CTPE" );
        $statusType->setIdStatusType(8);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Contrato Validado" , "CTVA" );
        $statusType->setIdStatusType(9);
        $em->persist($statusType);

        $statusType = new StatusTypes( "Consultar" , "CON" );
        $statusType->setIdStatusType(10);
        $em->persist($statusType);

        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($statusType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "StatusTypes Creados<br>";

        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Creating DocumentStatusTypes                     ║
         * ╚══════════════════════════════════════════════════╝
         */
        $response .= "Creando DocumentStatusTypes<br>";

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("All docs pending");
        $documentStatusType->setDocumentStatusCode("ALLDCP");
        $documentStatusType->setIdDocumentStatusType(1);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Employee docs pending");
        $documentStatusType->setDocumentStatusCode("EEDCPE");
        $documentStatusType->setIdDocumentStatusType(2);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Employer docs pending");
        $documentStatusType->setDocumentStatusCode("ERDCPE");
        $documentStatusType->setIdDocumentStatusType(3);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Message all docs ready");
        $documentStatusType->setDocumentStatusCode("ALLDCR");
        $documentStatusType->setIdDocumentStatusType(4);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("All docs in validation");
        $documentStatusType->setDocumentStatusCode("ALDCIV");
        $documentStatusType->setIdDocumentStatusType(5);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Only employer docs validated");
        $documentStatusType->setDocumentStatusCode("ERDCVA");
        $documentStatusType->setIdDocumentStatusType(6);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Only employee docs validated");
        $documentStatusType->setDocumentStatusCode("EEDCVA");
        $documentStatusType->setIdDocumentStatusType(7);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Employer docs validated but employee docs had errors");
        $documentStatusType->setDocumentStatusCode("ERVEEE");
        $documentStatusType->setIdDocumentStatusType(8);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Employee docs validated but employer docs had errors");
        $documentStatusType->setDocumentStatusCode("EEVERE");
        $documentStatusType->setIdDocumentStatusType(9);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Only employer docs had errors");
        $documentStatusType->setDocumentStatusCode("ERDCE");
        $documentStatusType->setIdDocumentStatusType(10);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Only employee docs had errors");
        $documentStatusType->setDocumentStatusCode("EEDCE");
        $documentStatusType->setIdDocumentStatusType(11);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("All docs had errors");
        $documentStatusType->setDocumentStatusCode("ALLDCE");
        $documentStatusType->setIdDocumentStatusType(12);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Docs had errors message");
        $documentStatusType->setDocumentStatusCode("DCERRM");
        $documentStatusType->setIdDocumentStatusType(13);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("All docs validated message");
        $documentStatusType->setDocumentStatusCode("ALDCVM");
        $documentStatusType->setIdDocumentStatusType(14);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("All docs validated");
        $documentStatusType->setDocumentStatusCode("ALDCVA");
        $documentStatusType->setIdDocumentStatusType(15);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Backoffice finished message");
        $documentStatusType->setDocumentStatusCode("BOFFMS");
        $documentStatusType->setIdDocumentStatusType(16);
        $em->persist($documentStatusType);

        $documentStatusType= new DocumentStatusType();
        $documentStatusType->setName("Backoffice finished");
        $documentStatusType->setDocumentStatusCode("BOFFFF");
        $documentStatusType->setIdDocumentStatusType(17);
        $em->persist($documentStatusType);

        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($documentStatusType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "DocumentStatusTypes Creados<br>";

        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Creating ContractDocumentStatusTypes             ║
         * ╚══════════════════════════════════════════════════╝
         */
        $response .= "creando ContractDocumentStatusTypes<br>";

        $contractDocumentStatusType= new ContractDocumentStatusType();
        $contractDocumentStatusType->setName("Contract is pending");
        $contractDocumentStatusType->setContractDocumentStatusCode("CTPE");
        $contractDocumentStatusType->setIdContractDocumentStatusType(1);
        $em->persist($contractDocumentStatusType);

        $contractDocumentStatusType= new ContractDocumentStatusType();
        $contractDocumentStatusType->setName("Contract was uploaded");
        $contractDocumentStatusType->setContractDocumentStatusCode("CTUP");
        $contractDocumentStatusType->setIdContractDocumentStatusType(2);
        $em->persist($contractDocumentStatusType);

        $contractDocumentStatusType= new ContractDocumentStatusType();
        $contractDocumentStatusType->setName("Contract has errors");
        $contractDocumentStatusType->setContractDocumentStatusCode("CTER");
        $contractDocumentStatusType->setIdContractDocumentStatusType(3);
        $em->persist($contractDocumentStatusType);

        $contractDocumentStatusType= new ContractDocumentStatusType();
        $contractDocumentStatusType->setName("Contract Validated");
        $contractDocumentStatusType->setContractDocumentStatusCode("CTVA");
        $contractDocumentStatusType->setIdContractDocumentStatusType(4);
        $em->persist($contractDocumentStatusType);

        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($contractDocumentStatusType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "ContractDocumentStatusTypes Creados<br>";

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Fast correction to de DB actions
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Fast correction to de DB actions",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postFastCorrectAction(){
        $response='';
        $em=$this->getDoctrine()->getManager();
        $users = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        /** @var User $user */
        foreach ($users as $user){
            $actions = $user->getActionsByType($this->getActionByType('VEN'));
            $response .= "ACTIONS ENCONTRADAS: ".$actions->count()."<br>";
            /** @var RealProcedure $procedure */
            $procedure = null;
            if($actions->count()>0){
                if($procedure == null)
                    $procedure = $actions->first()->getRealProcedureRealProcedure();
                /** @var Action $action */
                foreach ($actions as $action) {
                    $em->remove($action);
                }
                $em->flush();
            }
            $actions = $user->getActionsByType($this->getActionByType('IN'));
            $response .= "ACTIONS ENCONTRADAS: ".$actions->count()."<br>";
            if($actions->count()>0) {
                if($procedure == null)
                    $procedure = $actions->first()->getRealProcedureRealProcedure();
                foreach ($actions as $action) {
                    $em->remove($action);
                }
                $em->flush();
            }
            if($procedure!= null){
                $response .= "EXISTE.<br>";
                if($user->getStatus()>=2){
                    if($user->getPersonPerson()->getEmployer()->getEntities()->count()>0){
                        foreach ($user->getPersonPerson()->getEmployer()->getEntities() as $entity) {
                            if ($entity->getState() >= 0) {
                                $action = new Action();
                                $action->setStatus('Nuevo');
                                $procedure->addAction($action);
                                $user->addAction($action);
                                $user->getPersonPerson()->addAction($action);
                                $action->setEmployerEntity($entity);
                                if ($entity->getState() === 0) {
                                    $action->setActionTypeActionType($this->getActionByType('VEN'));
                                } elseif ($entity->getState() === 1) {
                                    $action->setActionTypeActionType($this->getActionByType('IN'));
                                }
                                $em->persist($action);
                            }
                        }
                        $em->flush();
                        /** @var EmployerHasEmployee $employerHasEmployee */
                        foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee) {
                            if($employerHasEmployee->getEmployeeEmployee()->getEntities()->count()>0 and $employerHasEmployee->getState()>2){
                                foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $entity) {
                                    if ($entity->getState() >= 0) {
                                        $action = new Action();
                                        $action->setStatus('Completado');
                                        $procedure->addAction($action);
                                        $user->addAction($action);
                                        $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
                                        $action->setEmployeeEntity($entity);
                                        if ($entity->getState() === 0) {
                                            $action->setActionTypeActionType($this->getActionByType('VEN'));
                                        } elseif ($entity->getState() === 1) {
                                            $action->setActionTypeActionType($this->getActionByType('IN'));
                                        }
                                        $em->persist($action);
                                    }
                                }
                                $em->flush();
                            }
                        }
                    }
                }

            }
        }
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * recreate Procedures and actions in the DB
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "recreates Procedures and actions in the DB",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postRecreateProceduresAction(){
        $response = "<br>".'- - - COMIENZA LA FUNCION - - -'."<br>";
        $em = $this->getDoctrine()->getManager();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Deleting previous Actions                        ║
         * ╚══════════════════════════════════════════════════╝
         */
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:Action")->findAll() as $action){
            $em->remove($action);
        }
        $em->flush();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Deleting previous Procedures                     ║
         * ╚══════════════════════════════════════════════════╝
         */
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll() as $procedure){
            $em->remove($procedure);
        }
        $em->flush();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Deleting previous ActionErrors                   ║
         * ╚══════════════════════════════════════════════════╝
         */
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ActionError")->findAll() as $errors){
            $em->remove($errors);
        }
        $em->flush();
        $response.= "- - - ELEMENTOS ELIMINADOS - - -<br><br>";

        $users = $em->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        $count = 1;
        $pCount = 1;
        $aCount = 1;
        /** @var User $user */
        foreach ($users as $user) {
            try {
                $response .= "- - - USUARIO ".$count.": " . $user->getPersonPerson()->getFullName() . " - - -<br>";
                if ($user->getStatus() >= 2) {
                    /**
                     * ╔══════════════════════════════════════════════════╗
                     * ║ creating user RealProcedures                     ║
                     * ╚══════════════════════════════════════════════════╝
                     */
                    $response .= "- - - CREANDO TRAMITES - - -<br>";
                    $count++;
                    if ($user->getRealProcedure()->isEmpty()) {
                        $result = $this->createProcedure($user, $pCount, $aCount);
                        $response .= $result['response'];
                        $pCount = $result['pCount'];
                        $aCount = $result['aCount'];
                    } else {
                        $response .= "- - - YA EXISTEN TRAMITES - - -<br><br>";
                    }
                } else {
                    $response .= "- - - NO HA TERMINADO 3 DE 3 - - -<br><br>";
                }
            } catch (Exception $e) {
                $response .= "Error en el usuario: " . $user->getPersonPerson()->getFullName()
                    . "<br><strong>Error: </strong>" . $e->getCode() . " " . $e->getMessage() . "<br>";
            }
        }
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createProcedure                              ║
     * ║ Function that creates all the realProcedures of the   ║
     * ║ user pass by parameter                                ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param integer $pCount optional                      ║
     * ║  @param integer $aCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createProcedure(User $user,$pCount=null,$aCount=null){
        $response = '';
        if($user->getPersonPerson()->getEmployer()) {
            $employer = $user->getPersonPerson()->getEmployer();
            if(!$employer->getEntities()->isEmpty()) {
                $em = $this->getDoctrine()->getManager();
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Procedure Register Employer Employees            ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $procedure = new RealProcedure();
                $procedure->setProcedureTypeProcedureType($this->getProcedureByType('REE'));//setting the procedure type
                $employer->addRealProcedure($procedure);//adding the realProcedure to the employer
                $procedure->setCreatedAt($user->getLastPayDate());//setting the createAt Date
                $procedure->setProcedureStatus($this->getStatusByType('DIS'));//setting the initial status Disable
                $procedure->setBackOfficeDate($user->getLastPayDate());//setting the backofice start Date
                $procedure->setPriority(0);//setting the default priority
                $user->addRealProcedure($procedure);//adding the realProcedure to the user
                if ($pCount != null) {//if theres an id parameter for the procedure
                    $procedure->setIdProcedure($pCount);//setting the id to the realProcedure
                    $em->persist($procedure);//persisting the procedure
                    $metadata = $em->getClassMetadata(get_class($procedure));//line of code necessaryy to force an id
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                    $pCount++;//increment id realProcedure count
                } else {
                    $em->persist($procedure);//persisting the procedure
                }
                $response .= "- - - PROCEDURE REGISTRO CREADO - - -<br>";
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Employer actions begin                           ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                if ($user->getPersonPerson()->getEmployee()) {//if user is also a employee
                    $result = $this->createActionsEmployerEmployee($user, $procedure, $aCount);//creating actions with function for employer also employee
                    $response .= $result['response'];
                    $aCount = $result['aCount'];
                } else {
                    $result = $this->createActionsEmployer($user, $procedure, $aCount);//creating actions with function for employer only
                    $response .= $result['response'];
                    $aCount = $result['aCount'];
                }

                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Employee Actions Begin                           ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $atLeastOneFinished = false;//setting flag for not employees finished
                /** @var EmployerHasEmployee $employerHasEmployee */
                foreach ($employer->getEmployerHasEmployees() as $employerHasEmployee) {//crossing employerHasEmployee to create procedures for each one
                    if ($employerHasEmployee->getState() < 2) {//if employee is not confirmed or disabled
                        $response .= "- - - - EMPLEADO INACTIVO ".$employerHasEmployee->getIdEmployerHasEmployee()." - - - -<br>";
                    } else {
                        $ePerson = $employerHasEmployee->getEmployerEmployer()->getPersonPerson();
                        $eePerson = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson();
                        if ($ePerson == $eePerson) {//if employer is same employee employerHasEmployee error
                            $response .= '- - - - ERROR: EMPLEADO QUE ES SU EMPLEADOR - - - -<br>';
                            $employerHasEmployee->setState(-2);//setting the state of error
                            $em->persist($employerHasEmployee);//persisting the employerHasEmployee
                        } elseif ($eePerson->getAction()->count() > 0) {//if employee has actions its employee of someone else or employer of someone else
                            $ehes = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findBy(array('employeeEmployee'=>$eePerson->getEmployee()));
                            $isEmployeeOf = 0;
                            foreach ($ehes as $ehe){
                                $isEmployeeOf++;
                            }
                            if ($eePerson->getEmployer() and $isEmployeeOf == 1) {//employee is also a employer
                                $response .= "- - - - EMPLEADO QUE TAMBIEN ES EMPLEADOR - - - -<br>";
                            }elseif($eePerson->getEmployer() and $isEmployeeOf >1){//employee is also a employer and is employee of more than one employer
                                $response .= "- - - - EMPLEADO EMPLEADOR CON VARIOS EMPLEADORES - - - -<br>";
                            }elseif($isEmployeeOf > 1){//employee has more employers
                                $response .= "- - - - EMPLEADO QUE YA ES EMPLEADO - - - -<br>";
                            }
                        } else {
                            $result = $this->createActionsEmployee($user, $procedure,$employerHasEmployee, $atLeastOneFinished, $aCount);//creating actions with function for employer also employee
                            $response .= $result['response'];
                            $aCount = $result['aCount'];
                            $atLeastOneFinished = $result['atLeast'];
                        }
                    }
                }
                $em->flush();
                $response .= "- - - TRAMITES CREADOS - - -<br><br>";
            }else{
                $response .= "ERROR: EL EMPLEADOR NO TIENE ENTIDADES . . .<br><br>";
            }
        }else{
            $response .="ERROR: NO SE ENCONTRO EL EMPELADOR<br><br>";
        }
        return array('pCount'=>$pCount,'aCount'=>$aCount,'response'=>$response);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createActionsEmployerEmployee                ║
     * ║ Function that creates all the actions for the user    ║
     * ║ user must be an employer and employee                 ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param integer $aCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployerEmployee(User $user,RealProcedure $procedure,$aCount=null){
        $response = '- - - EMPLEADOR QUE ES EMPLEADO - - -<br>';
        $response .= '- - - CREANDO ACCIONES EMPLEADOR - - - <br>';
        /** @var Person $person */
        $person = $user->getPersonPerson();//getting the person from the user
        $employer = $person->getEmployer();//getting the employer
        $employee = $person->getEmployee();//getting the employee
        $em = $this->getDoctrine()->getManager();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer info                    ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
        $user->addAction($action);//adding the action to the user
        $action->setActionTypeActionType($this->getActionByType('VER'));//setting the actionType validate employer info
        if($person->getActionsByActionType($this->getActionByType('VEE'))->first()){
            $action->setActionStatus($this->getStatusByType('CON'));//setting the initial state disable
        }else{
            if ($employer->getIdSqlSociety()) {//if the employer exist in SQL info must be correct
                $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to NEW
            } else {//else info need to be checked by backoffice
                $action->setActionStatus($this->getStatusByType('ERRO'));//setting the Action Status to ERRO
            }
        }
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        $action->setFinishedAt($user->getLastPayDate());
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessary to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer Document                ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
        $user->addAction($action);//adding the action to the user
        $action->setActionTypeActionType($this->getActionByType('VDDE'));//setting the actionType validate employer document
        if($person->getActionsByActionType($this->getActionByType('VDD'))->first()){
            $action->setActionStatus($this->getStatusByType('CON'));//setting the initial state disable
        }else{
            if ($employer->getPersonPerson()->getDocumentDocument()) {//if employer has documentDocument checking for existing media
                if ($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                    $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                    if($notifications->isEmpty()){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }else{
                        $equal = false;
                        /** @var Notification $notification */
                        foreach ($notifications as $notification){
                            $exUrl = explode('/',$notification->getRelatedLink());
                            if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                                $equal = true;
                                $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                                $notification->activate();
                            }
                        }
                        if(!$equal){
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
        }
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        $action->setFinishedAt($user->getLastPayDate());
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessary to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer RUT                     ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
        $user->addAction($action);//adding the action to the user
        $action->setActionTypeActionType($this->getActionByType('VRTE'));//setting the actionType validate employer rut
        $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        if($person->getActionsByActionType($this->getActionByType('VRT'))->first()){
            $action->setActionStatus($this->getStatusByType('CON'));
        }else{
            if ($employer->getPersonPerson()->getRutDocument()) {//if employer has RutDocument checking for existing media
                if ($employer->getPersonPerson()->getRutDocument()->getMediaMedia()) {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('RUT');
                    $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                    if($notifications->isEmpty()){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }else{
                        $equal = false;
                        /** @var Notification $notification */
                        foreach ($notifications as $notification){
                            $exUrl = explode('/',$notification->getRelatedLink());
                            if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                                $equal = true;
                                $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                                $notification->activate();
                            }
                        }
                        if(!$equal){
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('RUT');
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
        }
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessary to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer Mandatory               ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
        $user->addAction($action);//adding the action to the user
        $action->setActionTypeActionType($this->getActionByType('VM'));//setting the actionType validate employer mandatory
        $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        if ($employer->getMandatoryDocument()) {//if employer has mandatoryDocument checking for existing media
            if ($employer->getMandatoryDocument()->getMediaMedia()) {
                $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                //finding the notification to upload the document
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('MAND');
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getIdEmployer()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                        $dAction="Bajar";
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            //finding the notification to upload the document
            $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
            $docType = $this->getDocumentTypeByCode('MAND');
            $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
            if($notifications->isEmpty()){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                $dAction="Bajar";
                $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
            }else{
                $equal = false;
                /** @var Notification $notification */
                foreach ($notifications as $notification){
                    $exUrl = explode('/',$notification->getRelatedLink());
                    if($employer->getIdEmployer()==$exUrl[4] and !$equal){
                        $equal = true;
                        $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                        $notification->activate();
                    }
                }
                if(!$equal){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }
            }
        }
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessary to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Employer Entities Actions                        ║
         * ╚══════════════════════════════════════════════════╝
         */
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($employer->getEntities() as $employerHasEntity) {//crossing employerHasEntities to crreate actions for each one
            $action = new Action();
            $procedure->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            if ($employerHasEntity->getState() == 0) {//validate entity
                $action->setActionTypeActionType($this->getActionByType('VENE'));//setting actionType to validate entity
            } elseif ($employerHasEntity->getState() == 1) {//subscribe entity
                $action->setActionTypeActionType($this->getActionByType('INE'));//setting actionType to validate entity
            }
            $action->setEmployerEntity($employerHasEntity);
            $action->setActionStatus($this->getStatusByType('NEW'));//setting the action status to new
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
            if ($aCount != null) {//if there is a parameter for the id
                $action->setIdAction($aCount);//setting the action id
                $em->persist($action);//persisting the action
                $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                $aCount++;//increment actionId Count
            } else {
                $em->persist($action);//persisting the action
            }
        }
        $em->persist($procedure);//persisting procedure
        return array('aCount'=>$aCount,'response'=>$response);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createActionsEmployer                        ║
     * ║ Function that creates all the actions for the user    ║
     * ║ user must be only a employer                          ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param integer $aCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployer(User $user,RealProcedure $procedure,$aCount=null){
        $response='- - - CREANDO ACCIONES EMPLEADOR - - -<br>';
        $em = $this->getDoctrine()->getManager();
        $employer = $user->getPersonPerson()->getEmployer();
        $person = $user->getPersonPerson();//getting the person from the user
        //if employer person has actions it means its probably an employee too
        if ($employer->getPersonPerson()->getAction()->isEmpty()) {
            /**
             * ╔══════════════════════════════════════════════════╗
             * ║ Action validate employer info                    ║
             * ╚══════════════════════════════════════════════════╝
             */
            $action = new Action();
            $procedure->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            $action->setActionTypeActionType($this->getActionByType('VER'));//setting the actionType validate employer info
            $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
            if ($employer->getIdSqlSociety()) {//if the employer exist in SQL info must be correct
                $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to NEW
            } else {//else info need to be checked by backoffice
                $action->setActionStatus($this->getStatusByType('ERRO'));//setting the Action Status to ERRO
            }
            if ($aCount != null) {//if there is a parameter for the id
                $action->setIdAction($aCount);//setting the action id
                $em->persist($action);//persisting the action
                $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                $aCount++;//increment actionId Count
            } else {
                $em->persist($action);//persisting the action
            }
            /**
             * ╔══════════════════════════════════════════════════╗
             * ║ Action validate employer Document                ║
             * ╚══════════════════════════════════════════════════╝
             */
            $action = new Action();
            $procedure->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            $action->setActionTypeActionType($this->getActionByType('VDDE'));//setting the actionType validate employer document
            $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
            if ($employer->getPersonPerson()->getDocumentDocument()) {//if employer has documentDocument checking for existing media
                if ($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                    $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                    if($notifications->isEmpty()){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }else{
                        $equal = false;
                        /** @var Notification $notification */
                        foreach ($notifications as $notification){
                            $exUrl = explode('/',$notification->getRelatedLink());
                            if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                                $equal = true;
                                $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                                $notification->activate();
                            }
                        }
                        if(!$equal){
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
            if ($aCount != null) {//if there is a parameter for the id
                $action->setIdAction($aCount);//setting the action id
                $em->persist($action);//persisting the action
                $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                $aCount++;//increment actionId Count
            } else {
                $em->persist($action);//persisting the action
            }
            /**
             * ╔══════════════════════════════════════════════════╗
             * ║ Action validate employer RUT                     ║
             * ╚══════════════════════════════════════════════════╝
             */
            $action = new Action();
            $procedure->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            $action->setActionTypeActionType($this->getActionByType('VRTE'));//setting the actionType validate employer rut
            $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
            if ($employer->getPersonPerson()->getRutDocument()) {//if employer has RutDocument checking for existing media
                if ($employer->getPersonPerson()->getRutDocument()->getMediaMedia()) {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('RUT');
                    $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                    if($notifications->isEmpty()){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }else{
                        $equal = false;
                        /** @var Notification $notification */
                        foreach ($notifications as $notification){
                            $exUrl = explode('/',$notification->getRelatedLink());
                            if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                                $equal = true;
                                $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                                $notification->activate();
                            }
                        }
                        if(!$equal){
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('RUT');
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
            if ($aCount != null) {//if there is a parameter for the id
                $action->setIdAction($aCount);//setting the action id
                $em->persist($action);//persisting the action
                $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                $aCount++;//increment actionId Count
            } else {
                $em->persist($action);//persisting the action
            }
            /**
             * ╔══════════════════════════════════════════════════╗
             * ║ Action validate employer Mandatory               ║
             * ╚══════════════════════════════════════════════════╝
             */
            $action = new Action();
            $procedure->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            $action->setActionTypeActionType($this->getActionByType('VM'));//setting the actionType validate employer mandatory
            $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
            if ($employer->getMandatoryDocument()) {//if employer has mandatoryDocument checking for existing media
                if ($employer->getMandatoryDocument()->getMediaMedia()) {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('MAND');
                    $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                    if($notifications->isEmpty()){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                        $dAction="Bajar";
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }else{
                        $equal = false;
                        /** @var Notification $notification */
                        foreach ($notifications as $notification){
                            $exUrl = explode('/',$notification->getRelatedLink());
                            if($employer->getIdEmployer()==$exUrl[4] and !$equal){
                                $equal = true;
                                $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                                $notification->activate();
                            }
                        }
                        if(!$equal){
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                            $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                            $dAction="Bajar";
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('MAND');
                $notifications = $employer->getPersonPerson()->getNotificationByDocumentType($docType);
                if($notifications->isEmpty()){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employer->getIdEmployer()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                        $dAction="Bajar";
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl);
                    }
                }
            }
            if ($aCount != null) {//if there is a parameter for the id
                $action->setIdAction($aCount);//setting the action id
                $em->persist($action);//persisting the action
                $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                $aCount++;//increment actionId Count
            } else {
                $em->persist($action);//persisting the action
            }
            /**
             * ╔══════════════════════════════════════════════════╗
             * ║ Employer Entities Actions                        ║
             * ╚══════════════════════════════════════════════════╝
             */
            /** @var EmployerHasEntity $employerHasEntity */
            foreach ($employer->getEntities() as $employerHasEntity) {//crossing employerHasEntities to crreate actions for each one
                $action = new Action();
                $procedure->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                if ($employerHasEntity->getState() == 0) {//validate entity
                    $action->setActionTypeActionType($this->getActionByType('VENE'));//setting actionType to validate entity
                } elseif ($employerHasEntity->getState() == 1) {//subscribe entity
                    $action->setActionTypeActionType($this->getActionByType('INE'));//setting actionType to validate entity
                }
                $action->setEmployerEntity($employerHasEntity);
                $action->setActionStatus($this->getStatusByType('NEW'));//setting the action status to new
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
                if ($aCount != null) {//if there is a parameter for the id
                    $action->setIdAction($aCount);//setting the action id
                    $em->persist($action);//persisting the action
                    $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                    $aCount++;//increment actionId Count
                } else {
                    $em->persist($action);//persisting the action
                }
            }
            $em->persist($procedure);//persisting procedure
        } else {
            $response .= "ERROR: el usuario " . $employer->getPersonPerson()->getFullName() . " ya tiene acciones creadas<br>";
        }
        return array('aCount'=>$aCount,'response'=>$response);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createActionsEmployee                        ║
     * ║ Function that creates the actions for the employee    ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param EmployerHasEmployee $employerHasEmployee      ║
     * ║  @param bool $atLeastOneFinished                      ║
     * ║  @param integer $aCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployee(User $user,RealProcedure $procedure,EmployerHasEmployee $employerHasEmployee,$atLeastOneFinished,$aCount=null){
        $response='- - - - - CREANDO ACCIONES EMPLEADO - - - - -<br>';
        $employee = $employerHasEmployee->getEmployeeEmployee();
        $em = $this->getDoctrine()->getManager();
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employee info                    ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employee->getPersonPerson()->addAction($action);//adding the action to the employerPerson
        $user->addAction($action);//adding the action to the user
        $action->setActionTypeActionType($this->getActionByType('VEE'));//setting the actionType validate employer info
        $action->setActionStatus($this->getStatusByType('DIS'));//setting the initial state disable
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        if ($employerHasEmployee->getExistentSQL()) {//if the employee exist in SQL info must be correct
            $action->setActionStatus($this->getStatusByType('FIN'));//setting the Action Status to Finished
            if ($employerHasEmployee->getState() > 3 and !$atLeastOneFinished) {
                $atLeastOneFinished = true;
                $response .= '- - - - - UN EMPLEADO FINALIZADO - - - - -<br>';
                /** @var Action $tempAction */
                $tempAction = $procedure->getActionsByActionType($this->getActionByType('VER'))->first();
                if ($tempAction and $tempAction->getActionStatus()->getCode() == 'NEW') {
                    $tempAction->setActionStatus($this->getStatusByType('FIN'));
                    $em->persist($tempAction);
                }
                $tempAction = $procedure->getActionsByActionType($this->getActionByType('VDDE'))->first();
                if($tempAction and $tempAction->getActionStatus()->getCode()=='NEW'){
                    $tempAction->setActionStatus($this->getStatusByType('FIN'));
                    $em->persist($tempAction);
                }
                $tempAction = $procedure->getActionsByActionType($this->getActionByType('VRTE'))->first();
                if($tempAction and $tempAction->getActionStatus()->getCode()=='NEW'){
                    $tempAction->setActionStatus($this->getStatusByType('FIN'));
                    $em->persist($tempAction);
                }
                $tempAction = $procedure->getActionsByActionType($this->getActionByType('VM'))->first();
                if($tempAction and $tempAction->getActionStatus()->getCode()=='NEW'){
                    $tempAction->setActionStatus($this->getStatusByType('FIN'));
                    $em->persist($tempAction);
                }
                foreach ($procedure->getActionsByActionType($this->getActionByType('VENE')) as $tempAction){
                    if($tempAction){
                        $tempAction->setActionStatus($this->getStatusByType('FIN'));
                        $em->persist($tempAction);
                    }
                }
                foreach ($procedure->getActionsByActionType($this->getActionByType('INE')) as $tempAction){
                    if($tempAction) {
                        $tempAction->setActionStatus($this->getStatusByType('FIN'));
                        $em->persist($tempAction);
                    }
                }
            }
        } elseif ($employerHasEmployee->getState() > 3) {//else if the employee is finished info must have errors and need to be checked by backoffice
            $action->setActionStatus($this->getStatusByType('ERRO'));//setting the Action Status to Error
        } else {
            $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to new
        }
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employee Document                ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
        $user->addAction($action);//adding the action to the user
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VDD'));
        if ($employee->getPersonPerson()->getDocumentDocument()) {//if employee has documentDocument checking for existing media
            if ($employee->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - - <br>";
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - - <br>";
        }
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employee AuthLetter              ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
        $user->addAction($action);//adding the action to the user
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($user->getLastPayDate());//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VCAT'));
        if ($employerHasEmployee->getAuthDocument()) {//if employerHasEmployee has authLetter checking for existing media
            if ($employerHasEmployee->getAuthDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
        }
        if ($aCount != null) {//if there is a parameter for the id
            $action->setIdAction($aCount);//setting the action id
            $em->persist($action);//persisting the action
            $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
            $aCount++;//increment actionId Count
        } else {
            $em->persist($action);//persisting the action
        }
        return array('aCount'=>$aCount,'response'=>$response, 'atLeast'=>$atLeastOneFinished);
    }

    /**
     * @param $code
     * @return ProcedureType
     */
    protected function getProcedureByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=> $code));
    }

    /**
     * @param $code
     * @return StatusTypes
     */
    protected function getStatusByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findOneBy(array('code'=> $code));
    }

    /**
     * @param $docCode
     * @return DocumentType
     */
    protected function getDocumentTypeByCode($docCode){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array('docCode'=> $docCode));
    }

    /**
     * @param $code
     * @return ActionType
     */
    protected function getActionByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=> $code));
    }

    protected function createNotification($person, $descripcion, $url, $documentType = null, $action = "Subir",$dAction=null,$dUrl=null)
    {
        $notification = new Notification();
        $notification->setPersonPerson($person);
        $notification->setStatus(1);
        $notification->setDocumentTypeDocumentType($documentType);
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $notification->setDownloadAction($dAction);
        $notification->setDownloadLink($dUrl);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
    }

    /**
     * Assign document status for all employerHasEmployees
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Assign document status to all employerHasEmployees relation",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postAssignDocumentStatusAction()
    {
        $em = $this->getDoctrine()->getManager();
        $EHES = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findAll();
        /** @var EmployerHasEmployee $EHE */
        foreach($EHES as $EHE){
            if ($EHE->getState()<=2){
                $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:ContractDocumentStatusType")->findOneBy(array('documentStatusCode'=>'ALLDCP'));
                $EHE->setDocumentStatusType($documentStatus);
            }else {
                /** @var Employer $employer */
                $employer = $EHE->getEmployerEmployer();
                /** @var Employee $employee */
                $employee = $EHE->getEmployeeEmployee();
                /** @var Person $ePerson */
                $ePerson = $employer->getPersonPerson();
                /** @var Person $eePerson */
                $eePerson = $employee->getPersonPerson();
                /** @var Contract $contract */
                $contract = $EHE->getActiveContract();
                $procedure = $em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findOneBy(array(
                    'employerEmployer'=>$employer,
                    'procedureTypeProcedureType'=>$em->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=>'REE'))
                ));
                //setting flags
                $states = array(
                    'eDocument' => false,
                    'eDocumentMedia' => false,
                    'eRut' => false,
                    'eRutMedia' => false,
                    'eMAndatory' => false,
                    'eMandatoryMedia' => false,
                    'eeDocument' => false,
                    'eeDocumentMedia' => false,
                    'eeAuth' => false,
                    'eeAuthMedia' => false,
                    'contract' => false,
                    'contractMedia' => false,
                    'eError' => false,
                    'eeError' => false,
                    'contractError' => false,
                    'pendingEDoc' => false,
                    'pendingEeDoc' => false,
                    'pendingContract' => false,
                    'eDocumentsValidated' => false,
                    'eeDocumentsValidated' => false,
                );
                if ($employer->getPersonPerson()->getDocumentDocument()) {
                    $states['eDocument'] = true;
                    if ($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                        $states['eDocumentMedia'] = true;
                    }
                }
                if ($employer->getPersonPerson()->getRutDocument()) {
                    $states['eRut'] = true;
                    if ($employer->getPersonPerson()->getRutDocument()->getMediaMedia()) {
                        $states['eRutMedia'] = true;
                    }
                }
                if ($employer->getMandatoryDocument()) {
                    $states['eMAndatory'] = true;
                    if ($employer->getMandatoryDocument()->getMediaMedia()) {
                        $states['eMandatoryMedia'] = true;
                    }
                }
                if ($employee->getPersonPerson()->getDocumentDocument()) {
                    $states['eeDocument'] = true;
                    if ($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                        $states['eeDocumentMedia'] = true;
                    }
                }
                if ($EHE->getAuthDocument()) {
                    $states['eeAuth'] = true;
                    if ($EHE->getAuthDocument()->getMediaMedia()) {
                        $states['eeAuthMedia'] = true;
                    }
                }
                if ($contract) {
                    if ($contract->getDocumentDocument()) {
                        $states['contract'] = true;
                        if ($contract->getDocumentDocument()->getMediaMedia()) {
                            $states['contractMedia'] = true;
                        }
                    }
                }
                if ((!$states['eDocument'] and !$states['eDocumentMedia'])or(!$states['eRut'] and !$states['eRutMedia']) or (!$states['eMAndatory'] and !$states['eMandatoryMedia'])) {
                    $states['pendingEDoc'] = true;
                }
                if (($states['eDocument'] and !$states['eDocumentMedia'])or ($states['eRut'] and !$states['eRutMedia']) or ($states['eMAndatory'] and !$states['eMandatoryMedia'])) {
                    $states['eError'] = true;
                }
                if ((!$states['eeDocument'] and !$states['eeDocumentMedia']) or (!$states['eeAuth'] and !$states['eeAuthMedia'])) {
                    $states['pendingEeDoc'] = true;
                }
                if (($states['eeDocument'] and !$states['eeDocumentMedia']) or ($states['eeAuth'] and !$states['eeAuthMedia'])) {
                    $states['eeError'] = true;
                }

                if($states['pendingEDoc'] and $states['pendingEeDoc']){
                    $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:ContractDocumentStatusType")->findOneBy(array('documentStatusCode'=>'ALLDCP'));
                }elseif($states['pendingEDoc'] and !$states['pendingEeDoc']){
                    $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:ContractDocumentStatusType")->findOneBy(array('documentStatusCode'=>'ERDCPE'));
                }elseif (!$states['pendingEDoc'] and $states['pendingEeDoc']){
                    $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:ContractDocumentStatusType")->findOneBy(array('documentStatusCode'=>'EEDCPE'));
                }elseif(!$states['pendingEDoc'] and !$states['pendingEeDoc']){
                    if(!$EHE->getDateDocumentsUploaded()){
                        $documents =array();
                        $documents[] = $employer->getPersonPerson()->getDocumentDocument();
                        $documents[] = $employer->getPersonPerson()->getRutDocument();
                        $documents[] = $employer->getMandatoryDocument();
                        $documents[] = $employee->getPersonPerson()->getDocumentDocument();
                        $documents[] = $EHE->getAuthDocument();
                        $dateUploaded = null;
                        /** @var Document $document */
                        foreach ($documents as $document){
                            if($dateUploaded==null and $document->getMediaMedia()->getUpdatedAt()){
                                $dateUploaded = $document->getMediaMedia()->getUpdatedAt();
                            }elseif($document->getMediaMedia()->getUpdatedAt() and $dateUploaded < $document->getMediaMedia()->getUpdatedAt()){
                                $dateUploaded = $document->getMediaMedia()->getUpdatedAt();
                            }
                        }
                        $EHE->setDateDocumentsUploaded($dateUploaded);
                        $em->persist($EHE);
                        $em->flush();
                    }
                    $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDCE'));
                    /** @var Action $employerAction */
                    $employerAction = $procedure->getActionsByActionType($actionType)->first();
                    if($employerAction->getStatus()== "Completado"){
                        $states['eDocumentsValidated']=true;
                    }elseif($employerAction->getStatus()== "Error") {
                        $states['eError'] = true;
                    }
                    $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDC'));
                    /** @var Action $employeeAction */
                    $employeeAction = $procedure->getActionsByActionType($actionType);
                    if($employeeAction->getStatus()=="Completado"){
                        $states['eeDocumentsValidated']=true;
                    }elseif($employeeAction->getStatus()=="Error"){
                        $states['eeError']=true;
                    }
                    if($states['eDocumentsValidated'] and $states['eeDocumentsValidated']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'ALDCVA'));
                    }elseif($states['eDocumentsValidated'] and !$states['eeDocumentsValidated'] and !$states['eeError']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'ERDCVA'));
                    }elseif(!$states['eDocumentsValidated'] and !$states['eError'] and $states['eeDocumentsValidated']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'EEDCVA'));
                    }elseif($states['eDocumentsValidated'] and $states['eeError']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'ERVEEE'));
                    }elseif(!$states['eDocumentsValidated'] and $states['eError'] and $states['eeDocumentsValidated']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'EEVERE'));
                    }elseif(!$states['eDocumentsValidated'] and $states['eError'] and !$states['eeDocumentsValidated'] and !$states['eeError']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'ERDVE'));
                    }elseif(!$states['eDocumentsValidated'] and !$states['eError'] and !$states['eeDocumentsValidated'] and $states['eeError']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'EEDCE'));
                    }elseif($states['eError'] and $states['eeError']){
                        $documentStatus = $em->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>'ALLDCE'));
                    }


                }


            }
        }
        $view = View::create();
        $view->setStatusCode(200);
        return $view;
    }
}
?>