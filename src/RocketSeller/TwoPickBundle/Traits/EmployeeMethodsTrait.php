<?php

namespace RocketSeller\TwoPickBundle\Traits;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\Common\Persistence\ObjectRepository;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Notification;
use Symfony\Component\Config\Definition\Exception\Exception;

trait EmployeeMethodsTrait
{

    protected function getEmployeeDetails($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        $employee = $repository->find($id);

        return $employee;
    }

    protected function getEmployeeEps($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "EPS") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function getEmployeeAfp($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "AFP") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function getEmployeeFces($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "FCES") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    /**
     * Función que cuenta el numero de documentos pendientes para el empleador
     * @param Person $person Persona asociada al empleador
     * @return int Numero de documentos pendientes
     */
    protected function employerDocumentsReady(Person $person)
    {
        //Initialising de pending docs counter
        $pendingDocs = 0;

        if($person->getDocumentDocument()){
            try{
                $media = $person->getDocumentDocument()->getMediaMedia();
                if($media==null)
                    $pendingDocs++;
            }catch(Exception $e){
                $pendingDocs++;
            }
        }else{
            $pendingDocs++;
        }

        if($person->getRutDocument()){
            try{
                $media = $person->getRutDocument()->getMediaMedia();
                if($media==null)
                    $pendingDocs++;
            }catch(Exception $e){
                $pendingDocs++;
            }
        }else{
            $pendingDocs++;
        }

        if($person->getEmployer()->getMandatoryDocument()){
            try{
                $media = $person->getEmployer()->getMandatoryDocument()->getMediaMedia();
                if($media==null)
                    $pendingDocs++;
            }catch(Exception $e){
                $pendingDocs++;
            }
        }else{
            $pendingDocs++;
        }

        //returning the count of pending docs MAX 3
        return $pendingDocs;
    }

    /**
     * Función que verifica los documentos subidos para el empleado
     * los estados del contrato pueden ser 1 cuando existe un documento contrato para el contrato activo o 0 si no existe
     * @param EmployerHasEmployee $eHE employerHasEmployee del que se van a obtener los documentos
     * @return int Numero de documentos pendientes
     */
    protected function employeeDocumentsReady(EmployerHasEmployee $eHE)
    {
        //initialising de pending docs counter for employee
        $ePendingDocs=0;
        if($eHE->getAuthDocument()){
            try{
                $media = $eHE->getAuthDocument()->getMediaMedia();
                if($media==null)
                    $ePendingDocs++;
            }catch(Exception $e){
                $ePendingDocs++;
            }
        }else{
            $ePendingDocs++;
        }

        if($eHE->getEmployeeEmployee()->getPersonPerson()->getDocumentDocument()){
            try{
                $media = $eHE->getEmployeeEmployee()->getPersonPerson()->getDocumentDocument()->getMediaMedia();
                if($media==null)
                    $ePendingDocs++;
            }catch(Exception $e){
                $ePendingDocs++;
            }
        }else{
            $ePendingDocs++;
        }
        //returning employee pending docs
        return $ePendingDocs;
    }

