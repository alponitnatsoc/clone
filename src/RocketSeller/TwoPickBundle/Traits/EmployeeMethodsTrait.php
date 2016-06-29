<?php

namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Notification;

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

    protected function employerDocumentsReady(Person $person)
    {
        $em = $this->getDoctrine()->getManager();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);
        $docs = array('Cedula' => false, 'Rut' => false, 'Mandato' => false);
        $pendingDocs = 0;
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $pendingDocs++;
            }
        }
        return $pendingDocs;
    }

    protected function employeeDocumentsReady(EmployerHasEmployee $eHE)
    {
        $em = $this->getDoctrine()->getManager();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $ePerson=$eHE->getEmployeeEmployee()->getPersonPerson();
        $eDocuments= $documentsRepo->findByPersonPerson($ePerson);
        $eDocs = array('Cedula' => false,'Contrato'=>false,'Carta autorización Symplifica'=>false);
        $ePendingDocs=0;
        $contract=0;
        foreach ($eDocs as $type => $status) {
            foreach ($eDocuments as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $eDocs[$type] = true;
                    break;
                }
            }
            if (!$eDocs[$type]) {
                if($type!='Contrato'){
                    $ePendingDocs++;
                }else{
                    $contract=1;
                }
            }
        }
        return array('pending'=>$ePendingDocs,'contract'=>$contract);
    }

    /**
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
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();
        /** @var RealProcedure $procedure */
        $procedure = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:RealProcedure')->findOneBy(array(
            'userUser'          => $user,
            'employerEmployer'  => $employer,
        ));
        $actionType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ActionType')->findOneBy(array(
            'code'  =>'VDC',
        ));
        /** @var Action $action */
        $action = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Action')->findOneBy(array(
            'userUser'                  =>$user,
            'realProcedureRealProcedure'=>$procedure,
            'personPerson'              =>$person,
            'actionTypeActionType'      =>$actionType,
        ));
        $status = $action->getStatus();

        if($status == 'Completado'){
            return 1;
        }elseif($status == 'Error'){
            return -1;
        }elseif($status == 'Nuevo'){
            return 0;
        }else{
            return 2;
        }
    }
    /**
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
     * @param User $user usuario al que se le desea actualizar el estado de documentos de todos sus empleados
     * @return array arreglo de estado de documentos para cada empleado
     */
    protected function allDocumentsReady(User $user){

        $person = $user->getPersonPerson();
        $eHEs = $person->getEmployer()->getEmployerHasEmployees();
        /** @var EmployerHasEmployee $eHE */
        foreach($eHEs as $eHE){
            switch ($eHE->getDocumentStatus()){
                case -1:
                    $pend = $this->employerDocumentsReady($person);
                    $ePend = $this->employeeDocumentsReady($eHE);
                    if($ePend['pending']!=0 and $pend !=0){
                        $eHE->setDocumentStatus(-1);
                    }elseif($ePend['pending']!=0 and $pend ==0){
                        $eHE->setDocumentStatus(0);
                    }elseif($ePend['pending']==0 and $pend !=0){
                        $eHE->setDocumentStatus(1);
                    }elseif($pend==0 and $ePend['pending']==0){
                        $eHE->setDocumentStatus(2);
                    }
                    break;
                case 0:
                    $ePend = $this->employeeDocumentsReady($eHE);
                    if($ePend['pending']==0){
                        $eHE->setDocumentStatus(2);
                    }else{
                        $eHE->setDocumentStatus(0);
                    }
                    break;
                case 1:
                    $pend = $this->employerDocumentsReady($person);
                    if($pend==0){
                        $eHE->setDocumentStatus(2);
                    }else{
                        $eHE->setDocumentStatus(1);
                    }
                    break;
                case 2:
                    $eHE->setDocumentStatus(3);
                    break;
                case 3:
                    $docValid = $this->employerDocumentsValidated($user);
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
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
                        $eHE->setDocumentStatus(10);
                    }elseif($docValid == 1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }
                    break;
                case 4:
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($eDocsValid == 0){
                        $eHE->setDocumentStatus(4);
                    }elseif($eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }elseif($eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }
                    break;
                case 5:
                    $docValid = $this->employerDocumentsValidated($user);
                    if($docValid == 0 ){
                        $eHE->setDocumentStatus(5);
                    }elseif($docValid == 1 ){
                        $eHE->setDocumentStatus(11);
                    }elseif($docValid == -1 ){
                        $eHE->setDocumentStatus(6);
                    }
                    break;
                case 6:
                    $docValid = $this->employerDocumentsValidated($user);
                    if($docValid == 1 ){
                        $eHE->setDocumentStatus(11);
                    }elseif($docValid == -1 ){
                        $eHE->setDocumentStatus(6);
                    }
                    break;
                case 7:
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }elseif($eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }
                    break;
                case 8:
                    $docValid = $this->employerDocumentsValidated($user);
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($docValid == 1 and $eDocsValid == 0){
                        $eHE->setDocumentStatus(4);
                    }elseif($docValid == -1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(6);
                    }elseif($docValid == 1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }elseif($docValid == -1 and $eDocsValid == 0){
                        $eHE->setDocumentStatus(8);
                    }elseif($docValid == -1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(10);
                    }elseif($docValid == 1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }
                    break;
                case 9:
                    $docValid = $this->employerDocumentsValidated($user);
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($docValid == 0 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(5);
                    }elseif($docValid == -1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(6);
                    }elseif($docValid == 1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }elseif($docValid == 0 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(9);
                    }elseif($docValid == -1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(10);
                    }elseif($docValid == 1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }
                    break;
                case 10:
                    $docValid = $this->employerDocumentsValidated($user);
                    $eDocsValid = $this->employeeDocumentsValidated($user,$eHE);
                    if($docValid == -1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(6);
                    }elseif($docValid == 1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(7);
                    }elseif($docValid == -1 and $eDocsValid == -1){
                        $eHE->setDocumentStatus(10);
                    }elseif($docValid == 1 and $eDocsValid == 1){
                        $eHE->setDocumentStatus(11);
                    }
                    break;
                case 11:
                    $eHE->setDocumentStatus(13);
                    break;
                case 12:
                    $eHE->setDocumentStatus(10);
                    break;
                case 13:
                    $eHE->setDocumentStatus(14);
                    break;
                case 14:
                    $ePend = $this->employeeDocumentsReady($eHE);
                    if($ePend['contract']==0){
                        $eHE->setDocumentStatus(14);
                    }else{
                        $eHE->setDocumentStatus(15);
                    }
                    break;
                case 15:
                    $eHE->setDocumentStatus(16);
                    break;
                case 16:
                    break;
            }
            $response[] = ['idEHE'=>$eHE->getIdEmployerHasEmployee(),'docStatus'=>$eHE->getDocumentStatus()];
            $em = $this->getDoctrine()->getManager();
            $em->persist($eHE);
            $em->flush();
        }
        return $response;
    }


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
        $person = $realEmployee->getPersonPerson();

        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array(
            'employerEmployer' => $employer,
            'employeeEmployee' => $realEmployee,
            'state' => 3
        ));
        /** @var Contract $contract */
        $contract = $em->getRepository('RocketSellerTwoPickBundle:Contract')->findOneBy(array(
            'employerHasEmployeeEmployerHasEmployee' => $employerHasEmployee,
            'state' => 3
        ));
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        $docs = array('Cedula' => false, 'Contrato' => false,'Carta autorización Symplifica'=>false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            // {{ path('download_document', {'id': employees[0].personPerson.idPerson , 'idDocument':doc.idDocument}) }}
            if (!$docs[$type]) {
                $msj = "";
                $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
                $dAction=null;
                $nAction="Subir";
                $dUrl=null;
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'Cedula';
                } elseif ($type == 'Contrato') {
                    $documentType = 'Contrato';
                    if($employerHasEmployee->getLegalFF()==1){
                        $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    }else{
                        $msj = "Aviso sobre el contrato de ". $utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                        $nAction="Ver";
                    }

                } elseif ($type == 'Carta autorización Symplifica') {
                    $documentType = 'Carta autorización Symplifica';
                    $msj = "Subir copia de la Carta autorización Symplifica de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
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
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        $docs = array('Cedula' => false, 'Rut' => false, 'Mandato' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
                $dAction=null;
                $dUrl=null;
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'Cedula';
                } elseif ($type == 'Rut') {
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'Rut';
                } elseif ($type == 'Mandato'){
                  $documentType = 'Mandato';
                  $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                  $dUrl = $this->generateUrl("download_documents", array('id' => $person->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                  $dAction="Bajar";
                }
                $documentType = $documentTypeRepo->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
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

}
