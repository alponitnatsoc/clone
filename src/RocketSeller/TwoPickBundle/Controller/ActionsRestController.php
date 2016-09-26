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
use RocketSeller\TwoPickBundle\Entity\Employee;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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

        /** @var  ClassMetadataInfo $metadata */
        $metadata = $em->getClassMetadata(get_class($statusType));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
        $response .= "StatusTypes Creados<br>";

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
        $response = "Comienza<br>";
        $em = $this->getDoctrine()->getManager();

        $response.= "Eliminando anteriores actions<br>";
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:Action")->findAll() as $action){
            $em->remove($action);
        }
        $em->flush();
        $response.= "Actions eliminadas<br>";

        $response.= "Eliminando anteriores realProcedures<br>";
        $em = $this->getDoctrine()->getManager();
        foreach ($em->getRepository("RocketSellerTwoPickBundle:RealProcedure")->findAll() as $procedure){
            $em->remove($procedure);
        }
        $em->flush();
        $response.= "realProcedures eliminados<br>";

        $response.= "Eliminando anteriores actionErrors<br>";
        foreach ($em->getRepository("RocketSellerTwoPickBundle:ActionError")->findAll() as $errors){
            $em->remove($errors);
        }
        $em->flush();
        $response.= "Todos los errores eliminados<br>";

        $users = $em->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        $count = 0;
        $pCount = 1;
        $aCount = 1;
        /** @var User $user */
        foreach ($users as $user){
            try{
                if($user->getStatus()>=2){
                    $response .="Creando tramites del usuario ".$count.": ".$user->getPersonPerson()->getFullName()."<br>";
                    $count++;
                    if($user->getRealProcedure()->isEmpty()){
                        $result= $this->createInitialProcedures($user,$pCount, $aCount);
                        $response.=$result['response'];
                        $pCount = $result['pCount'];
                        $aCount = $result['aCount'];
                    }else{
                        $response.="El usuario: ".$user->getPersonPerson()->getFullName()."ya tiene tramites<br>";
                    }
                }else{
                    $response .="El usuario: ".$user->getPersonPerson()->getFullName()." no ha terminado 3 de 3<br>";
                }
            }catch (Exception $e){
                $response .= "Error en el usuario: " . $user->getPersonPerson()->getFullName()
                    . "<br><strong>Error: </strong>" . $e->getCode() . " " . $e->getMessage() . "<br>";
            }
        }


        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }


    /**
     * function that creates initial procedures in the specified counts
     * @param User $user
     * @param integer $count
     * @param integer $pCount
     * @param integer $aCount
     * @return array
     */
    private function createInitialProcedures(User $user, $pCount=null, $aCount=null){
        $response = '';
        //getting the employer
        if($user->getPersonPerson()->getEmployer()){
            $employer =$user->getPersonPerson()->getEmployer();
            $result = $this->createProcedure($user,$employer,$pCount,$aCount);
            $response.=$result['response'];
            $pCount = $result['pCount'];
            $aCount = $result['aCount'];
        }else{
            $response .="No se encontro el empleador para este usuario<br>";
        }
        return array('response'=>$response, 'aCount'=>$aCount , 'pCount'=>$pCount);
    }

    /**
     * @param User $user
     * @param Employer $employer
     * @param integer $pCount
     * @param integer $aCount
     * @return array
     */
    private function createProcedure(User $user,Employer $employer,$pCount=null,$aCount=null){
        $response = '';
        $em = $this->getDoctrine()->getManager();
        $today=new DateTime();
        $procedure = new RealProcedure();
        $procedure->setProcedureTypeProcedureType($this->getProcedureByType('REE'));
        $employer->addRealProcedure($procedure);
        $procedure->setCreatedAt($user->getLastPayDate());
        $procedure->setStatusUpdatedAt($today);
        $procedure->setProcedureStatus($this->getStatusByType('DIS'));
        $procedure->setActionChangedAt($today);
        $procedure->setPriority(0);
        $user->addRealProcedure($procedure);
        if($pCount != null){
            $procedure->setIdProcedure($pCount);
            $em->persist($procedure);
            $metadata = $em->getClassMetadata(get_class($procedure));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
            $pCount++;
        }else{
            $em->persist($procedure);
            $em->flush();
        }
        $response .="Procedure registro creado<br>";
        //if employer person has actions it means its probably an employee too
        if($employer->getPersonPerson()->getAction()->isEmpty()){
            //action validate employer info
            $action = new Action();
            $action->setActionTypeActionType($this->getActionByType('VER'));
            $action->setActionStatus($this->getStatusByType('DIS'));
            $action->setUpdatedAt($today);
            $procedure->addAction($action);
            $employer->getPersonPerson()->addAction($action);
            $user->addAction($action);
            if($employer->getIdSqlSociety() and $employer->getIdHighTech()){
                $response .="Existe en hightech y en SQL ".$employer->getIdEmployer()." <br>";
                $action->setActionStatus($this->getStatusByType('FIN'));
            }else{
                $action->setActionStatus($this->getStatusByType('ERRO'));
            }
            if($aCount != null){
                $action->setIdAction($aCount);
                $em->persist($action);
                $metadata = $em->getClassMetadata(get_class($action));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
                $aCount++;
            }else{
                $em->persist($action);
                $em->flush();
            }
            $response .="Accion validar info creada<br>";
            //persisting entities
            $em->persist($procedure);
            $em->persist($employer);
            $em->persist($user);
            $em->flush();
            //action validate document employer
            $action = new Action();
            $action->setActionTypeActionType($this->getActionByType('VDDE'));
            $action->setActionStatus($this->getStatusByType('DIS'));
            $action->setUpdatedAt($today);
            $procedure->addAction($action);
            $employer->getPersonPerson()->addAction($action);
            $user->addAction($action);
            if($employer->getPersonPerson()->getDocumentDocument()){
                if($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()){
                    $action->setActionStatus($this->getStatusByType('NEW'));
                }else{
                    $action->setActionStatus($this->getStatusByType('ERRO'));
                }
            }
            if($aCount != null){
                $action->setIdAction($aCount);
                $em->persist($action);
                $metadata = $em->getClassMetadata(get_class($action));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
                $aCount++;
            }else{
                $em->persist($action);
                $em->flush();
            }
            $response .="Accion validar documento creada<br>";
            //persisting entities
            $em->persist($procedure);
            $em->persist($employer);
            $em->persist($user);
            $em->flush();

        }else{
            //checking employer is also an employee
            if($employer->getPersonPerson()->getEmployee()){
                $response .="encontre un empleadoempleador<br>";
            }else{
                $response .="ERROR: el usuario ".$employer->getPersonPerson()->getFullName()." ya tiene acciones creadas<br>";
            }
        }

        return array('pCount'=>$pCount,'aCount'=>$aCount,'response'=>$response);
    }

    /**
     * @param $code
     * @return ProcedureType
     */
    public function getProcedureByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=> $code));
    }

    /**
     * @param $code
     * @return StatusTypes
     */
    public function getStatusByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findOneBy(array('code'=> $code));
    }

    /**
     * @param $code
     * @return ActionType
     */
    public function getActionByType($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=> $code));
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