    /**
     * Función que retorna el estado de validación de documentos de un empleador por backoffice
     * 1 - validado
     * 0 - Por Validar
     * -1 - Error de Validación
     * 2 - estado desconocido
     *
     * @param User $user Usuario del empleado del que se consulta el estado de validacion de documentos
     * @return int
     */
    protected function employerDocumentsValidated(User $user)
    {
        //Getting the person from the user
        /** @var Person $person */
        $person = $user->getPersonPerson();
        //Getting the employer associated to the person
        /** @var Employer $employer */
        $employer = $person->getEmployer();
        //Finding the procedure type with code REE Registro empleador y empleados
        /** @var ProcedureType $procedureType */
        $procedureType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ProcedureType')->findOneBy(array(
            'code'  =>'REE',
        ));
        //finding the procedure for the user employer combination
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findOneBy(array(
            'userUser'          => $user,
            'employerEmployer'  => $employer,
            'procedureTypeProcedureType' => $procedureType,
        ));
        //Finding the action type with code VDC validar Documentos
        /** @var ActionType $actionType */
        $actionType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'  =>'VDC',
        ));
        //Finding the action type with code VM validar Mandato
        /** @var ActionType $actionType */
        $actionType2 = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'  =>'VM',
        ));
        //Finding the action for the actual employer
        /** @var Action $action */
        $action = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'                  =>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'              =>$person,
            'actionTypeActionType'      =>$actionType,
        ));
        //Finding the action for the actual employer
        /** @var Action $action */
        $action2 = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'                  =>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'              =>$person,
            'actionTypeActionType'      =>$actionType2,
        ));
        //Getting the completion status for the action
        $status = $action->getStatus();
        //Getting the completion status for the action
        $status2 = $action2->getStatus();

        if($status == 'Completado' and $status2 == 'Completado'){
            //return state Validated
            return 1;
        }elseif($status == 'Error' or $status2 == 'Error'){
            //return state Error
            return -1;
        }elseif($status == 'Nuevo' or $status2=='Nuevo'){
            //return state Pending Validation
            return 0;
        }else{
            //return state Unknown State
            return 2;
        }
    }

    /**
     * Función que retorna el estado de validación de información de un empleado por backoffice
     * 1 - validado
     * 0 - Por Validar
     * -1 - Error de Validación
     * 2 - estado desconocido
     *
     * @param User $user Usuario del empleado del que se consulta el estado de validacion
     * @param EmployerHasEmployee $eHE employerHasEmployee del empleado del que se consulta el estado de validacion
     * @return int
     */
    protected function employeeValidated(User $user, EmployerHasEmployee $eHE)
    {
        if($eHE->getState()<3){
            return 2;
        }
        $employer = $user->getPersonPerson()->getEmployer();
        $employee = $eHE->getEmployeeEmployee();
        $person = $employee->getPersonPerson();
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findOneBy(array(
            'userUser' => $user,
            'employerEmployer' => $employer,
        ));
        $actionType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'=>'VER',
        ));
        /** @var Action $action */
        $action = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'=>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'=>$person,
            'actionTypeActionType'=>$actionType,
        ));
        $status=$action->getStatus();

        if($status=='Completado'){
            return 1;
        }elseif($status=='Error'){
            return -1;
        }elseif($status=='Nuevo'){
            return 0;
        }else{
            return 2;
        }
    }

    /**
     * Función que retorna el estado de validación de documentos de un empleado por backoffice
     * 1 - validado
     * 0 - Por Validar
     * -1 - Error de Validación
     * 2 - estado desconocido
     *
     * @param User $user Usuario del empleado del que se consulta el estado de validacion de documentos
     * @param EmployerHasEmployee $eHE employerHasEmployee del empleado del que se consulta el estado de validacion de documetos
     * @return int
     */
    protected function employeeDocumentsValidated(User $user, EmployerHasEmployee $eHE)
    {
        if($eHE->getState()<3){
            return 2;
        }
        $employer = $user->getPersonPerson()->getEmployer();
        $employee = $eHE->getEmployeeEmployee();
        $person = $employee->getPersonPerson();
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findOneBy(array(
            'userUser' => $user,
            'employerEmployer' => $employer,
        ));
        $actionType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'=>'VDC',
        ));
        /** @var Action $action */
        $action = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'=>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'=>$person,
            'actionTypeActionType'=>$actionType,
        ));
        $status=$action->getStatus();

        if($status=='Completado'){
            return 1;
        }elseif($status=='Error'){
            return -1;
        }elseif($status=='Nuevo'){
            return 0;
        }else{
            return 2;
        }
    }

    /**
     * Función que retorna el estado de validación del contrato de un empleado por backoffice
     * 1 - validado
     * 0 - Por Validar
     * -1 - Error de Validación
     * 2 - estado desconocido
     *
     * @param User $user Usuario del empleado del que se consulta el estado de validacion de documentos
     * @param EmployerHasEmployee $eHE employerHasEmployee del empleado del que se consulta el estado de validacion de documetos
     * @return int
     */
    protected function employeeContractValidated(User $user, EmployerHasEmployee $eHE)
    {
        if($eHE->getState()<3){
            return 2;
        }
        $employer = $user->getPersonPerson()->getEmployer();
        $employee = $eHE->getEmployeeEmployee();
        $person = $employee->getPersonPerson();
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findOneBy(array(
            'userUser' => $user,
            'employerEmployer' => $employer,
        ));
        $actionType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'=>'VC',
        ));
        /** @var Action $action */
        $action = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'=>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'=>$person,
            'actionTypeActionType'=>$actionType,
        ));
        $status=$action->getStatus();

        if($status=='Completado'){
            return 1;
        }elseif($status=='Error'){
            return -1;
        }elseif($status=='Nuevo'){
            return 0;
        }else{
            return 2;
        }
    }

    /**
     * @param User $user usuario al que se le desea actualizar el estado de documentos de todos sus empleados
     * @return array arreglo de estado de documentos para cada empleado
     */
    protected function allDocumentsReady(User $user){

        //getting the person
        $person = $user->getPersonPerson();
        //getting all the employees
        $eHEs = $person->getEmployer()->getEmployerHasEmployees();
        //crossing the emmployees
        /** @var EmployerHasEmployee $eHE */
        foreach($eHEs as $eHE){
            //if the employee is payed
            if($eHE->getState()>=3){
                //if employee state is not payed or all documents pending changing the document state to all documents pending
                if($eHE->getDocumentStatus()==-2 or $eHE->getDocumentStatus()==-1)
                    $eHE->setDocumentStatus(-1);
                //get the actual document state of the employerHasEmployee must be at least -1
                $case = $eHE->getDocumentStatus();
                //if the antique state is all documents pending verifying that documents are still pending
                if($case == -1){
                    //getting the amount of documents pending for the employer
                    $pend = $this->employerDocumentsReady($person);
                    //getting the amount of documents pending for the employee
                    $ePend = $this->employeeDocumentsReady($eHE);
                    //if employer and employee documents pending are greater than 0 state is -1 all docs pending
                    if($ePend>0 and $pend >0){
                        $eHE->setDocumentStatus(-1);
                    //if employee pending docs greater than 0 but employer pending docs equal 0 state is 0 employee documents pending
                    }elseif($ePend>0 and $pend ==0){
                        $eHE->setDocumentStatus(0);
                    //if employee pending docs equal to 0 but employer pending docs greater than 0 state is 1 employer documents pending
                    }elseif($ePend==0 and $pend >0){
                        $eHE->setDocumentStatus(1);
                    //if both employer and employee pending docs are equal to 0 state is 2 message docs ready
                    }elseif($pend==0 and $ePend==0){
                        $eHE->setDocumentStatus(2);
                        $this->checkSubscription($eHE);
                        $eHE->setDateDocumentsUploaded(new DateTime());
                    }
                //if the antique state is employee documents pending checking if employee documents are still pending
                }elseif($case == 0){
                    //getting the amount of documents pending for the employee
                    $ePend = $this->employeeDocumentsReady($eHE);
                    //if amount of pending docs for employee equal to 0 state is 2 message docs ready
                    if($ePend['pending']==0){
                        $eHE->setDocumentStatus(2);
                        $this->checkSubscription($eHE);
                        $eHE->setDateDocumentsUploaded(new DateTime());
                    //if amount of pending docs for employee is greater than 0 state remains in 0 employee documents pending
                    }else{
                        $eHE->setDocumentStatus(0);
                    }
                //if the antique state is employer documents pending checking if employer documents are still pending
                }elseif($case == 1){
                    //getting the amount of documents pending for the employer
                    $pend = $this->employerDocumentsReady($person);
                    //if amount of pending docs for employer equal to 0 state is 2 message docs ready
                    if($pend==0){
                        $eHE->setDocumentStatus(2);
                        $this->checkSubscription($eHE);
                        $eHE->setDateDocumentsUploaded(new DateTime());
                    //if amount of pending docs for employer is greater than 0 state remains in 1 employer documents pending
                    }else{
                        $eHE->setDocumentStatus(1);
                    }
                }elseif($case > 1 and $case < 11){
                    $docValid = $this->employerDocumentsValidated($user);
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($docValid == 2 or $eDocsValid == 2 ){
                        break;
                    }
                    if($docValid == 0 and $eDocsValid == 0){
                        $eHE->setDocumentStatus(3);
                    }elseif($docValid == 1 and $eDocsValid == 0){
                        $eHE->setDocumentStatus(4);
                    }elseif($docValid == 0 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(5);
                    }elseif($docValid == -1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(6);
                    }elseif($docValid == 1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }elseif($docValid == -1 and $eDocsValid == 0){
                        $eHE->setDocumentStatus(8);
                    }elseif($docValid == 0 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(9);
                    }elseif($docValid == -1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(12);
                    }elseif($docValid == 1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }
                }elseif($case == 11){
                    $ePend = $this->employeeDocumentsReady($eHE);
                    if($ePend['contract']==0){
                        $eHE->setDocumentStatus(14);
                    }else{
                        $eHE->setDocumentStatus(15);
                    }
                }elseif($case == 12){
                    $eHE->setDocumentStatus(10);
                }elseif($case == 13){
                    $eHE->setDocumentStatus(16);
                }elseif($case == 14){
                    $ePend = $this->employeeDocumentsReady($eHE);
                    if($ePend['contract']==0){
                        $eHE->setDocumentStatus(14);
                    }else{
                        $eHE->setDocumentStatus(15);
                    }
                }elseif($case == 15){
                    if($eHE->getState()>4){
                        $eHE->setDocumentStatus(13);
                    }
                }
            }else{
                $eHE->setDocumentStatus(-2);
            }
            $response[] = ['idEHE'=>$eHE->getIdEmployerHasEmployee(),'docStatus'=>$eHE->getDocumentStatus()];
            $em = $this->getDoctrine()->getManager();
            $em->persist($eHE);
            $em->flush();
        }
        return $response;
    }


    /**
     * function to generate all the employer and employee notifications 
     * @param User $user
     * @return bool
     */
    protected function validateDocuments(User $user)
    {
        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $employerHasEmployee = $employerHasEmployees->first();
        $this->validateDocumentsEmployer($user, $employerHasEmployee->getEmployerEmployer());
        do {
            if ($employerHasEmployee->getState() < 2)
                continue;
            $employee = $employerHasEmployee->getEmployeeEmployee();
            $this->validateDocumentsEmployee($user, $employee);
            //$this->validateEntitiesEmployee($user, $employee);
        } while ($employerHasEmployee = $employerHasEmployees->next());

        return true;
    }

    protected function validateDocumentsEmployee(User $user, Employee $realEmployee)
    {
        // = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $employer = $user->getPersonPerson()->getEmployer();
        $eePerson = $realEmployee->getPersonPerson();
        // obtaining the employerHasEmployee for the relation User employee
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array(
            'employerEmployer' => $employer,
            'employeeEmployee' => $realEmployee,
        ));
        if($employerHasEmployee->getState()<2){
            return false;
        }
        // obtaining the active contract for the employerHasEmployee
        /** @var Contract $contract */
        $contract = $em->getRepository('RocketSellerTwoPickBundle:Contract')->findOneBy(array(
            'employerHasEmployeeEmployerHasEmployee' => $employerHasEmployee,
            'state' => 1
        ));

        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        //array of employeeDocs
        $docs = array('CC' => false, 'CTR' => false,'CAS'=>false);
        if($eePerson->getDocumentDocument()){
            $docs['CC']=true;
        }
        if($employerHasEmployee->getAuthDocument()){
            $docs['CAS']=true;
        }
        if($contract->getDocumentDocument()){
            $docs['CTR']=true;
        }
        foreach ($docs as $type => $status) {
            // {{ path('download_document', {'id': employees[0].personPerson.idPerson , 'idDocument':doc.idDocument}) }}
            if (!$docs[$type]) {
                $msj = "";
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$type));
                $dAction=null;
                $nAction="Subir";
                $dUrl=null;
                if ($type == 'CC') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$eePerson->getIdPerson(),'docCode'=>'CC'));
                }elseif ($type == 'CAS') {
                    $msj = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                }elseif ($type == 'CTR') {
                    if($employerHasEmployee->getLegalFF()==1){
                        $configurations=$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                        $flag=false;
                        /** @var Configuration $config */
                        foreach ($configurations as $config) {
                            if($config->getValue()=="PreLegal-SignedContract"){
                                $flag=true;
                                break;
                            }
                        }
                        if(!$flag){
                            $dAction="Bajar";
                            $dUrl = $this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
                        }
                        $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                        $url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId'=>$contract->getIdContract(),'docCode'=>'CTR'));
                    }else{
                        $msj = "Aviso sobre el contrato de ". $utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                        $nAction="Ver";
                    }

                }
                if($nAction=="Ver"){
                    $url = $this->generateUrl("view_document_contract_state", array("idEHE"=>$employerHasEmployee->getIdEmployerHasEmployee()));
                }
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url, $documentType,$nAction,$dAction,$dUrl);
            }
        }
    }

    protected function validateEntitiesEmployee(User $user, Employee $realEmployee)
    {
        //$user = $this->getUser();
        //$em = $this->getDoctrine()->getManager();
        $personEmployee = $realEmployee->getPersonPerson();
        //$employeeHasEntityRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity');
        //$entities_b = $employeeHasEntityRepo->findByEmployeeEmployee($realEmployee);
        //if (gettype($entities_b) != "array") {
        //    $entities[] = $entities_b;
        //} else {
        //    $entities = $entities_b;
        //}
        //foreach ($entities as $key => $value) {
        $msj = "Subir documentos de " .explode(" ",$personEmployee->getNames())[0]." ". $personEmployee->getLastName1(). " para afiliarlo a las entidades.";
        $url = $this->generateUrl("show_documents", array('id' => $personEmployee->getIdPerson()));
        $this->createNotification($user->getPersonPerson(), $msj, $url, null, "Ir");
        //}
    }

    protected function validateDocumentsEmployer(User $user, Employer $realEmployer)
    {
        //$user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $person = $realEmployer->getPersonPerson();
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        $docs = array('CC' => false, 'RUT' => false, 'MAND' => false);
        if($realEmployer->getMandatoryDocument()){
            $docs['MAND']=true;
        }
        if($person->getDocumentDocument()){
            $docs['CC']=true;
        }
        if($person->getRutDocument()){
            $docs['RUT']=true;
        }
        foreach ($docs as $type => $status) {
            if (!$docs[$type]) {
                $msj = "";
                $documentType= $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$type));
                $dAction=null;
                $dUrl=null;
                if ($type == 'CC') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>'CC'));
                } elseif ($type == 'RUT') {
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>'RUT'));
                } elseif ($type == 'MAND'){
                    $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$realEmployer->getIdEmployer(),'docCode'=>'MAND'));
                    $dUrl = $this->generateUrl("download_documents", array('id' => $person->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                    $dAction="Bajar";
                }
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url, $documentType,"Subir",$dAction,$dUrl);
            }
        }
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
     * @param EmployerHasEmployee $eHE
     */
    private function checkSubscription(EmployerHasEmployee $eHE){
        $personEmployer = $eHE->getEmployerEmployer()->getPersonPerson();
        /** @var ObjectRepository $userRepo */
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $realUser */
        $realUser = $userRepo->findOneBy(array('personPerson'=>$personEmployer));
        $dateToday = new DateTime();
        /** @var User $user */
        $user = $realUser;
        $effectiveDate = $user->getLastPayDate();
        $isFreeMonths = $user->getIsFree();
        if($isFreeMonths==0){
            //we prorat the subscrition to the end of the month
            $realToPay=new PurchaseOrders();
            $days = intval($dateToday->format("t"))-intval($dateToday->format("d"));
            $contracts = $eHE->getContracts();
            /** @var Contract $contract */
            foreach ($contracts as $contract) {
                if($contract->getState()==1){
                    $actualDays=$contract->getWorkableDaysMonth();
                    /** @var ObjectRepository $productRepo */
                    $productRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
                    if ($actualDays < 10) {
                        /** @var Product $PS1 */
                        $PS = $productRepo->findOneBy(array("simpleName" => "PS1"));
                    } elseif ($actualDays <= 19) {
                        /** @var Product $PS2 */
                        $PS = $productRepo->findOneBy(array("simpleName" => "PS2"));
                    } else {
                        /** @var Product $PS3 */
                        $PS = $productRepo->findOneBy(array("simpleName" => "PS3"));
                    }
                    $procesingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));

                    $symplificaPOD = new PurchaseOrdersDescription();
                    $symplificaPOD->setDescription("Subscripción Symplifica de ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName());
                    $symplificaPOD->setValue(round((($PS->getPrice() * (1 + $PS->getTaxTax()->getValue()) )/30)*$days,0));
                    $symplificaPOD->setProductProduct($PS);
                    $symplificaPOD->setPurchaseOrdersStatus($procesingStatus);

                    $realToPay->addPurchaseOrderDescription($symplificaPOD);
                    $realToPay->setPurchaseOrdersStatus($procesingStatus);
                    $user->addPurchaseOrder($realToPay);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                }
            }
        }

    }

}
