<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Notification;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use DateTime;

class NotificationRestController extends FOSRestController
{

    /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Returned when the notification id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="notificationId", nullable=false,  requirements="\d+", strict=true, description="the notification id.")
     * @RequestParam(name="status", nullable=false,  requirements="-1|0|1", strict=true, description="the novelty type id.")
     * @return View
     */
    public function postChangeStatusAction(ParamFetcher $paramFetcher)
    {
        $user = $this->getUser();
        $view = View::create();
        if($user==null){
            $view->setStatusCode(401);
            return $view;
        }
        /** @var NotificationEmployer $notification */
        $notification = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Notification')
            ->find($paramFetcher->get('notificationId'));
        if($notification==null){
            $view->setStatusCode(404);
            return $view;
        }
        $notification->setStatus($paramFetcher->get('status'));
        $em=$this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $view->setStatusCode(200);
        $response=array();
        $response["url"]="notifications/employer";
        $serializer = $this->get('jms_serializer');
        $serializer->serialize($response, "json");
        $view->setData($response);
        return $view;
    }
        /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Returned when the notification id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="idPerson",  requirements="\d+",nullable=false, strict=true, description="the person id.")
     * @RequestParam(name="type", nullable=false, strict=true, description="notification 
     type")
     * @RequestParam(name="accion", nullable=false, strict=true, description="notification accion")
     * @return View
     */
    public function postCreateNotificationAction(ParamFetcher $paramFetcher)
    {
        // $user = $this->getUser();
        $view = View::create();
        // if($user==null){
        //     $view->setStatusCode(401);
        //     return $view;
        // }
        $role = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Role')
                ->findOneByName("ROLE_BACK_OFFICE");
        $person = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Person')
            ->find($paramFetcher->get('idPerson'));

        $notification = new Notification();
        $notification->setType($paramFetcher->get('type'));
        $notification->setAccion($paramFetcher->get('accion'));
        $notification->setPersonPerson($person);
        $notification->setRoleRole($role);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }

    /**
     * correct notifications
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "correct the upload document notifications.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *   }
     * )
     * @return View
     */
    public function postCorrectNotificationsAction()
    {
        $msgs = "";
        $msgs = $msgs . " CORRIGIENDO NOTIFICACIONES ..." . "<br>";
        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository("RocketSellerTwoPickBundle:Notification")->findAll();
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        /** @var Notification $notification */
        foreach ($notifications as $notification){
            /** @var Person $person */
            $person = $notification->getPersonPerson();
            if($notification->getRelatedLink()==null){
                    $msgs = $msgs . " ERROR RELATEDLINK NULL" . "<br>";
            }else{
                $exUrl = explode('/',$notification->getRelatedLink());
                if($exUrl[1] == "document" and $exUrl[2] == "add"){
                    /** @var Person $person */
                    $docPerson = $em->getRepository("RocketSellerTwoPickBundle:Person")->find($exUrl[3]);
                    /** @var DocumentType $docType */
                    $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find($exUrl[4]);
                    try {
                        switch($docType->getName()){
                            case "Cedula":
                                $description = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$docPerson->getNames())[0]." ". $docPerson->getLastName1());
                                $notification->setDescription($description);
                                $url = $this->generateUrl("documentos_employee", array("entityType"=>'Person',"entityId"=>$docPerson->getIdPerson(),"docCode"=>'CC'));
                                $notification->setDocumentTypeDocumentType($docType);
                                $notification->setRelatedLink($url);
                                $em->persist($notification);
                                $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CEDULA CORREGIDA"."<br>";
                                break;
                            case "Rut":
                                $description =  "Subir copia del RUT de "  .$utils->mb_capitalize(explode(" ",$docPerson->getNames())[0]." ". $docPerson->getLastName1());
                                $notification->setDescription($description);
                                $url = $this->generateUrl("documentos_employee", array("entityType"=>'Person',"entityId"=>$docPerson->getIdPerson(),"docCode"=>'RUT'));
                                $notification->setDocumentTypeDocumentType($docType);
                                $notification->setRelatedLink($url);
                                $em->persist($notification);
                                $msgs = $msgs . " NOTIFICACION ".$notification->getId()." RUT CORREGIDA"."<br>";
                                break;
                            case "Contrato":
                                if($notification->getDownloadLink()==null){
                                    $correct = false;
                                    if($person!=$docPerson){
                                        $eHES = $person->getEmployer()->getEmployerHasEmployees();
                                        /** @var EmployerHasEmployee $eHE */
                                        foreach ($eHES as $eHE){
                                            if($eHE->getEmployeeEmployee()->getPersonPerson() == $docPerson){
                                                $contracts = $eHE->getContracts();
                                                /** @var Contract $contract */
                                                foreach ($contracts as $contract){
                                                    if($contract->getState()==1){
                                                        $activeContract = $contract;
                                                        break;
                                                    }
                                                }
                                                if($activeContract){
                                                    $correct = true;
                                                    $description =  "Subir copia del contrato de ".$utils->mb_capitalize(explode(" ",$docPerson->getNames())[0]." ". $docPerson->getLastName1());
                                                    $notification->setDescription($description);
                                                    $url = $this->generateUrl("documentos_employee", array("entityType"=>'Contract',"entityId"=>$activeContract->getIdContract(),"docCode"=>'CTR'));
                                                    $notification->setDocumentTypeDocumentType($docType);
                                                    $notification->setRelatedLink($url);
                                                    $notification->setDownloadLink("/documents/download/contrato/".$activeContract->getIdContract()."/pdf");
                                                    $notification->setDownloadAction("Bajar");
                                                    $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CONTRATO CORREGIDA"."<br>";
                                                }
                                            }
                                        }
                                    }
                                    if($person->getEmployer()->getEmployerHasEmployees()->count()==1){
                                        /** @var EmployerHasEmployee $eHE */
                                        $eHE = $person->getEmployer()->getEmployerHasEmployees()->first();
                                        $contracts = $eHE->getContracts();
                                        /** @var Contract $contract */
                                        foreach ($contracts as $contract){
                                            if($contract->getState()==1){
                                                $activeContract = $contract;
                                                break;
                                            }
                                        }
                                        if($activeContract){
                                            $correct = true;
                                            $eePerson = $eHE->getEmployeeEmployee()->getPersonPerson();
                                            $description =  "Subir copia del contrato de ".$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                                            $notification->setDescription($description);
                                            $url = $this->generateUrl("documentos_employee", array("entityType"=>'Contract',"entityId"=>$activeContract->getIdContract(),"docCode"=>'CTR'));
                                            $notification->setDocumentTypeDocumentType($docType);
                                            $notification->setRelatedLink($url);
                                            $notification->setDownloadLink("/documents/download/contrato/".$activeContract->getIdContract()."/pdf");
                                            $notification->setDownloadAction("Bajar");
                                            $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CONTRATO CORREGIDA"."<br>";
                                        }
                                    }
                                    if(!$correct){
                                        $notification->setRelatedLink("");
                                        $msgs = $msgs . " NOTIFICACION CONTRATO ERROR ".$notification->getId()."<br>";
                                    }
                                }else{
                                    $strex = explode('/',$notification->getDownloadLink());
                                    /** @var Contract $contract */
                                    $contract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($strex[4]);
                                    if($contract){
                                        $ePerson = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                                        $description =  "Subir copia del contrato de ".$utils->mb_capitalize(explode(" ",$ePerson->getNames())[0]." ". $ePerson->getLastName1());
                                        $notification->setDescription($description);
                                        $url = $this->generateUrl("documentos_employee", array("entityType"=>'Contract',"entityId"=>$contract->getIdContract(),"docCode"=>'CTR'));
                                        $notification->setDocumentTypeDocumentType($docType);
                                        $notification->setRelatedLink($url);
                                        $notification->setDownloadAction("Bajar");
                                        $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CONTRATO CORREGIDA"."<br>";
                                    }else{
                                        $notification->setRelatedLink("");
                                        $msgs = $msgs . " NOTIFICACION CONTRATO ERROR ".$notification->getId()."<br>";
                                    }
                                }
                                $em->persist($notification);
                                break;
                            case "Carta autorización Symplifica":
                                if($notification->getDownloadLink()==null){
                                    if($person->getEmployer()->getEmployerHasEmployees()->count()==1) {
                                        /** @var EmployerHasEmployee $eHE */
                                        $eHE = $person->getEmployer()->getEmployerHasEmployees()->first();
                                        $eePerson = $eHE->getEmployeeEmployee()->getPersonPerson();
                                        $description =  "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                                        $notification->setDescription($description);
                                        $url = $this->generateUrl("documentos_employee", array("entityType"=>'EmployerHasEmployee',"entityId"=>$eHE->getIdEmployerHasEmployee(),"docCode"=>'CAS'));
                                        $notification->setDocumentTypeDocumentType($docType);
                                        $notification->setRelatedLink($url);
                                        $notification->setDownloadLink("/documents/downloads/aut-afiliacion-ss/".$eHE->getIdEmployerHasEmployee()."/pdf");
                                        $notification->setDownloadAction("Bajar");
                                        $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CARTA DE AUTORIZACION CORREGIDA"."<br>";
                                    }else{
                                        $notification->setRelatedLink("/old".$notification->getRelatedLink());
                                        $notification->setDescription("Old subir autorización firmada de manejo de datos y afiliación.");
                                        $notification->setDocumentTypeDocumentType(null);
                                        $msgs = $msgs . " NOTIFICACIONCARTA DE AUTORIZACION ERROR ".$notification->getId()."<br>";
                                    }
                                }else{
                                    $strex = explode('/',$notification->getDownloadLink());
                                    /** @var EmployerHasEmployee $eHE */
                                    $eHE = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($strex[4]);
                                    $eePerson = $eHE->getEmployeeEmployee()->getPersonPerson();
                                    $description =  "Subir autorización firmada de manejo de datos y afiliación de " .$utils->mb_capitalize(explode(" ",$eePerson->getNames())[0]." ". $eePerson->getLastName1());
                                    $notification->setDescription($description);
                                    $url = $this->generateUrl("documentos_employee", array("entityType"=>'EmployerHasEmployee',"entityId"=>$strex[4],"docCode"=>'CAS'));
                                    $notification->setDocumentTypeDocumentType($docType);
                                    $notification->setRelatedLink($url);
                                    $notification->setDownloadAction("Bajar");
                                    $msgs = $msgs . " NOTIFICACION ".$notification->getId()." CARTA DE AUTORIZACION CORREGIDA"."<br>";
                                }
                                $em->persist($notification);
                                break;
                            case "Mandato":
                                if($docPerson == $person){
                                    $description =  "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                                    $notification->setDescription($description);
                                    $notification->setDownloadLink("/documents/downloads/mandato/".$person->getIdPerson()."/pdf");
                                    $employer = $person->getEmployer();
                                    $url = $this->generateUrl("documentos_employee", array("entityType"=>'Employer',"entityId"=>$employer->getIdEmployer(),"docCode"=>'MAND'));
                                    $notification->setDocumentTypeDocumentType($docType);
                                    $notification->setRelatedLink($url);
                                    $notification->setDownloadAction("Bajar");
                                    $msgs = $msgs . " NOTIFICACION ".$notification->getId()." MANDATO CORREGIDA"."<br>";
                                }else{
                                    $notification->setRelatedLink("");
                                    $notification->setDescription("");
                                    $notification->setDownloadLink("");
                                    $msgs = $msgs . " NOTIFICACION MANDATO ERROR ".$notification->getId()."<br>";
                                }
                                $em->persist($notification);
                                break;
                            case "Comprobante":
                                $strex = explode('/',$notification->getDownloadLink());
                                /** @var Payroll $payroll */
                                $payroll = $em->getRepository("RocketSellerTwoPickBundle:Payroll")->find($strex[4]);
                                if($payroll){
                                    /** @var EmployerHasEmployee $eHE */
                                    $eHE = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee();
                                    $description =  "Subir Comprobante del pago a " .$utils->mb_capitalize(explode(" ",$eHE->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ".$eHE->getEmployeeEmployee()->getPersonPerson()->getLastName1(). " " . $utils->period_number_to_name($payroll->getPeriod()) . " " . $utils->month_number_to_name($payroll->getMonth()));
                                    $notification->setDescription($description);
                                    $url = $this->generateUrl("documentos_employee", array("entityType"=>'Payroll',"entityId"=>$payroll->getIdPayroll(),"docCode"=>'CPR'));
                                    $notification->setDocumentTypeDocumentType($docType);
                                    $notification->setRelatedLink($url);
                                    $notification->setDownloadAction("Bajar");
                                    $msgs = $msgs . " NOTIFICACION COMPROBANTE CORREGIDA"."<br>";
                                }else{
                                    $notification->setDescription("Old subir coprobante de pago");
                                    $msgs = $msgs . " NOTIFICACION COMPROBANTE ERROR ".$notification->getId()."<br>";

                                }
                                $em->persist($notification);
                                break;
                            default:
                                break;
                        }
                        $em->flush();
                    }catch(Exeption $e){
                        $msgs = $msgs . " Ocurrio un error: ".$e->getMessage()."<br>";
                    }
                }elseif($notification->getAccion()=="Bajar"){
                    $notification->setStatus(0);
                    $notification->setDocumentTypeDocumentType(null);
                    $em->persist($notification);
                    $em->flush();
                }
            }

        }
        $view = View::create($msgs);
        $view->setStatusCode(200);
        return $view;
    }
}
?>