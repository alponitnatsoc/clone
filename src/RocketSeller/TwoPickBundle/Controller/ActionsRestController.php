<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Payroll;
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
     * Calculate procedure status
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Runs calculateProcedureStatus to all procedures",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postCalculateProceduresStatusAction(){
        $response = "Comienza<br>";
        $change = false;
        $em = $this->getDoctrine()->getManager();
        $procedures = $em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll();
        foreach ($procedures as $procedure) {
            if($this->calculateProcedureStatus($procedure,true)==1){
                $em->persist($procedure);
                $change = true;
            }
        }
        if($change)$em->flush();
        $response .= "Estados actualizados<br>";
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Calculate procedure priority
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Runs calculateProcedurePriority to all procedures",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="begin", description="Recibe el numero menor")
     * @RequestParam(name="end", description="Recibe el numero mayor")
     *
     * @return View
     *
     */
    public function postCalculateProceduresPriorityAction(ParamFetcher $paramFetcher){
        $begin = ($paramFetcher->get('begin'));
        $end = ($paramFetcher->get('end'));
        $response = "Comienza<br>";
        $change = false;
        $em = $this->getDoctrine()->getManager();
        $procedures = $em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll();
        $count = 1;
        foreach ($procedures as $procedure) {
            if($count >= $begin and $count<=$end){
                if($this->calculateProcedurePriority($procedure,true)==1){
                    $em->persist($procedure);
                    $change = true;
                }

            }
            if($count%10 == 0){
                if($count >= $begin and $count<=$end){
                    if($change){
                        $response .= "grupo de ".($count - 9)." a ". $count ." Actualizado <br>";
                        $em->flush();
                    }else{
                        $response .= "grupo de ". ($count - 9) ." a ". $count ." No tenia cambios <br>";
                    }
                }
                $change = false;
            }
            $count++;

        }
        if($count >= $begin and $count<=$end) {
            if ($change) {
                $response .= "grupo hasta " . $count . " Actualizado <br>";
                $em->flush();
            }
        }

        $response .= "Prioridades actualizadas<br>";
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Calculate Documents Status
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Runs CalculateDocumentsStatus to all procedures",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="begin", description="Recibe el numero menor")
     * @RequestParam(name="end", description="Recibe el numero mayor")
     *
     * @return View
     *
     */
    public function postCalculateDocumentsStatusAction(ParamFetcher $paramFetcher){
        $begin = ($paramFetcher->get('begin'));
        $end = ($paramFetcher->get('end'));
        $response = "Comienza<br>";
        $em = $this->getDoctrine()->getManager();
        $ehes = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findAll();
        foreach ($ehes as $ehe) {
            $ehe->setDocumentStatus(null);
            $em->persist($ehe);
        }
        $procedures = $em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll();
        $count = 1;
        foreach ($procedures as $procedure) {
            if($count >= $begin and $count<=$end and $count%2 != 0){
                if($this->calculateDocumentStatus($procedure)==1){
                    $em->persist($procedure);
                }
            }
            $count++;
        }
        $em->flush();
        $response .= "Estados de documentos actualizadas<br>";
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

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
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ActionError")->findAll() as $actionError) {
            $em->remove($actionError);
        }
        $em->flush();
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
        $procedureType = new ProcedureType("Acciones de Validación",'VAC');
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

        $statusType = new StatusTypes( "Deshabilitado" , "DIS" );
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
            }
            $actions = $user->getActionsByType($this->getActionByType('IN'));
            $response .= "ACTIONS ENCONTRADAS: ".$actions->count()."<br>";
            if($actions->count()>0) {
                if($procedure == null)
                    $procedure = $actions->first()->getRealProcedureRealProcedure();
                foreach ($actions as $action) {
                    $em->remove($action);
                }
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
                        /** @var EmployerHasEmployee $employerHasEmployee */
                        foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee) {
                            if($employerHasEmployee->getEmployeeEmployee()->getEntities()->count()>0 and $employerHasEmployee->getState()>2){
                                foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $entity) {
                                    $response.= "EMPLOYEE ACTION ENCONTRADA<br>";
                                    if ($entity->getState() >= 0) {
                                        $action = new Action();
                                        $action->setStatus('Nuevo');
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
                            }
                        }
                    }
                }

            }
        }
        $em->flush();

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Create Procedures
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Runs Create Procedures in range passed by param",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="begin", description="Recibe el numero menor")
     * @RequestParam(name="end", description="Recibe el numero mayor")
     * @RequestParam(name="nCount", description="numero de notificaciones")
     * @RequestParam(name="pCount", description="numero de realProcedures")
     * @RequestParam(name="aCount", description="numero de actions")
     *
     * @return View
     *
     */
    public function postRecreateProceduresByRangeAction(ParamFetcher $paramFetcher){
        $begin = ($paramFetcher->get('begin'));
        $end = ($paramFetcher->get('end'));
        $nCount = ($paramFetcher->get('nCount'));
        $pCount = ($paramFetcher->get('pCount'));
        $aCount = ($paramFetcher->get('aCount'));
        $response = "Comienza<br>";
        $count = 1;
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        /** @var User $user */
        foreach ($users as $user) {
            if($count>=$begin and $count<=$end){
                try {
                    $response .= "- - - USUARIO ".$count.": " . $user->getPersonPerson()->getFullName() . " - - -<br>";
                    if ($user->getStatus() >= 2) {
                        /**
                         * ╔══════════════════════════════════════════════════╗
                         * ║ creating user RealProcedures                     ║
                         * ╚══════════════════════════════════════════════════╝
                         */
                        $response .= "- - - CREANDO TRAMITES - - -<br>";
                        if ($user->getRealProcedure()->isEmpty()) {
                            $result = $this->createProcedure($user, $pCount, $aCount, $nCount);
                            $response .= $result['response'];
                            $pCount = $result['pCount'];
                            $aCount = $result['aCount'];
                            $nCount = $result['nCount'];
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
            $count++;
        }
        $response .= "pCount = ".$pCount."<br>";
        $response .= "nCount = ".$nCount."<br>";
        $response .= "aCount = ".$aCount."<br>";
        $em->flush();
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
        foreach ($em->getRepository("RocketSellerTwoPickBundle:Notification")->findAll() as $notification){
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('CC'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('CE'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('CE'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('TI'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('RUT'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('MAND'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('CAS'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('FIRM'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('CTR'))
                $em->remove($notification);
            if($notification->getDocumentTypeDocumentType()==$this->getDocumentTypeByCode('PASAPORTE'))
                $em->remove($notification);
            if($notification->getRelatedLink()!=null){
                $exstr = explode("/",$notification->getRelatedLink());
                if($exstr[1]=='manage')
                    $em->remove($notification);
                if($exstr[1]=='old')
                    $em->remove($notification);
                if($exstr[1]=='documents')
                    $em->remove($notification);
            }
        }
        $em->flush();

        if(!$this->getDocumentTypeByCode('RAD')){
            $response.="- - - RAD CREADO - - -<br>";
            $documentTypeRad = new DocumentType();
            $documentTypeRad->setName('Radicado');
            $documentTypeRad->setDocCode('RAD');
            $em->persist($documentTypeRad);
        }

        $nCount=1;
        foreach ($em->getRepository("RocketSellerTwoPickBundle:Notification")->findAll() as $notification){
            $notification->setId($nCount);//setting the id
            $em->persist($notification);//persisting the notification
            $metadata = $em->getClassMetadata(get_class($notification));//line of code necessary to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessary to force an id
            $nCount++;//increment notificationId Count
        }
        $em->flush();
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * saves some important data from the procedures before other actions
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "saves info from the procedures before other rest actions",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *   }
     * )
     *
     * @return View
     */
    public function postSaveProceduresDataAction(){
        $response = "Comienza<br>";
        $em =$this->getDoctrine()->getManager();
        $procedures = $em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll();
        /** @var RealProcedure $procedure */
        foreach ($procedures as $procedure) {
            $log = new Log($procedure->getUserUser(),"RealProcedure","createdAt",$procedure->getIdProcedure(),$procedure->getCreatedAt()->format("Y-m-d H:i:s"),"",null);
            $em->persist($log);
            /** @var Action $action */
            $response.= count($procedure->getAction())."<br>";
            foreach ($procedure->getAction() as $action) {
                if($action->getStatus()=='Error'){
                    $log2 = new Log($procedure->getUserUser(),"Action",'Status',$action->getIdAction(),$action->getStatus(),'',$action->getActionTypeCode().':'.$action->getPersonPerson()->getIdPerson());
                    $em->persist($log2);
                }
            }
        }
        $em->flush();
        $response .= "termino<br>";
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
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createProcedure(User $user,$pCount=null,$aCount=null, $nCount= null){
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
                $procedure->setCreatedAt($this->getDateCreatedInLog($user));//setting the createAt Date
                $procedure->setProcedureStatus($this->getStatusByType('NEW'));//setting the initial status Disable
                $procedure->setBackOfficeDate($this->getDateCreatedInLog($user));//setting the backofice start Date
                $procedure->setFinishedAt(null);
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
                    $result = $this->createActionsEmployerEmployee($user, $procedure, $aCount, $nCount);//creating actions with function for employer also employee
                    $response .= $result['response'];
                    $aCount = $result['aCount'];
                    $nCount = $result['nCount'];
                } else {
                    $result = $this->createActionsEmployer($user, $procedure, $aCount, $nCount);//creating actions with function for employer only
                    $response .= $result['response'];
                    $aCount = $result['aCount'];
                    $nCount = $result['nCount'];
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
                                $result = $this->createActionsEmployeeEmployer($user, $procedure,$employerHasEmployee, $atLeastOneFinished, $aCount ,$nCount);//creating actions with function for employer also employee
                                $response .= $result['response'];
                                $aCount = $result['aCount'];
                                $atLeastOneFinished = $result['atLeast'];
                                $nCount = $result['nCount'];
                            }elseif($eePerson->getEmployer() and $isEmployeeOf >1){//employee is also a employer and is employee of more than one employer
                                $response .= "- - - - EMPLEADO EMPLEADOR CON VARIOS EMPLEADORES - - - -<br>";
                            }elseif($isEmployeeOf > 1){//employee has more employers
                                $response .= "- - - - EMPLEADO QUE YA ES EMPLEADO - - - -<br>";
                                $result = $this->createActionsEmployeeEmployee($user, $procedure,$employerHasEmployee, $atLeastOneFinished, $aCount, $nCount);//creating actions with function for employer also employee
                                $response .= $result['response'];
                                $aCount = $result['aCount'];
                                $atLeastOneFinished = $result['atLeast'];
                                $nCount = $result['nCount'];
                            }
                        } else {
                            $result = $this->createActionsEmployee($user, $procedure,$employerHasEmployee, $atLeastOneFinished, $aCount, $nCount);//creating actions with function for employer also employee
                            $response .= $result['response'];
                            $aCount = $result['aCount'];
                            $atLeastOneFinished = $result['atLeast'];
                            $nCount = $result['nCount'];
                        }
                    }
                }
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Procedure Validate Actions                       ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $procedure2 = new RealProcedure();
                $procedure2->setProcedureTypeProcedureType($this->getProcedureByType('VAC'));//setting the procedure type
                $employer->addRealProcedure($procedure2);//adding the realProcedure to the employer
                $procedure2->setCreatedAt($this->getDateCreatedInLog($user));//setting the createAt Date
                $procedure2->setProcedureStatus($this->getStatusByType('NEW'));//setting the initial status Disable
                $procedure2->setBackOfficeDate($this->getDateCreatedInLog($user));//setting the backofice start Date
                $procedure2->setPriority(0);//setting the default priority
                $procedure2->setFinishedAt(null);
                $user->addRealProcedure($procedure2);//adding the realProcedure to the user
                if ($pCount != null) {//if theres an id parameter for the procedure
                    $procedure2->setIdProcedure($pCount);//setting the id to the realProcedure
                    $em->persist($procedure2);//persisting the procedure
                    $metadata = $em->getClassMetadata(get_class($procedure));//line of code necessaryy to force an id
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
                    $pCount++;//increment id realProcedure count
                } else {
                    $em->persist($procedure2);//persisting the procedure
                }
                $response .= "- - - PROCEDURE ACCIONES VALIDACION CREADO - - -<br>";
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Employer post subscribe action begin             ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $result = $this->createPostValidateActionsEmployerEmployee($user, $procedure2, $aCount, $nCount);//creating actions with function for employer also employee
                $response .= $result['response'];
                $aCount = $result['aCount'];
                $nCount = $result['nCount'];
                $response .= "- - - TRAMITES CREADOS - - -<br><br>";
            }else{
                $response .= "ERROR: EL EMPLEADOR NO TIENE ENTIDADES . . .<br><br>";
            }
        }else{
            $response .="ERROR: NO SE ENCONTRO EL EMPELADOR<br><br>";
        }
        return array('pCount'=>$pCount,'aCount'=>$aCount,'response'=>$response, 'nCount'=>$nCount);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createPostValidateActionsEmployerEmployee    ║
     * ║ this function creates the post subscription actions   ║
     * ║ such as validate contract and upload RAD documents    ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param integer $aCount optional                      ║
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    public function createPostValidateActionsEmployerEmployee($user, $procedure, $aCount, $nCount){
        $response = '';
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();//getting the employer
        $em = $this->getDoctrine()->getManager();
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($employer->getEntities() as $employerHasEntity){
            if($employerHasEntity->getState()==1){
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Action Upload Document RAD                       ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $action = new Action();
                $procedure->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionByType('SDE'));//setting the actionType
                $action->setActionStatus($this->getStatusByType('NEW'));
                $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
                $action->setEmployerEntity($employerHasEntity);
                $action->setUpdatedAt();
                if ($aCount != null) {//if there is a parameter for the id
                    $action->setIdAction($aCount);//setting the action id
                    $em->persist($action);//persisting the action
                    $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessary to force an id
                    $aCount++;//increment actionId Count
                } else {
                    $em->persist($action);//persisting the action
                }
            }
        }
        $response .= "- - - - RADS CREADOS - - - -<br>";
        /** @var EmployerHasEmployee $employerHasEmployee */
        foreach ($employer->getEmployerHasEmployees() as $employerHasEmployee) {
            if($employerHasEmployee->getState()>2){
                if($employerHasEmployee->getExistentSQL()==1){
                    $response .= "- - - - EXISTE SQL - - - -<br>";
                    /**
                     * ╔══════════════════════════════════════════════════╗
                     * ║ Action validate contract                         ║
                     * ╚══════════════════════════════════════════════════╝
                     */
                    $action = new Action();
                    $procedure->addAction($action);//adding the action to the procedure
                    $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                    $user->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionByType('VC'));//setting the actionType validate contract
                    $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createdAt Date
                    $action->setUpdatedAt();
                    if($employerHasEmployee->getActiveContract()->getDocumentDocument()){//if document exist
                        if($employerHasEmployee->getActiveContract()->getDocumentDocument()->getMediaMedia()){//searching for media
                            if($this->getErrorInLog($procedure->getUserUser(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),'VC')){
                                $action->setActionStatus($this->getStatusByType('ERRO'));//if contract document exist in DB
                            }else{
                                $action->setActionStatus($this->getStatusByType('NEW'));//if contract document exist in DB
                            }

                        }else{//if not media found error
                            $action->setActionStatus($this->getStatusByType('ERRO'));
                            $response .= "- - - - ERROR: DOCUMENTO CONTRATO NO ENCONTRADO - - - - <br>";
                            $docType = $this->getDocumentTypeByCode('CTR');
                            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($person,$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$docType);//finding contract notifications for the person
                            if($notifications== null){//if notification not found
                                if($employerHasEmployee->getLegalFF()==1){//its ancient
                                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                    $utils = $this->get('app.symplifica_utils');
                                    $dAction=null;
                                    $dUrl=null;
                                    $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                    $flag=false;
                                    /** @var Configuration $config */
                                    foreach ($configurations as $config) {//searching if user need to generate a new contract
                                        if($config->getValue()=="PreLegal-SignedContract"){
                                            $flag=true;
                                            break;
                                        }
                                    }
                                    if(!$flag){//generating url to download contract
                                        $dAction="Bajar";
                                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                    }
                                    $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                    $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                    $nCount++;//notification created
                                }elseif($employerHasEmployee->getLegalFF()==0){//its new
                                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                    $utils = $this->get('app.symplifica_utils');
                                    $dAction="Bajar";
                                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                    $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                    $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                    $nCount++;//notification created
                                }else{
                                    $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                                }
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
                                    if($employerHasEmployee->getLegalFF()==1){//its ancient
                                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                        $utils = $this->get('app.symplifica_utils');
                                        $dAction=null;
                                        $dUrl=null;
                                        $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                        $flag=false;
                                        /** @var Configuration $config */
                                        foreach ($configurations as $config) {//searching if user need to generate a new contract
                                            if($config->getValue()=="PreLegal-SignedContract"){
                                                $flag=true;
                                                break;
                                            }
                                        }
                                        if(!$flag){//generating url to download contract
                                            $dAction="Bajar";
                                            $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                        }
                                        $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                        $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                        $nCount++;//notification created
                                    }elseif($employerHasEmployee->getLegalFF()==0){//its new
                                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                        $utils = $this->get('app.symplifica_utils');
                                        $dAction="Bajar";
                                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                        $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                        $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                        $nCount++;//notification created
                                    }else{
                                        $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                                    }
                                }
                            }
                        }
                    }else{
                        $action->setActionStatus($this->getStatusByType('CTPE'));
                        $response .= "- - - - ERROR: DOCUMENTO CONTRATO NO ENCONTRADO - - - - <br>";
                        $docType = $this->getDocumentTypeByCode('CTR');
                        $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($person,$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$docType);
                        if($notifications== null){
                            if($employerHasEmployee->getLegalFF()==1){//its ancient
                                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                $utils = $this->get('app.symplifica_utils');
                                $dAction=null;
                                $dUrl=null;
                                $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                $flag=false;
                                /** @var Configuration $config */
                                foreach ($configurations as $config) {//searching if user need to generate a new contract
                                    if($config->getValue()=="PreLegal-SignedContract"){
                                        $flag=true;
                                        break;
                                    }
                                }
                                if(!$flag){//generating url to download contract
                                    $dAction="Bajar";
                                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                }
                                $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                $nCount++;//notification created
                            }elseif($employerHasEmployee->getLegalFF()==0){//its new
                                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                $utils = $this->get('app.symplifica_utils');
                                $dAction="Bajar";
                                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                $nCount++;//notification created
                            }else{
                                $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                            }
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
                                if($employerHasEmployee->getLegalFF()==1){//its ancient
                                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                    $utils = $this->get('app.symplifica_utils');
                                    $dAction=null;
                                    $dUrl=null;
                                    $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                    $flag=false;
                                    /** @var Configuration $config */
                                    foreach ($configurations as $config) {//searching if user need to generate a new contract
                                        if($config->getValue()=="PreLegal-SignedContract"){
                                            $flag=true;
                                            break;
                                        }
                                    }
                                    if(!$flag){//generating url to download contract
                                        $dAction="Bajar";
                                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                    }
                                    $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                    $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                    $nCount++;//notification created
                                }elseif($employerHasEmployee->getLegalFF()==0){//its new
                                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                    $utils = $this->get('app.symplifica_utils');
                                    $dAction="Bajar";
                                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                    $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                    $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                    $nCount++;//notification created
                                }else{
                                    $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                                }
                            }
                        }
                    }
                    if ($aCount != null) {//if there is a parameter for the id
                        $action->setIdAction($aCount);//setting the action id
                        $em->persist($action);//persisting the action
                        $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessary to force an id
                        $aCount++;//increment actionId Count
                    } else {
                        $em->persist($action);//persisting the action
                    }
                }else{//employerHasEmployee doesn't exist in SQL
                    $response .= "- - - - NO EXISTE SQL - - - -<br>";
                    $docType = $this->getDocumentTypeByCode('CTR');
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($person,$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$docType);
                    if($notifications== null){
                        $response .= "- - - - ERROR: DOCUMENTO CONTRATO NO ENCONTRADO - - - - <br>";
                        if($employerHasEmployee->getLegalFF()==1){//its ancient
                            $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                            $flag=false;
                            /** @var Configuration $config */
                            foreach ($configurations as $config) {//searching if user need to generate a new contract
                                if($config->getValue()=="PreLegal-SignedContract"){
                                    $flag=true;
                                    break;
                                }
                            }
                            if(!$flag){//generating url to download contract
                                $dAction="Bajar";
                                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                            }
                            $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                            $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                            $nCount++;//notification created
                        }elseif($employerHasEmployee->getLegalFF()==0){//its new
                            $utils = $this->get('app.symplifica_utils');
                            $dAction=null;
                            $dUrl=null;
                            $msj = "Aviso sobre el contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                            $url = $this->generateUrl("view_document_contract_state", array("idEHE"=>$employerHasEmployee->getIdEmployerHasEmployee()));
                            $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Ver",$dAction,$dUrl, $nCount);
                            $nCount++;
                        }else{
                            $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                        }
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
                            $response .= "- - - - ERROR: DOCUMENTO CONTRATO NO ENCONTRADO - - - - <br>";
                            if($employerHasEmployee->getLegalFF()==1){//its ancient
                                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                                $utils = $this->get('app.symplifica_utils');
                                $dAction=null;
                                $dUrl=null;
                                $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                                $flag=false;
                                /** @var Configuration $config */
                                foreach ($configurations as $config) {//searching if user need to generate a new contract
                                    if($config->getValue()=="PreLegal-SignedContract"){
                                        $flag=true;
                                        break;
                                    }
                                }
                                if(!$flag){//generating url to download contract
                                    $dAction="Bajar";
                                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getActiveContract()->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                                }
                                $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$employerHasEmployee->getActiveContract()->getIdContract(),'docCode'=>'CTR'));
                                $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                                $nCount++;//notification created
                            }elseif($employerHasEmployee->getLegalFF()==0){//its new
                                $utils = $this->get('app.symplifica_utils');
                                $dAction=null;
                                $dUrl=null;
                                $msj = "Aviso sobre el contrato de ". $utils->mb_capitalize(explode(" ",$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                                $url = $this->generateUrl("view_document_contract_state", array("idEHE"=>$employerHasEmployee->getIdEmployerHasEmployee()));
                                $this->createNotification($employer->getPersonPerson(), $msj, $url, $docType,"Ver",$dAction,$dUrl, $nCount);
                                $nCount++;
                            }else{
                                $response.= "- - - - ERROR: LEGAL FLAG - - - - <br>";
                            }
                        }
                    }
                }
                /** @var EmployeeHasEntity $employeeHasEntity */
                foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
                    if($employeeHasEntity->getState()==1){
                        /**
                         * ╔══════════════════════════════════════════════════╗
                         * ║ Action Upload Document RAD                       ║
                         * ╚══════════════════════════════════════════════════╝
                         */
                        $action = new Action();
                        $procedure->addAction($action);//adding the action to the procedure
                        $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                        $user->addAction($action);//adding the action to the user
                        $action->setActionTypeActionType($this->getActionByType('SDE'));//setting the actionType
                        $action->setActionStatus($this->getStatusByType('NEW'));
                        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
                        $action->setEmployeeEntity($employeeHasEntity);
                        $action->setUpdatedAt();
                        if ($aCount != null) {//if there is a parameter for the id
                            $action->setIdAction($aCount);//setting the action id
                            $em->persist($action);//persisting the action
                            $metadata = $em->getClassMetadata(get_class($action));//line of code necessaryy to force an id
                            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessary to force an id
                            $aCount++;//increment actionId Count
                        } else {
                            $em->persist($action);//persisting the action
                        }
                    }
                }
            }
        }
        return array('aCount'=>$aCount,'response'=>$response, 'nCount'=>$nCount);
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
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployerEmployee(User $user, RealProcedure $procedure, $aCount=null, $nCount=null){
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
                if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VER')){
                    $action->setActionStatus($this->getStatusByType('ERRO'));
                }else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to NEW
                }
            } else {//else info need to be checked by backoffice
                $action->setActionStatus($this->getStatusByType('ERRO'));//setting the Action Status to ERRO
            }
        }
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
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
                    if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VDDE')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                    if($notifications== null){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                            $nCount++;
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                        $nCount++;
                    }
                }
            }
        }
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setFinishedAt(null);
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        if($person->getActionsByActionType($this->getActionByType('VRT'))->first()){
            $action->setActionStatus($this->getStatusByType('CON'));
        }else{
            if ($employer->getPersonPerson()->getRutDocument()) {//if employer has RutDocument checking for existing media
                if ($employer->getPersonPerson()->getRutDocument()->getMediaMedia()) {
                    if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VRTE')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('RUT');
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                    if($notifications== null){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                        $nCount++;
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
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                            $nCount++;
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('RUT');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                        $nCount++;
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        if ($employer->getMandatoryDocument()) {//if employer has mandatoryDocument checking for existing media
            if ($employer->getMandatoryDocument()->getMediaMedia()) {
                if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VM')){
                    $action->setActionStatus($this->getStatusByType('ERRO'));
                }else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                //finding the notification to upload the document
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('MAND');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                        $nCount++;
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            //finding the notification to upload the document
            $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
            $docType = $this->getDocumentTypeByCode('MAND');
            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
            if($notifications== null){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                $dAction="Bajar";
                $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                $nCount++;
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
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl,$nCount);
                    $nCount++;
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
                $code = 'VENE';
            } elseif ($employerHasEntity->getState() == 1) {//subscribe entity
                $action->setActionTypeActionType($this->getActionByType('INE'));//setting actionType to validate entity
                $code = 'INE';
            }
            $action->setEmployerEntity($employerHasEntity);
            if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),$code)){
                $action->setActionStatus($this->getStatusByType('ERRO'));
            }else {
                $action->setActionStatus($this->getStatusByType('NEW'));//setting the action status to new
            }
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
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
        return array('aCount'=>$aCount,'response'=>$response, 'nCount'=>$nCount);
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
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployer(User $user,RealProcedure $procedure,$aCount=null, $nCount=null){
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
            $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
            if ($employer->getIdSqlSociety()) {//if the employer exist in SQL info must be correct
                if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VER')){
                    $action->setActionStatus($this->getStatusByType('ERRO'));
                }else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to NEW
                }
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
            $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
            if ($employer->getPersonPerson()->getDocumentDocument()) {//if employer has documentDocument checking for existing media
                if ($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                    if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VDDE')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                    if($notifications== null){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                            $nCount++;
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode($person->getDocumentType());
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
            $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
            if ($employer->getPersonPerson()->getRutDocument()) {//if employer has RutDocument checking for existing media
                if ($employer->getPersonPerson()->getRutDocument()->getMediaMedia()) {
                    if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VRTE')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('RUT');
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                    if($notifications== null){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction=null;
                        $dUrl=null;
                        $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                            $nCount++;
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: RUT NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('RUT');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications == null ){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employer->getPersonPerson()->getIdPerson(),'docCode'=>'RUT'));
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
            $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
            if ($employer->getMandatoryDocument()) {//if employer has mandatoryDocument checking for existing media
                if ($employer->getMandatoryDocument()->getMediaMedia()) {
                    if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),'VM')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                } else {//if media not found changing action status to error
                    $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                    //finding the notification to upload the document
                    $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                    $docType = $this->getDocumentTypeByCode('MAND');
                    $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                    if($notifications == null){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                        $dAction="Bajar";
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
                            $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                            $nCount++;
                        }
                    }
                }
            } else {//if document not found means document is pending
                $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
                //finding the notification to upload the document
                $response .= "- - - - ERROR: MANDATO NO ENCONTRADO - - - - <br>";
                $docType = $this->getDocumentTypeByCode('MAND');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$person,$docType);
                if($notifications == null ){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$employer->getPersonPerson()->getNames())[0]." ". $employer->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$employer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employer->getPersonPerson()->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                    $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
                        $this->createNotification($person, $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
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
                    $code = 'VENE';
                } elseif ($employerHasEntity->getState() == 1) {//subscribe entity
                    $action->setActionTypeActionType($this->getActionByType('INE'));//setting actionType to validate entity
                    $code = 'INE';
                }
                $action->setEmployerEntity($employerHasEntity);
                if($this->getErrorInLog($procedure->getUserUser(),$employer->getPersonPerson(),$code)){
                    $action->setActionStatus($this->getStatusByType('ERRO'));
                }else {
                    $action->setActionStatus($this->getStatusByType('NEW'));//setting the action status to new
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
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
        return array('aCount'=>$aCount,'response'=>$response,'nCount'=>$nCount);
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
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployee(User $user,RealProcedure $procedure,EmployerHasEmployee $employerHasEmployee,$atLeastOneFinished,$aCount=null, $nCount=null){
        $response='- - - - - CREANDO ACCIONES EMPLEADO - - - - -<br>';
        $employer = $employerHasEmployee->getEmployerEmployer();
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        if ($employerHasEmployee->getExistentSQL()==1) {//if the employee exist in SQL info must be correct
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

        } elseif ($employerHasEmployee->getState() > 4) {//else if the employee is finished info must have errors and need to be checked by backoffice
            $action->setActionStatus($this->getStatusByType('ERRO'));//setting the Action Status to Error
        } else {
            if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),'VEE')){
                $action->setActionStatus($this->getStatusByType('ERRO'));
            }else {
                $action->setActionStatus($this->getStatusByType('NEW'));//setting the Action Status to new
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
         * ║ Action validate employee Document                ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
        $user->addAction($action);//adding the action to the user
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VDD'));
        if ($employee->getPersonPerson()->getDocumentDocument()) {//if employee has documentDocument checking for existing media
            if ($employee->getPersonPerson()->getDocumentDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3 and $employerHasEmployee->getExistentSQL()==1) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),'VDD')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - - <br>";
                $docType = $this->getDocumentTypeByCode($employee->getPersonPerson()->getDocumentType());
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
                if($notifications == null ){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction=null;
                    $dUrl=null;
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employee->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
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
                        $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employee->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                        $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: DOCUMENTO DE IDENTIDAD NO ENCONTRADO - - - - - <br>";
            $docType = $this->getDocumentTypeByCode($employee->getPersonPerson()->getDocumentType());
            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
            if($notifications == null){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $dAction=null;
                $dUrl=null;
                $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employee->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                $nCount++;
            }else{
                $equal = false;
                /** @var Notification $notification */
                foreach ($notifications as $notification){
                    $exUrl = explode('/',$notification->getRelatedLink());
                    if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
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
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$employee->getPersonPerson()->getIdPerson(),'docCode'=>$docType->getDocCode()));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
         * ║ Action validate employee AuthLetter              ║
         * ╚══════════════════════════════════════════════════╝
         */
        $action = new Action();
        $procedure->addAction($action);//adding the action to the procedure
        $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
        $user->addAction($action);//adding the action to the user
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VCAT'));
        if ($employerHasEmployee->getAuthDocument()) {//if employerHasEmployee has authLetter checking for existing media
            if ($employerHasEmployee->getAuthDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3 and $employerHasEmployee->getExistentSQL()==1) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),'VCAT')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
                $docType = $this->getDocumentTypeByCode('CAS');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
                if($notifications == null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction="Bajar";
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                        $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                        $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
            $docType = $this->getDocumentTypeByCode('CAS');
            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
            if($notifications== null){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $dAction="Bajar";
                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                $nCount++;
            }else{
                $equal = false;
                /** @var Notification $notification */
                foreach ($notifications as $notification){
                    $exUrl = explode('/',$notification->getRelatedLink());
                    if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                        $equal = true;
                        $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                        $notification->activate();
                    }
                }
                if(!$equal){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
        /** @var EmployeeHasEntity $employeeHasEntity */
        foreach ($employee->getEntities() as $employeeHasEntity){
            if($employeeHasEntity->getState()!=-1){
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Employee entities begin                          ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $action = new Action();
                $procedure->addAction($action);//adding the action to the procedure
                $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
                $user->addAction($action);//adding the action to the user
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
                $action->setEmployeeEntity($employeeHasEntity);
                if($employeeHasEntity->getState()==0){
                    $action->setActionTypeActionType($this->getActionByType('VEN'));
                    $code = 'VEN';
                }elseif($employeeHasEntity->getState() == 1){
                    $action->setActionTypeActionType($this->getActionByType('IN'));
                    $code = 'IN';
                }
                if($employerHasEmployee->getExistentSQL() == 1){
                    if($employerHasEmployee->getState()>3){
                        $action->setActionStatus($this->getStatusByType('FIN'));
                    }else{
                        if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),$code)){
                            $action->setActionStatus($this->getStatusByType('ERRO'));
                        }else {
                            $action->setActionStatus($this->getStatusByType('NEW'));
                        }
                    }
                }else{
                    if($employerHasEmployee->getState()>4){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                        $response .= "- - - - - ERROR: EMPLEADO QUE NO EXISTE EN SQL Y ESTA TERMINADO - - - - - <br>";
                    }else{
                        if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),$code)){
                            $action->setActionStatus($this->getStatusByType('ERRO'));
                        }else {
                            $action->setActionStatus($this->getStatusByType('NEW'));
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

            }

        }
        return array('aCount'=>$aCount,'response'=>$response, 'atLeast'=>$atLeastOneFinished ,'nCount'=>$nCount);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createActionsEmployeeEmployee                ║
     * ║ Function that creates the actions for the employee    ║
     * ║ that has more than one employer                       ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param EmployerHasEmployee $employerHasEmployee      ║
     * ║  @param bool $atLeastOneFinished                      ║
     * ║  @param integer $aCount optional                      ║
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployeeEmployee(User $user,RealProcedure $procedure,EmployerHasEmployee $employerHasEmployee,$atLeastOneFinished,$aCount=null, $nCount=null){
        $response='- - - - - CREANDO ACCIONES EMPLEADO - - - - -<br>';
        $employee = $employerHasEmployee->getEmployeeEmployee();
        $employer = $employerHasEmployee->getEmployerEmployer();
        $em = $this->getDoctrine()->getManager();
        //Validate info must exist for this employee
        if($employee->getPersonPerson()->getActionsByActionType($this->getActionByType('VEE'))->isEmpty()){
            $response .= "- - - - - ERROR: EMPLEADO QUE YA ES EMPLEADO NO TIENE ACCION VALIDAR INFORMACION - - - - - <br>";
        }

        if($employerHasEmployee->getExistentSQL()){
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
        }
        //Validate document must exist for this employee
        if($employee->getPersonPerson()->getActionsByActionType($this->getActionByType('VDD'))->isEmpty()){
            $response .= "- - - - - ERROR: EMPLEADO QUE YA ES EMPLEADO NO TIENE ACCION VALIDAR DOCUMENTO - - - - - <br>";
        }
        //Creating action validate authletter for this employerHasEmployee relation
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VCAT'));
        if ($employerHasEmployee->getAuthDocument()) {//if employerHasEmployee has authLetter checking for existing media
            if ($employerHasEmployee->getAuthDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3 and $employerHasEmployee->getExistentSQL()==1) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),'VCAT')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
                $docType = $this->getDocumentTypeByCode('CAS');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction="Bajar";
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                        $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                        $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
            $docType = $this->getDocumentTypeByCode('CAS');
            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
            if($notifications== null){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $dAction="Bajar";
                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                $nCount++;
            }else{
                $equal = false;
                /** @var Notification $notification */
                foreach ($notifications as $notification){
                    $exUrl = explode('/',$notification->getRelatedLink());
                    if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                        $equal = true;
                        $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                        $notification->activate();
                    }
                }
                if(!$equal){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
        //validate and suscribe entities must exist for this employee
        if($employee->getEntities()->count() != $employee->getPersonPerson()->getActionsByActionType($this->getActionByType('VEN'))->count()+$employee->getPersonPerson()->getActionsByActionType($this->getActionByType('IN'))->count()){
            $response .= "- - - - - ERROR: EMPLEADO QUE YA ES EMPLEADO NO TIENE ACCIONES VALIDAR E INSCRIVIR ENTIDADES COMPLETAS - - - - - <br>";
        }
        return array('aCount'=>$aCount,'response'=>$response, 'atLeast'=>$atLeastOneFinished , 'nCount'=>$nCount);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createActionsEmployeeEmployer                ║
     * ║ Function that creates the actions for the employee    ║
     * ║ that is also a employer                               ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param User $user                                    ║
     * ║  @param RealProcedure $procedure                      ║
     * ║  @param EmployerHasEmployee $employerHasEmployee      ║
     * ║  @param bool $atLeastOneFinished                      ║
     * ║  @param integer $aCount optional                      ║
     * ║  @param integer $nCount optional                      ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return array                                        ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createActionsEmployeeEmployer(User $user,RealProcedure $procedure,EmployerHasEmployee $employerHasEmployee,$atLeastOneFinished,$aCount=null, $nCount=null){
        $response='- - - - - CREANDO ACCIONES EMPLEADO QUE ES EMPLEADOR - - - - -<br>';
        $employee = $employerHasEmployee->getEmployeeEmployee();
        $employer = $employerHasEmployee->getEmployerEmployer();
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
        $action->setActionStatus($this->getStatusByType('CON'));//setting the initial state consulting
        $action->setUpdatedAt();//setting the action updatedAt Date
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        if ($employerHasEmployee->getExistentSQL() == 1) {//if the employee exist in SQL info must be correct
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VDD'));
        $action->setActionStatus($this->getStatusByType('CON'));//setting the initial state consulting
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
        $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
        $action->setActionTypeActionType($this->getActionByType('VCAT'));
        if ($employerHasEmployee->getAuthDocument()) {//if employerHasEmployee has authLetter checking for existing media
            if ($employerHasEmployee->getAuthDocument()->getMediaMedia()) {
                if ($employerHasEmployee->getState() > 3 and $employerHasEmployee->getExistentSQL()==1) {
                    $action->setActionStatus($this->getStatusByType('FIN'));//if media found and state >3 employerHasEmployee finished set status to finish
                } else {
                    if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),'VCAT')){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                    }else {
                        $action->setActionStatus($this->getStatusByType('NEW'));//if media found setting the action status to new
                    }
                }
            } else {//if media not found changing action status to error
                $action->setActionStatus($this->getStatusByType('ERRO'));//if media not found setting action status to error
                $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
                $docType = $this->getDocumentTypeByCode('CAS');
                $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
                if($notifications== null){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
                }else{
                    $equal = false;
                    /** @var Notification $notification */
                    foreach ($notifications as $notification){
                        $exUrl = explode('/',$notification->getRelatedLink());
                        if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                            $equal = true;
                            $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                            $notification->activate();
                        }
                    }
                    if(!$equal){
                        $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                        $utils = $this->get('app.symplifica_utils');
                        $dAction="Bajar";
                        $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                        $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                        $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                        $nCount++;
                    }
                }
            }
        } else {//if document not found means document is pending
            $action->setActionStatus($this->getStatusByType('DCPE'));//changing the action status to document pending
            $response .= "- - - - - ERROR: CARTA DE AUTORIZACION NO ENCONTRADA - - - - - <br>";
            $docType = $this->getDocumentTypeByCode('CAS');
            $notifications = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employee->getPersonPerson(),$docType);
            if($notifications== null){
                $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                $utils = $this->get('app.symplifica_utils');
                $dAction="Bajar";
                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                $nCount++;
            }else{
                $equal = false;
                /** @var Notification $notification */
                foreach ($notifications as $notification){
                    $exUrl = explode('/',$notification->getRelatedLink());
                    if($employee->getPersonPerson()->getIdPerson()==$exUrl[4] and !$equal){
                        $equal = true;
                        $response .= "- - - - ACTIVANDO NOTIFICACION - - - - <br>";
                        $notification->activate();
                    }
                }
                if(!$equal){
                    $response .= "- - - - CREANDO NOTIFICACION - - - - <br>";
                    $utils = $this->get('app.symplifica_utils');
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$employee->getPersonPerson()->getNames())[0]." ". $employee->getPersonPerson()->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $this->createNotification($employerHasEmployee->getEmployerEmployer()->getPersonPerson(), $msj, $url, $docType,"Subir",$dAction,$dUrl, $nCount);
                    $nCount++;
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
        /** @var EmployeeHasEntity $employeeHasEntity */
        foreach ($employee->getEntities() as $employeeHasEntity){
            if($employeeHasEntity->getState()!=-1){
                /**
                 * ╔══════════════════════════════════════════════════╗
                 * ║ Employee entities begin                          ║
                 * ╚══════════════════════════════════════════════════╝
                 */
                $action = new Action();
                $procedure->addAction($action);//adding the action to the procedure
                $employee->getPersonPerson()->addAction($action);//adding the action to the employeePerson
                $user->addAction($action);//adding the action to the user
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($this->getDateCreatedInLog($user));//setting the Action createrAt Date
                if($employeeHasEntity->getState()==0){
                    $action->setActionTypeActionType($this->getActionByType('VEN'));
                    $code= 'VEN';
                }elseif($employeeHasEntity->getState() == 1){
                    $action->setActionTypeActionType($this->getActionByType('IN'));
                    $code = 'IN';
                }
                if($employerHasEmployee->getExistentSQL() == 1){
                    if($employerHasEmployee->getState()>3 ){
                        $action->setActionStatus($this->getStatusByType('FIN'));
                    }else{
                        if($this->getErrorInLog($procedure->getUserUser(),$employee->getPersonPerson(),$code)){
                            $action->setActionStatus($this->getStatusByType('ERRO'));
                        }else {
                            $action->setActionStatus($this->getStatusByType('NEW'));
                        }
                    }
                }else{
                    if($employerHasEmployee->getState()>4){
                        $action->setActionStatus($this->getStatusByType('ERRO'));
                        $response .= "- - - - - ERROR: EMPLEADO QUE NO EXISTE EN SQL Y ESTA TERMINADO - - - - - <br>";
                    }else{
                        $action->setActionStatus($this->getStatusByType('NEW'));
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

            }

        }
        return array('aCount'=>$aCount,'response'=>$response, 'atLeast'=>$atLeastOneFinished ,'nCount'=>$nCount);
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function getProcedureByType                           ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param string $code of the ProcedureType to find     ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return ProcedureType                                ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function getProcedureByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=> $code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function getStatusByType                              ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param string $code of the statusType to find        ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return ProcedureType                                ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function getStatusByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findOneBy(array('code'=> $code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getActionTypeByActionTypeCode                        ║
     * ║ Returns the StatusType that match the code send by parameter  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return object|\RocketSeller\TwoPickBundle\Entity\StatusTypes║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getActionStatusByStatusCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findOneBy(array('code'=>$code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getDocumentTypeByCode                                ║
     * ║ Returns the DocumentType that match the code send by parameter║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param $docCode                                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return DocumentType                                         ║
     * ╚═══════════════════════════════════════════════════════════════╝
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


    /**
     * ╔═════════════════════════════════════════════════════════════════╗
     * ║ Function getDateCreatedInLog                                    ║
     * ║ Returns the date finded in log for the procedure creation       ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @param User $user                                              ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @return DateTime                                               ║
     * ╚═════════════════════════════════════════════════════════════════╝
     */
    protected function getDateCreatedInLog(User $user){
        $em = $this->getDoctrine()->getManager();
        /** @var Log $log */
        $log = $em->getRepository("RocketSellerTwoPickBundle:Log")->findOneBy(array("userUser"=>$user,'tableName'=>'RealProcedure'));
        if($log){
            $strDate = $log->getPreviousData();
            $date = DateTime::createFromFormat("Y-m-d H:i:s",$strDate);
        }else{
            $date = new DateTime();
        }
        return $date;
    }

    /**
     * ╔═════════════════════════════════════════════════════════════════╗
     * ║ Function getErrorInLog                                          ║
     * ║ Returns true if the action has error                            ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @param User $user                                              ║
     * ║  @param Person $person                                          ║
     * ║  @param string $code                                            ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @return bool                                                   ║
     * ╚═════════════════════════════════════════════════════════════════╝
     */
    protected function getErrorInLog(User $user, Person $person, $code){
        $em = $this->getDoctrine()->getManager();
        /** @var Log $log */
        $log = $em->getRepository("RocketSellerTwoPickBundle:Log")->findOneBy(array("userUser"=>$user,'tableName'=>'Action'));
        if($log){
            $strEx = explode(':',$log->getMessage());
            if(intval($strEx[1])==$person->getIdPerson()){
                switch ($strEx[0]){
                    case 'VER':
                        //validar informacion
                        if($code == 'VER'){
                            return true;
                        }else{
                            return false;
                        }
                        break;
                    case 'VEE':
                        if($code == 'VEE'){
                            return true;
                        }else{
                            return false;
                        }
                        //validar informacion empleado
                        break;
                    case 'VEN':
                        if($code == 'VENE' or $code == 'VEN' ){
                            return true;
                        }else{
                            return false;
                        }
                        //validar entidad
                        break;
                    case 'VDC':
                        //validar documentos
                        if($code == 'VDDE' or $code == 'VRTE' or $code == 'VRCE' or $code == 'VDD' or $code == 'VRT' or $code == 'VCAT' ){
                            return true;
                        }else{
                            return false;
                        }
                        break;
                    case 'IN':
                        //inscribir entidad
                        if($code == 'INE' or $code == 'IN' ){
                            return true;
                        }else{
                            return false;
                        }
                        break;
                    case 'VM':
                        //validar mandato
                        if($code == 'VM' ){
                            return true;
                        }else{
                            return false;
                        }
                        break;
                    case 'VC':
                        //validar contrato
                        if($code == 'VC' ){
                            return true;
                        }else{
                            return false;
                        }
                        break;
                }
            }
        }
        return false;
    }


    /**
     * ╔═══════════════════════════════════════════════════════╗
     * ║ Function createNotification                           ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @param Person $person notification owner             ║
     * ║  @param string $descripcion text to display           ║
     * ║  @param string $url related link string               ║
     * ║  @param DocumentType $documentType                    ║
     * ║  @param string $action                                ║
     * ║  @param string $dAction                               ║
     * ║  @param string $dUrl related download link            ║
     * ║  @param integer $nCount notification id to force it   ║
     * ╠═══════════════════════════════════════════════════════╣
     * ║  @return ProcedureType                                ║
     * ╚═══════════════════════════════════════════════════════╝
     */
    protected function createNotification($person, $descripcion, $url, $documentType = null, $action = "Subir", $dAction=null, $dUrl=null, $nCount=null)
    {
        $notification = new Notification();
        $notification->setPersonPerson($person);
        $notification->activate();
        $notification->setDocumentTypeDocumentType($documentType);
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $notification->setDownloadAction($dAction);
        $notification->setDownloadLink($dUrl);
        $em = $this->getDoctrine()->getManager();
        if ($nCount != null) {//if theres an id parameter for the procedure
            $notification->setId($nCount);//setting the id to the realProcedure
            $em->persist($notification);;//persisting the procedure
            $metadata = $em->getClassMetadata(get_class($notification));//line of code necessaryy to force an id
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
        } else {
            $em->persist($notification);;//persisting the procedure
        }
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateProcedureStatus                             ║
     * ║ Calculates de procedure Status if needed                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ║  @param bool $force                                           ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer 0 if noting change 1 if something change     ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateProcedureStatus($procedure,$force = false)
    {
        if($procedure->getStatusUpdatedAt()==null or $procedure->getActionChangedAt()==null or $procedure->getStatusUpdatedAt()<$procedure->getActionChangedAt() or $force){
            $today = new DateTime();
            $type = $procedure->getProcedureTypeProcedureType()->getCode();
            if($procedure->getActionChangedAt()==null){
                $procedure->setActionChangedAt($today);
            }
            switch ($type){
                case 'REE':
                    /** @var Employer $emmployer */
                    $emmployer = $procedure->getEmployerEmployer();
                    if($emmployer->getIdSqlSociety()==null){
                        $procedure->setProcedureStatus($this->getActionStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                        break;
                    }
                    //if employer have at least one active employerHasEmployee
                    if(count($emmployer->getActiveEmployerHasEmployees())>0 and count($procedure->getAction())>0){
                        $error=false;
                        /** @var Action $actionError */
                        $actionError = null;
                        $corrected = false;
                        /** @var Action $actionCorrected */
                        $actionCorrected = null;
                        $begin = false;
                        $finish = true;
                        $dcpe = false;
                        /** @var Action $action */
                        foreach ($this->getInfoEmployerActions($procedure) as $action) {
                            if($action->getActionStatusCode()=='DCPE'){
                                $dcpe = true;
                            }
                        }
                        if($dcpe){
                            $procedure->setProcedureStatus($this->getActionStatusByStatusCode('DCPE'));
                            $procedure->setStatusUpdatedAt($today);
                            break;
                        }
                        $atLeastOne = false;
                        $ehes = $procedure->getEmployerEmployer()->getEmployerHasEmployees();
                        /** @var Action $action */
                        /** @var EmployerHasEmployee $ehe */
                        foreach ($ehes as $ehe) {
                            $dcpe = false;
                            foreach ($procedure->getActionsByEmployerHasEmployee($ehe) as $action) {
                                if($action->getActionStatusCode()=='DCPE'){
                                    $dcpe = true;
                                    break;
                                }
                            }
                            if($ehe->getExistentSQL()!=1){
                                $finish=false;
                            }
                            if(!$dcpe){
                                $atLeastOne = true;
                                break;
                            }
                        }
                        if($atLeastOne){
                            foreach ($procedure->getAction() as $action) {
                                if($action->getActionStatusCode()=='ERRO'){
                                    $error = true;
                                    if($actionError==null or $action->getErrorAt()<$actionError->getErrorAt()){
                                        $actionError = $action;
                                    }
                                }
                                if($action->getActionStatusCode()=='CORT'){
                                    $corrected = true;
                                    if($actionCorrected==null or $action->getCorrectedAt()<$actionCorrected->getCorrectedAt()){
                                        $actionCorrected=$action;
                                    }
                                }
                                if($action->getActionStatusCode()=='FIN' and !$begin){
                                    $begin = true;
                                }
                                if($action->getActionStatusCode()!='FIN' and $finish){
                                    $finish = false;
                                }
                            }
                            if($error and !$corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getProcedureStatusCode()!='ERRO'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('ERRO'));
                                }
                            }
                            if($error and $corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CORT'));
                                }
                            }
                            if($corrected and !$error){
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CORT'));
                                }
                            }
                            if(!$corrected and !$error){
                                if($begin and !$finish){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('STRT'));
                                }elseif($finish and $procedure->getProcedureStatuscode()!='FIN'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('FIN'));
                                    foreach ($procedure->getEmployerEmployer()->getEmployerHasEmployees() as $ehe){
                                        $ehe->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                                        if($ehe->getAllDocsReadyMessageAt()==null)
                                        $ehe->setAllEmployeeDocsReadyAt(new DateTime());
                                        if($ehe->getDateFinished()==null)
                                        $ehe->setDateFinished(new DateTime());
                                        $this->getDoctrine()->getManager()->persist($ehe);
                                    }
                                }else{
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('NEW'));
                                }
                            }
                            $procedure->setStatusUpdatedAt($today);
                        }else{
                            $procedure->setProcedureStatus($this->getActionStatusByStatusCode('DCPE'));
                            $procedure->setStatusUpdatedAt($today);
                        }
                    }else{
                        $procedure->setProcedureStatus($this->getActionStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                    }
                    break;
                case 'PPL':
                    break;
                case 'VAC':
                    /** @var Employer $emmployer */
                    $emmployer = $procedure->getEmployerEmployer();
                    $oneFinished = false;
                    /** @var EmployerHasEmployee $ehe */
                    foreach ($emmployer->getActiveEmployerHasEmployees() as $ehe) {
                        if($ehe->getExistentSQL()==1){
                            $oneFinished = true;
                            break;
                        }
                    }
                    if(!$oneFinished){
                        $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                        break;
                    }
                    //if employer have at least one active employerHasEmployee
                    if(count($emmployer->getActiveEmployerHasEmployees())>0 and count($procedure->getAction())>0){
                        $error=false;
                        /** @var Action $actionError */
                        $actionError = null;
                        $corrected = false;
                        /** @var Action $actionCorrected */
                        $actionCorrected = null;
                        $begin = false;
                        $finish = true;
                        $dcpe = false;
                        /** @var Action $action */
                        foreach ($procedure->getAction() as $action) {
                            if($action->getActionStatusCode()=='CTPE'){
                                $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CTPE'));
                                $procedure->setStatusUpdatedAt($today);
                                $dcpe = true;
                                break;
                            }
                            if($action->getActionStatusCode()=='ERRO'){
                                $error = true;
                                if($actionError==null or $action->getErrorAt()<$actionError->getErrorAt()){
                                    $actionError = $action;
                                }
                            }
                            if($action->getActionStatusCode()=='CORT'){
                                $corrected = true;
                                if($actionCorrected==null or $action->getCorrectedAt()<$actionCorrected->getCorrectedAt()){
                                    $actionCorrected=$action;
                                }
                            }
                            if($action->getActionStatusCode()=='FIN' and !$begin){
                                $begin = true;
                            }
                            if($action->getActionStatusCode()!='FIN' and $finish){
                                $finish = false;
                            }

                        }
                        if(!$dcpe){
                            if($error and !$corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getProcedureStatusCode()!='ERRO'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('ERRO'));
                                }
                            }
                            if($error and $corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CORT'));
                                }
                            }
                            if($corrected and !$error){
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CORT'));
                                }
                            }
                            if(!$corrected and !$error){
                                if($begin and !$finish){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('STRT'));
                                }elseif($finish){
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('CTVA'));
                                }else{
                                    $procedure->setProcedureStatus($this->getActionStatusByStatusCode('NEW'));
                                }
                            }
                            $procedure->setStatusUpdatedAt($today);
                        }
                    }else{
                        $procedure->setProcedureStatus($this->getActionStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                    }
                    break;
                case 'SPL':

                    break;
            }
            return 1;
        }
        return 0;

    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateProcedurePriority                           ║
     * ║ Calculates de procedure priority if needed                    ║
     * ║ return 1 if procedure changed 0 if not                        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ║  @param bool $force                                           ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer                                              ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateProcedurePriority($procedure,$force = false)
    {
        if($procedure->getPriorityUpdatedAt()<$procedure->getActionChangedAt() or $procedure->getPriorityUpdatedAt() == null or $force){
            $today = new DateTime();
            //if procedure already reached maxtime priority is 3
            if($procedure->getMaxTimeReached()==1){
                $priority = 3;
            }else{
                $toMuchTime = false;
                switch($procedure->getProcedureTypeProcedureType()->getCode()){
                    case 'REE':
                        //checking the first error for the procedure
                        if($procedure->getFirstErrorAt()!=null and $procedure->getProcedureStatusCode()!= 'DCPE'){
                            //calculating the time between procedure creation and procedure first error
                            $tempo = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFirstErrorAt()->format("Y-m-d")), array('_format' => 'json'));
                            if ($tempo->getStatusCode() == 200) {
                                $days = json_decode($tempo->getContent(),true)["days"];
                                //if time in days > 3 time is exceeded and all the actions will have priority 2
                                if(intval($days) >= 3){
                                    //flag to much time to notice time was exceeded
                                    $toMuchTime = true;
                                }
                            }
                        }
                        //if time was not exceeded
                        if(!$toMuchTime){
                            $code = $procedure->getProcedureStatusCode();
                            switch ($code){
                                case 'DIS': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'DCPE': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'ERRO': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CORT'://if status is corrected
                                    //dateStart is correctedAt
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'NEW'://if status is new
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'STRT'://if status is newstarted
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;

                                case 'FIN'://is status is finished
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                            }
                            if ($response->getStatusCode() == 200) {
                                $days = json_decode($response->getContent(),true)["days"];
                                if($days == 0){
                                    $priority = 0;
                                }elseif ($days==1 or $days ==2){
                                    $priority = 1;
                                }elseif ($days==3){
                                    $priority = 2;
                                }elseif ($days >3){
                                    $priority = 3;
                                }
                            }
                        }else{
                            //setting permanently max priority
                            $priority = 3;
                        }
                        break;
                    case 'PPL':
                        break;
                    case 'VAC':
                        /** @var RealProcedure $REEProcedure */
                        $REEProcedure = $procedure->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('REE'))->first();
                        if($REEProcedure->getFinishedAt()!= null){
                            $stardate = $REEProcedure->getFinishedAt();
                            if($procedure->getBackOfficeDate()!=$stardate){
                                $procedure->setBackOfficeDate($stardate);
                            }
                        }else{
                            $stardate = $today;
                        }
                        //checking the first error for the procedure
                        if($procedure->getFirstErrorAt()!=null and $procedure->getProcedureStatusCode()!= 'CTPE'){
                            if($stardate<$procedure->getFirstErrorAt()){
                                //calculating the time between procedure creation and procedure first error
                                $tempo = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFirstErrorAt()->format("Y-m-d")), array('_format' => 'json'));
                                if ($tempo->getStatusCode() == 200) {
                                    $days = json_decode($tempo->getContent(),true)["days"];
                                    //if time in days > 3 time is exceeded and all the actions will have priority 2
                                    if($days = 5){
                                        //flag to much time to notice time was exceeded
                                        $toMuchTime = true;
                                    }
                                }
                            }
                        }
                        //if time was not exceeded
                        if(!$toMuchTime){
                            $code = $procedure->getProcedureStatusCode();
                            switch ($code){
                                case 'DIS': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CTPE': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'ERRO': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CORT'://if status is corrected
                                    //dateStart is correctedAt
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'NEW'://if status is new
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'STRT'://if status is started
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'CTVA'://is status is finished
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                            }
                            if ($response->getStatusCode() == 200) {
                                $days = json_decode($response->getContent(),true)["days"];
                                if($days == 0){
                                    $priority = 0;
                                }elseif ($days==1 or $days ==2){
                                    $priority = 1;
                                }elseif ($days==3){
                                    $priority = 2;
                                }elseif ($days >3){
                                    $priority = 3;
                                }
                            }
                        }else{
                            //setting permanently max priority
                            $priority = 3;
                        }
                        break;
                    case 'SPL':
                        break;
                }
            }
            if($procedure->getPriority()!=$priority){
                $procedure->setPriority($priority);
            }
            $procedure->setPriorityUpdatedAt($today);
            return 1;
        }
        return 0;
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

    /**
     * Corrects finish date in backoffice
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Corrects finishDate in backoffice.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="start_index", nullable=false, strict=true, description="start_index.")
     * @RequestParam(name="end_index", nullable=false, strict=true, description="end_index.")
     *
     * @return View
     */
    public function postCorrectFinishDateAction(ParamFetcher $paramFetcher){
        $em = $this->getDoctrine()->getManager();
        $response = 'START'.'<br>----------------------------------------------------------------------------------------------------------------------------------------------------------------------<br>';
        $start = $paramFetcher->get('start_index');
        $end = $paramFetcher->get('end_index');
        $count = 1;
        $users = $em->getRepository('RocketSellerTwoPickBundle:User')->findAll();
        /** @var User $user */
        foreach ($users as $user) {
            if($count >= $start and $count <= $end){
                if($user->getPersonPerson() and $user->getPersonPerson()->getEmployer()){
                    $EHES = $user->getPersonPerson()->getEmployer()->getActiveEmployerHasEmployees();
                    if($EHES->count()>0){
                        $response.= 'EMPLOYER: '.$user->getPersonPerson()->getEmployer()->getIdEmployer().' '.$user->getPersonPerson()->getFullName().' EHE_COUNT: '.$EHES->count().'<br>';
                        /** @var EmployerHasEmployee $ehe */
                        foreach ($EHES as $ehe) {
                            $response.= 'EHE_ID: '.$ehe->getIdEmployerHasEmployee();
                            if($ehe->getExistentSQL()==1){
                                $response.= ' SQL_STATE: TRUE';
                                $ehe->setState(4);
                                $em->persist($ehe);
                            }elseif($ehe->getState()<2){
                                $ehe->setDocumentStatusType(null);
                                $ehe->setDocumentStatus(null);
                                $em->persist($ehe);
                            }

                            if($ehe->getActiveContract()){
                                /** @var Contract $contract */
                                $contract = $ehe->getActiveContract();
                            }
                            if($ehe->getDateFinished())
                                $response.= ' EHE_FINISH_DATE: '.$ehe->getDateFinished()->format('d-m-Y H:i:s');
                        }
                        $response.='<br>----------------------------------------------------------------------------------------------------------------------------------------------------------------------<br>';
                    }
                }
                if($count % 30 == 0){
                    $response .= '<br>GROUP FROM ' . ( $count - 30 ) . ' TO ' . $count . ' FLUSHED' . '<br><br>';
                    $em->flush();
                    $em->clear();
                }
                if($count % 30 != 0 and $count == $end){
                    $response .= '<br>GROUP FROM ' . ( $count - ( $count % 30 ) ) . ' TO ' . ( $count ) . ' FLUSHED' . '<br><br>';
                    $em->flush();
                    $em->clear();
                }
            }
            $count++;
        }
        if($count <= $end and $count % 30 != 0 ){
            $response .= '<br>GROUP FROM ' . ( $count - ( $count % 30 ) ) . ' TO ' . ( $count - 1 ) . ' FLUSHED' . '<br><br>';
            $em->flush();
            $em->clear();
        }
        $view = View::create($response);
        $view->setStatusCode(200);
        return $view;
    }
}
?>