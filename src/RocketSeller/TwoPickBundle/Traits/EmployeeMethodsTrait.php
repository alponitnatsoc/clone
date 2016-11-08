<?php

namespace RocketSeller\TwoPickBundle\Traits;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Campaign;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\UserHasCampaign;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\Date;

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
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║ Function getDocumentStatusByCode                                      ║
     * ║ Returns the DocumentStatus that match the code send by parameter      ║
     * ╠═══════════════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                                  ║
     * ╠═══════════════════════════════════════════════════════════════════════╣
     * ║  @return object|\RocketSeller\TwoPickBundle\Entity\DocumentStatusType ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    protected function getDocumentStatusByCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentStatusType")->findOneBy(array('documentStatusCode'=>$code));
    }

    /**
     * ╔═════════════════════════════════════════════════════════════════╗
     * ║ Function getProcedureTypeByCode                                 ║
     * ║ Returns the ProcedureType that match the code send by parameter ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                            ║
     * ╠═════════════════════════════════════════════════════════════════╣
     * ║  @return object|\RocketSeller\TwoPickBundle\Entity\ProcedureType║
     * ╚═════════════════════════════════════════════════════════════════╝
     */
    protected function getProcedureTypeByCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ProcedureType")->findOneBy(array('code'=>$code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getActionTypeByActionTypeCode                        ║
     * ║ Returns the ActionType that match the code send by parameter  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return object|\RocketSeller\TwoPickBundle\Entity\ActionType ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getActionTypeByActionTypeCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>$code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getStatusByStatusCode                                ║
     * ║ Returns the StatusType that match the code send by parameter  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return object|\RocketSeller\TwoPickBundle\Entity\StatusTypes║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getStatusByStatusCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:StatusTypes")->findOneBy(array('code'=>$code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getNotificationByPersonAndDocumentType               ║
     * ║ Returns the notification that match the parameters            ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Person $owner                                         ║
     * ║  @param Person $person                                        ║
     * ║  @param DocumentType $docType                                 ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return Notification                                         ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getNotificationByPersonAndOwnerAndDocumentType($owner,$person,$docType){
        $notifications = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification")->findBy(array('personPerson'=>$owner,'documentTypeDocumentType'=>$docType));
        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            $strex = explode('/',$notification->getRelatedLink());
            switch ($strex[3]){
                case 'Person':
                    if(intval($strex[4])==$person->getIdPerson())
                        return $notification;
                    break;
                case 'EmployerHasEmployee':
                    $ehe = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array('employeeEmployee'=>$person->getEmployee(),'employerEmployer'=>$owner->getEmployer()));
                    if($ehe and intval($strex[4])==$ehe->getIdEmployerHasEmployee())
                        return $notification;
                    break;
                case 'Employer':
                    if($owner->getEmployer()->getIdEmployer()==intval($strex[4]))
                        return $notification;
                    break;
                case 'Contract':
                    /** @var EmployerHasEmployee $ehe */
                    $ehe = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array('employeeEmployee'=>$person->getEmployee(),'employerEmployer'=>$owner->getEmployer()));
                    if($ehe and $ehe->getActiveContract() and $ehe->getActiveContract()->getIdContract()==intval($strex[4]))
                        return $notification;
                    break;
            }
        }
        return null;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getDocumentTypeByCode                                ║
     * ║ Returns the documentType that match the code send by parameter║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return DocumentType                                         ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getDocumentTypeByCode($code){
        return $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array('docCode'=>$code));
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function createNotificationByDocType                          ║
     * ║ Returns the notification                                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Person $owner                                         ║
     * ║  @param Person $person                                        ║
     * ║  @param DocumentType $docType                                 ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return Notification                                         ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function createNotificationByDocType($owner,$person,$docType){
        $em=$this->getDoctrine()->getManager();
        $id = $em->getRepository("RocketSellerTwoPickBundle:Notification")->createQueryBuilder('p')->select('MAX(p.id)')->getQuery()->getResult()[0][1]+1;
        $utils = $this->get('app.symplifica_utils');
        switch ($docType->getDocCode()){
            case 'CC':
                $dAction=null;
                $dUrl=null;
                $descripcion = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>$docType->getDocCode()));
                $action = "subir";
                break;
            case 'CE':
                $dAction=null;
                $dUrl=null;
                $descripcion = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>$docType->getDocCode()));
                $action = "subir";
                break;
            case 'PASAPORTE':
                $dAction=null;
                $dUrl=null;
                $descripcion = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>$docType->getDocCode()));
                $action = "subir";
                break;
            case 'TI':
                $dAction=null;
                $dUrl=null;
                $descripcion = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>$docType->getDocCode()));
                $action = "subir";
                break;
            case 'RUT':
                $dAction=null;
                $dUrl=null;
                $action = "subir";
                $descripcion = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>'RUT'));
                break;
            case 'MAND':
                $action = "subir";
                $descripcion = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Employer','entityId'=>$person->getEmployer()->getIdEmployer(),'docCode'=>'MAND'));
                $dUrl = $this->generateUrl("download_documents", array('id' => $person->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                $dAction="Bajar";
                break;
            case 'CAS':
                $employerHasEmployee = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array("employeeEmployee"=>$person->getEmployee(),"employerEmployer"=>$owner->getEmployer()));
                $dAction="Bajar";
                $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                $action = "subir";
                $descripcion = "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'EmployerHasEmployee','entityId'=>$employerHasEmployee->getIdEmployerHasEmployee(),'docCode'=>'CAS'));
                break;
        }
        $notification = new Notification();
        $notification->setPersonPerson($owner);
        $notification->setStatus(0);
        $notification->setDocumentTypeDocumentType($docType);
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $notification->setDownloadAction($dAction);
        $notification->setDownloadLink($dUrl);
        $em = $this->getDoctrine()->getManager();
        $notification->setId($id);//setting the id to the realProcedure
        $em->persist($notification);;//persisting the procedure
        $metadata = $em->getClassMetadata(get_class($notification));//line of code necessaryy to force an id
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);//line of code necessaryy to force an id
        $em->flush();
        return $notification;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getInfoEmployerActions                               ║
     * ║ Returns the array whit the info and document actions of the   ║
     * ║ Employer                                                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return array                                                ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getInfoEmployerActions($procedure)
    {
        return array(
            '0' => $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VER'))->first(),
            '1' => $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VDDE'))->first(),
            '2' => $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VRTE'))->first(),
            '3' => $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VM'))->first()
        );
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function checkEmployerInfoActions                             ║
     * ║ Returns array with:                                           ║
     * ║ bool error true when at least one info action has error       ║
     * ║ DateTime firstErrorAt when error with the errorDate           ║
     * ║ integer idActionError when error with the ActionErrorid       ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return array                                                ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function checkEmployerInfoActions($procedure)
    {
        $actions = $this->getInfoEmployerActions($procedure);
        $firstError = null;
        $tempAction = null;
        $finish = true;
        $pending = false;
        $today = new DateTime();
        /** @var Action $action */
        foreach ($actions as $action) {
            if($action->getActionStatusCode()!= 'FIN')
                $finish = false;
            if($action->getActionStatusCode()=='DCPE')
                $pending=true;
            if($firstError == null and $action->getErrorAt()!= null){
                $firstError = $action->getErrorAt();
                $tempAction = $action;
            }elseif($firstError != null and $action->getErrorAt()!=null){
                if($action->getErrorAt()<$firstError){
                    $firstError = $action->getErrorAt();
                    $tempAction = $action;
                }
            }
        }
        if(!$pending){
            $procedure->getEmployerEmployer()->setAllDocsReadyAt($today);
            $this->getDoctrine()->getManager()->persist($procedure->getEmployerEmployer());
            $this->getDoctrine()->getManager()->flush();
        }
        if ($firstError != null){
            if($procedure->getEmployerEmployer()->getInfoErrorAt()==null){
                if($this->getUser()){
                    $log = new Log($this->getUser(),'Employer','infoErrorAt',$procedure->getEmployerEmployer()->getIdEmployer(),null,$today->format('d-m-Y H:i:s'),'Backoffice encontro errores al validar la información de un empleador');
                    $this->getDoctrine()->getManager()->persist($log);
                }
                $procedure->getEmployerEmployer()->setInfoErrorAt($today);
                $this->getDoctrine()->getManager()->persist($procedure);
                $this->getDoctrine()->getManager()->flush();
            }
            if(!$finish){
                return array('error'=>true,'firstErrorAt'=>$firstError,'idActionError'=>$tempAction->getIdAction(),'finish'=>false,'pending'=>$pending);
            }else{
                if($procedure->getEmployerEmployer()->getInfoValidatedAt() == null){
                    if($this->getUser()){
                        $log = new Log($this->getUser(),'Employer','infoValidatedAt',$procedure->getEmployerEmployer()->getIdEmployer(),null,$today->format('d-m-Y H:i:s'),'Backoffice terminó de validar la información de un empleador');
                        $this->getDoctrine()->getManager()->persist($log);
                    }
                    $procedure->getEmployerEmployer()->setInfoValidatedAt($today);
                    $this->getDoctrine()->getManager()->persist($procedure);
                    $this->getDoctrine()->getManager()->flush();
                }
                return array('error'=>false,'firstErrorAt'=>$firstError,'idActionError'=>$tempAction->getIdAction(),'finish'=>true,'pending'=>$pending);
            }
        }elseif($finish){
            if($procedure->getEmployerEmployer()->getInfoValidatedAt() == null){
                if($this->getUser()){
                    $log = new Log($this->getUser(),'Employer','infoValidatedAt',$procedure->getEmployerEmployer()->getIdEmployer(),null,$today->format('d-m-Y H:i:s'),'Backoffice terminó de validar la información de un empleador');
                    $this->getDoctrine()->getManager()->persist($log);
                }
                $procedure->getEmployerEmployer()->setInfoValidatedAt($today);
                $this->getDoctrine()->getManager()->persist($procedure);
                $this->getDoctrine()->getManager()->flush();
            }
            return array('error'=>false,'finish'=>true,'pending'=>$pending);
        }
        return array('error'=>false,'finish'=>false, 'pending'=>$pending);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateEmployerDocStatus                           ║
     * ║ calculates employerDocStatus                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Employer $employer                                    ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateEmployerDocStatus($employer)
    {
        /** @var ObjectManager $em */
        $em =$this->getDoctrine()->getManager();
        /** @var RealProcedure $procedure */
        $procedure = $employer->getRealProcedureByProcedureTypeType($this->getProcedureTypeByCode('REE'))->first();
        /** @var Action $vdde */
        $vdde = $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VDDE'))->first();
        /** @var Action $vrte */
        $vrte = $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VRTE'))->first();
        /** @var Action $vm */
        $vm = $procedure->getActionsByActionType($this->getActionTypeByActionTypeCode('VM'))->first();
        $cc = false;
        $rut = false;
        $mand = false;
        if($employer->getPersonPerson()->getDocumentDocument()){
            if($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()){
                $cc = true;
                switch ($vdde->getActionStatusCode()){
                    case 'DCPE':
                        $vdde->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $log = new Log($this->getUser(),'Action','ActionStatus',$vdde->getIdAction(),$this->getStatusByStatusCode('DCPE')->getIdStatusType(),$this->getStatusByStatusCode('NEW')->getIdStatusType(),'El empleador ya tenia el documento');
                        $em->persist($vdde);
                        $em->persist($log);
                        break;
                }
            }else{
                if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()))){
                    $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
                }else{
                    $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
                }
                if($notification->getStatus()!= 1){
                    $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir documento del empleador');
                    $notification->setStatus(1);
                    $em->persist($log);
                    $em->persist($notification);
                }
            }
        }else{
            if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()))){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
            }else{
                $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
            }
            if($notification->getStatus()!= 1){
                $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir documento del empleador');
                $notification->setStatus(1);
                $em->persist($log);
                $em->persist($notification);
            }

        }
        if($employer->getPersonPerson()->getRutDocument()){
            if($employer->getPersonPerson()->getRutDocument()->getMediaMedia()){
                $rut = true;
                switch ($vrte->getActionStatusCode()){
                    case 'DCPE':
                        $vrte->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $log = new Log($this->getUser(),'Action','ActionStatus',$vrte->getIdAction(),$this->getStatusByStatusCode('DCPE')->getIdStatusType(),$this->getStatusByStatusCode('NEW')->getIdStatusType(),'El empleador ya tenia el documento');
                        $em->persist($vdde);
                        $em->persist($log);
                        break;
                }
            }else{
                if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'))){
                    $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
                }else{
                    $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
                }
                if($notification->getStatus()!= 1){
                    $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir rut del empleador');
                    $notification->setStatus(1);
                    $em->persist($log);
                    $em->persist($notification);
                }

            }
        }else{
            if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'))){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
            }else{
                $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
            }
            if($notification->getStatus()!= 1){
                $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir rut del empleador');
                $notification->setStatus(1);
                $em->persist($log);
                $em->persist($notification);
            }
        }
        if($employer->getMandatoryDocument()){
            if($employer->getMandatoryDocument()->getMediaMedia()){
                $mand = true;
                switch ($vm->getActionStatusCode()){
                    case 'DCPE':
                        $vm->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $log = new Log($this->getUser(),'Action','ActionStatus',$vm->getIdAction(),$this->getStatusByStatusCode('DCPE')->getIdStatusType(),$this->getStatusByStatusCode('NEW')->getIdStatusType(),'El empleador ya tenia el documento');
                        $em->persist($vm);
                        $em->persist($log);
                        break;
                }
            }else{
                if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'))){
                    $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
                }else{
                    $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
                }
                if($notification->getStatus()!= 1){
                    $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir mandato del empleador');
                    $notification->setStatus(1);
                    $em->persist($log);
                    $em->persist($notification);
                }

            }
        }else{
            if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'))){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
            }else{
                $notification = $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
            }
            if($notification->getStatus()!= 1){
                $log = new Log($this->getUser(),'Notification','Status',$notification->getId(),$notification->getStatus(),1,'Se activo la notificacion de subir mandato del empleador');
                $notification->setStatus(1);
                $em->persist($log);
                $em->persist($notification);
            }
        }
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function checkEmployerActionErros                             ║
     * ║ Returns true if at least one error in employer actions        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return bool                                                 ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function checkEmployerActionErros(RealProcedure $procedure){
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VER'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VER'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VER'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VEE'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VEE'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDDE'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDDE'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDDE'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDD'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDD'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRTE'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRTE'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRTE'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRT'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRT'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRCE'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRCE'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRCE'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRC'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRC'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VM'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VM'))))>0){
            $action = $procedure->getActionsByActionType($procedure->getEmployerEmployer()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VM'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        return false;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function checkEmployeeActionErros                             ║
     * ║ Returns true if at least one error in employee actions        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ║  @param EmployerHasEmployee $ehe                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return bool                                                 ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function checkEmployeeActionErros(RealProcedure $procedure, EmployerHasEmployee $ehe){
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VEE'));
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$actionType))>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VEE'))))>0){
            $action = $procedure->getActionsByActionType($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VEE'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDD'));
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$actionType))>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDD'))))>0){
            $action = $procedure->getActionsByActionType($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VDD'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRT'));
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$actionType))>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRT'))))>0){
            $action = $procedure->getActionsByActionType($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRT'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VCAT'));
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$actionType))>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'CVAT'))))>0){
            $action = $procedure->getActionsByActionType($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VCAT'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        $actionType = $em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRC'));
        if(count($procedure->getActionsByActionType($actionType)->first())>0){
            /** @var Action $action */
            $action = $procedure->getActionsByActionType($actionType)->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }elseif(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRC'))))>0){
            $action = $procedure->getActionsByActionType($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($em->getRepository("RocketSellerTwoPickBundle:ActionType")->findOneBy(array('code'=>'VRC'))))->first();
            if($action->getActionStatusCode()=='ERRO')
                return true;
        }
        return false;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function checkEmployeeInfoActions                             ║
     * ║ Returns array with:                                           ║
     * ║ bool error true when at least one info action has error       ║
     * ║ DateTime firstErrorAt when error with the errorDate           ║
     * ║ integer idActionError when error with the ActionErrorid       ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return array                                                ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function checkEmployeeInfoActions($procedure)
    {
        $response = array();
        /** @var EmployerHasEmployee $ehe */
        foreach ($procedure->getEmployerEmployer()->getActiveEmployerHasEmployees() as $ehe) {
            $actions = $this->getInfoEmployeeActions($procedure,$ehe);
            $firstError = null;
            $tempAction = null;
            $finish = true;
            $pending = false;
            /** @var Action $action */
            foreach ($actions as $action) {
                if($action->getActionStatusCode()!= 'FIN')
                    $finish = false;
                if($action->getActionStatusCode()== 'DCPE')
                    $pending = true;
                if($firstError == null and $action->getErrorAt()!= null){
                    $firstError = $action->getErrorAt();
                    $tempAction = $action;
                }elseif($firstError != null and $action->getErrorAt()!=null){
                    if($action->getErrorAt()<$firstError){
                        $firstError = $action->getErrorAt();
                        $tempAction = $action;
                    }
                }
            }
            if(!$pending){
                if($procedure->getEmployerEmployer()->getAllDocsReadyAt()!=null){
                    $ehe->setDateDocumentsUploaded(new DateTime());
                    $ehe->setAllEmployeeDocsReadyAt(new DateTime());
                    $this->getDoctrine()->getManager()->persist($ehe);
                    $this->getDoctrine()->getManager()->flush();
                }else{
                    $ehe->setAllEmployeeDocsReadyAt(new DateTime());
                    $this->getDoctrine()->getManager()->persist($ehe);
                    $this->getDoctrine()->getManager()->flush();
                }
            }
            if ($firstError != null){
                if($ehe->getInfoErrorAt()==null){
                    $today = new DateTime();
                    if($this->getUser()){
                        $log = new Log($this->getUser(),'EmployerHasEmployee','infoErrorAt',$ehe->getIdEmployerHasEmployee(),null,$today->format('d-m-Y H:i:s'),'Backoffice encontro errores al validar la información de un empleado');
                        $this->getDoctrine()->getManager()->persist($log);
                    }
                    $ehe->setInfoErrorAt($today);
                    $this->getDoctrine()->getManager()->persist($ehe);
                    $this->getDoctrine()->getManager()->flush();
                }
                if(!$finish){
                    $response[$ehe->getIdEmployerHasEmployee()] = array('error'=>true,'firstErrorAt'=>$firstError,'idActionError'=>$tempAction->getIdAction(),'finish'=>false);
                }else{
                    if($ehe->getInfoValidatedAt() == null){
                        $today = new DateTime();
                        if($this->getUser()){
                            $log = new Log($this->getUser(),'EmployerHasEmployee','infoValidatedAt',$ehe->getIdEmployerHasEmployee(),null,$today->format('d-m-Y H:i:s'),'Backoffice terminó de validar la información de un empleado');
                            $this->getDoctrine()->getManager()->persist($log);
                        }
                        $ehe->setInfoValidatedAt($today);
                        $this->getDoctrine()->getManager()->persist($ehe);
                        $this->getDoctrine()->getManager()->flush();
                    }
                    $response[$ehe->getIdEmployerHasEmployee()] =  array('error'=>false,'firstErrorAt'=>$firstError,'idActionError'=>$tempAction->getIdAction(),'finish'=>true);
                }
            }elseif($finish){
                if($ehe->getInfoValidatedAt() == null){
                    $today = new DateTime();
                    if($this->getUser()){
                        $log = new Log($this->getUser(),'EmployerHasEmployee','infoValidatedAt',$ehe->getIdEmployerHasEmployee(),null,$today->format('d-m-Y H:i:s'),'Backoffice terminó de validar la información de un empleado');
                        $this->getDoctrine()->getManager()->persist($log);
                    }
                    $ehe->setInfoValidatedAt($today);
                    $this->getDoctrine()->getManager()->persist($ehe);
                    $this->getDoctrine()->getManager()->flush();
                }
                $response[$ehe->getIdEmployerHasEmployee()] = array('error'=>false,'finish'=>true);
            }elseif(!$finish and $firstError == null){
                $response[$ehe->getIdEmployerHasEmployee()] = array('error'=>false,'finish'=>false);
            }
        }
        return $response;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getInfoEmployerActions                               ║
     * ║ Returns the array whit the info and document actions of the   ║
     * ║ Employee matching the employerHasEmployee                     ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ║  @param EmployerHasEmployee $ehe                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return array                                                ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getInfoEmployeeActions($procedure,$ehe)
    {
        if($procedure->getProcedureTypeProcedureType()->getCode()!= 'REE'){
            return null;
        }
        $response = array();
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VEE')))==1){
            $response['0']=$procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VEE'))->first();
        }else{
            if(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE')))==1)
                $response['0']=$ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first();
        }
        if(count($procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VDD')))==1){
            $response['1']=$procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VDD'))->first();
        }else{
            if(count($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD')))==1)
                $response['1']=$ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->first();
        }
        $response['2']=$procedure->getActionsByPersonAndActionType($ehe->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VCAT'))->first();
        return $response;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateDocumentStatus                              ║
     * ║ Calculates de DocumentStatus for employers and employees      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer                                              ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateDocumentStatus($procedure)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            if($procedure->getProcedureStatusCode()=='FIN'){
                $procedure->getEmployerEmployer()->setDocumentStatus($this->getDocumentStatusByCode('BOFFFF'));
                $em->persist($procedure->getEmployerEmployer());
                /** @var EmployerHasEmployee $ehe */
                foreach ($procedure->getEmployerEmployer()->getActiveEmployerHasEmployees() as $ehe) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                    $em->persist($ehe);
                }
            }else{
                $this->checkEmployerInfoActions($procedure);
                $this->checkEmployeeInfoActions($procedure);
                $today = new DateTime();
                $employer = $procedure->getEmployerEmployer();
                $employerHasEmployees = $employer->getActiveEmployerHasEmployees();
                if($employer->getAllDocsReadyAt()==null){
                    $employer->setDocumentStatus($this->getDocumentStatusByCode('ALLDCP'));
                    $em->persist($employer);
                }elseif($employer->getInfoValidatedAt()!= null){
                    $employer->setDocumentStatus($this->getDocumentStatusByCode('ALDCVA'));
                    $employer->setDashboardMessage($today);
                    $em->persist($employer);
                }elseif($employer->getInfoErrorAt()!=null){
                    $employer->setDocumentStatus($this->getDocumentStatusByCode('ALLDCE'));
                    $employer->setDashboardMessage($today);
                    $em->persist($employer);
                }else{
                    $employer->setDocumentStatus($this->getDocumentStatusByCode('ALDCIV'));
                    $employer->setDashboardMessage($today);
                    $em->persist($employer);
                }
                /** @var EmployerHasEmployee $ehe */
                foreach ($employerHasEmployees as $ehe) {
                    if ($ehe->getDateDocumentsUploaded() == null) {
                        if ($employer->getAllDocsReadyAt() == null and $ehe->getAllEmployeeDocsReadyAt() == null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCP'));
                            $em->persist($ehe);
                        } elseif ($employer->getAllDocsReadyAt() == null and $ehe->getAllEmployeeDocsReadyAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCPE'));
                            $em->persist($ehe);
                        } elseif ($employer->getAllDocsReadyAt() != null and $ehe->getAllEmployeeDocsReadyAt() == null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCPE'));
                            $em->persist($ehe);
                        }elseif ($employer->getAllDocsReadyAt() != null and $ehe->getAllEmployeeDocsReadyAt() != null) {
                            $ehe->setDateDocumentsUploaded($today);
                            $em->persist($ehe);
                            if($ehe->getInfoValidatedAt() != null) {
                                if ($employer->getInfoValidatedAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCVM'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } elseif ($employer->getInfoErrorAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEVERE'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } else {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCVA'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                }
                            }elseif($ehe->getInfoErrorAt() != null) {
                                if ($employer->getInfoValidatedAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERVEEE'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } elseif ($employer->getInfoErrorAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCE'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } else {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCE'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                }
                            } else {
                                if ($employer->getInfoValidatedAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCVA'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } elseif ($employer->getInfoErrorAt() != null) {
                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCE'));
                                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                                    $em->persist($ehe);
                                } else {

                                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCIV'));
                                    $em->persist($ehe);
                                }
                            }
                        }
                    }elseif($ehe->getInfoValidatedAt() != null) {
                        if($ehe->getAllDocsReadyMessageAt()==null){
                            $ehe->setAllDocsReadyMessageAt($today);
                        }
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCVM'));
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEVERE'));
                            $em->persist($ehe);
                        } else {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCVA'));
                            $em->persist($ehe);
                        }
                    }elseif($ehe->getInfoErrorAt() != null) {
                        if($ehe->getAllDocsReadyMessageAt()==null){
                            $ehe->setAllDocsReadyMessageAt($today);
                        }
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERVEEE'));
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCE'));
                            $em->persist($ehe);
                        } else {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCE'));
                            $em->persist($ehe);
                        }
                    } else {
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCVA'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } else {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCIV'));
                            $em->persist($ehe);
                        }
                    }
                }
            }
            return 1;
        }catch(Exception $e){
            return 0;
        }
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function employerDocumentsReady                               ║
     * ║ Calculates de amount of pending docs for the employer         ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Person $person the employer person                    ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer docs pending                                 ║
     * ╚═══════════════════════════════════════════════════════════════╝
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
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function employeeDocumentsReady                               ║
     * ║ Calculates de amount of pending docs for the employee         ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param EmployerHasEmployee $eHE                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer docs pending                                 ║
     * ╚═══════════════════════════════════════════════════════════════╝
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
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function allDocumentsReady                                    ║
     * ║ returns 1 if all states change correctly                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param User $user                                            ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer                                              ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function allDocumentsReady(User $user){
        $em= $this->getDoctrine()->getManager();
        $status = $this->calculateDocumentStatus($user->getProceduresByType($this->getProcedureTypeByCode('REE'))->first());
        if($status==1){
            $em->flush();
            //getting all the employees
            $ehes = $user->getPersonPerson()->getEmployer()->getActiveEmployerHasEmployees();
            //getting the person
            /** @var Person $person */
            $person = $user->getPersonPerson();
            /** @var EmployerHasEmployee $ehe */
            foreach ($ehes as $ehe) {
                if($ehe->getAllDocsReadyMessageAt()==null and $ehe->getDocumentStatusType()->getDocumentStatusCode()=='ALDCIV'){
                    $this->checkSubscription($ehe);
                }
            }
            return 1;
        }else{
            return 0;
        }
    }

    //todo old function test and remove
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
            'code'  =>'VDDE',
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

    //todo old function test and remove
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

    //todo old function test and remove
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
            'code'=>'VDD',
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

    //todo old function test and remove
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
        dump('Contrato');
        dump($employerHasEmployee->getActiveContract());
        dump('Contrato2');
        dump($em->getRepository('RocketSellerTwoPickBundle:Contract')->findOneBy(array(
            'employerHasEmployeeEmployerHasEmployee' => $employerHasEmployee,
            'state' => 1,
        )));
        $contract = $employerHasEmployee->getActiveContract();

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
        dump($contract);
        if($contract!= null and $contract->getDocumentDocument()){
            $docs['CTR']=true;
        }
        foreach ($docs as $type => $status) {
            // {{ path('download_document', {'id': employees[0].personPerson.idPerson , 'idDocument':doc.idDocument}) }}
            if (!$docs[$type]) {
                $msj = "";
                if($type == 'CC'){
                    $documentType= $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$eePerson->getDocumentType()));
                }
                else{
                    $documentType= $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$type));
                }
                if($this->getNotificationByPersonAndOwnerAndDocumentType($user->getPersonPerson(),$eePerson,$documentType)!= null){
                    continue;
                }
                $dAction=null;
                $nAction="Subir";
                $dUrl=null;
                if ($type == 'CC') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$eePerson->getIdPerson(),'docCode'=>$eePerson->getDocumentType()));
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
            $docs['CC']=true; //This makes references to any document CC CE or TI, it is just in order to get inside the proper case
        }
        if($person->getRutDocument()){
            $docs['RUT']=true;
        }
        foreach ($docs as $type => $status) {
            if (!$docs[$type]) {
                $msj = "";
                if($type == 'CC'){
                  $documentType= $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$person->getDocumentType()));
                }
                else{
                  $documentType= $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode'=>$type));
                }
                if($this->getNotificationByPersonAndOwnerAndDocumentType($person,$person,$documentType)!= null){
                    continue;
                }
                $dAction=null;
                $dUrl=null;
                if ($type == 'CC') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$person->getIdPerson(),'docCode'=>$person->getDocumentType()));
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
        $this->activate150KCampaign($user);
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

    /**
     * @param User $user
     */
    private function activate150KCampaign($user){
        $em = $this->getDoctrine()->getManager();
        $campaignRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Campaign");
        /** @var Campaign $campaign150 */
        $campaign150 = $campaignRepo->findOneBy(array('description'=>'150k'));
        $dateToday = new DateTime();
        if($campaign150->getDateStart()<=$dateToday&&$campaign150->getDateEnd()>=$dateToday){
            $uHCs = $user->getUserHasCampaigns();
            $uHC=$this->check150kCampaing($uHCs);
            if($uHC!=null){
                //enable 150k Campaign
                $uHC->setDateStarted($dateToday);
                $uHC->setState(1);
                $em->persist($uHC);
            }
        }

    }
    private function check150kCampaing(Collection $uHCs){
        /** @var UserHasCampaign $uHC */
        foreach ($uHCs as $uHC) {
            if($uHC->getCampaignCampaign()->getDescription()=="150k"){
                return $uHC;
            }
        }
        return null;
    }

}
