<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PromotionCodeType;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Traits\GetTransactionDetailTrait;

class EmployerRestController extends FOSRestController
{

    use GetTransactionDetailTrait;
    use EmployeeMethodsTrait;


    /**
     * Obtener el detalle de una transaccion
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la transaccion
     * @param string $type - Tipo de transaccon (pago, contrato, liquidacion)
     *
     * @return View
     *
     */
    public function getTransactionDetailAction($type, $id)
    {
        $details = $this->transactionDetail($type, $id);

        $view = View::create();
        $view->setData($details)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener el listado de Pagos o Contratos de un usuario
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param string $type - Tipo de información a listar (pagos, contratos, novedades)
     * @param integer $id - Id del usuario
     *
     *  @return View
     */
    public function getListByUserAction($type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepository->findOneBy(
                array(
                    "id" => $id
                )
        );

        $data = array();
        switch ($type) {
            case "payments":
                if ($user) {
                    $data = $user->getPayments();
                }
                break;
            case "contracts":
                if ($user) {
                    $employerHasEmployee = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                    $contracts = array();
                    foreach ($employerHasEmployee as $ehe) {
                        $contracts[] = $ehe->getContracts();
                    }
                    foreach ($contracts as $contract) {
                        /** @var Contract $contract */
                        $data[] = $contract;
                    }
                }
                break;
            default:
                break;
        }

        $view = View::create();
        $view->setData($data)->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener el estado de validacion de los documentos del empleador
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener el estado de validacion de documentos del empleado.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $idUser - Id del usuario del que se desea consultar el estado de validacion de documentos como empleado por backoffice
     *
     *  @return View
     */
    public function getEmployerDocumentValidationStateAction($idUser)
    {
        /** @var User $user */
        $user=$this->loadClassById($idUser,"User");
        $view = View::create();
        if($user){
            $valid = $this->employerDocumentsValidated($user);
            if($valid==1){
                $data = 'Validado';
            }elseif($valid==0){
                $data = 'Por Validar';
            }elseif($valid==-1){
                $data = 'Error en documentos';
            }elseif ($valid==2){
                $data = 'Estado desconocido';
            }
        }else{
            $data = 'Usuario no encontrado';
            $view->setData($data)->setStatusCode(404);
            return $view;
        }

        $view->setData($data)->setStatusCode(200);
        return $view;
    }

    /**
     * Obtener el estado de validacion de los documentos del empleado
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener el estado de validacion de documentos del empleado.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $idUser - Id del usuario del que se desea consultar el estado de validacion de documentos como empleado por backoffice
     * @param integer $idEHE - id del employer has employee que se esta consultando
     *  @return View
     */
    public function getEmployeeDocumentValidationStateAction($idUser,$idEHE)
    {
        /** @var User $user */
        $user=$this->loadClassById($idUser,"User");
        $eHE= $this->loadClassById($idEHE,"EmployerHasEmployee");
        $view = View::create();
        if($user){
            if($eHE){
                $valid = $this->employeeDocumentsValidated($user,$eHE);
                if($valid==1){
                    $data = 'Validado';
                }elseif($valid==0){
                    $data = 'Por Validar';
                }elseif($valid==-1){
                    $data = 'Error en documentos';
                }elseif ($valid==2){
                    $data = 'Estado desconocido';
                }
            }else{
                $data = 'EmployerHasEmployee no encontrado';
                $view->setData($data)->setStatusCode(404);
                return $view;
            }
        }else{
            $data = 'Usuario no encontrado';
            $view->setData($data)->setStatusCode(404);
            return $view;
        }
        $view->setData($data)->setStatusCode(200);
        return $view;
    }

    /**
     * Obtener el estado de documentos de todos los empleados de un usuario;
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener el estado de documentos de todos los empleados del usuario",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $idUser - Id del usuario del que se desea consultar
     *  @return View
     */
    public function getAllEmloyeeDocumentsStatusAction($idUser)
    {
        /** @var User $user */
        $user=$this->loadClassById($idUser,"User");
        $view = View::create();
        if($user){
            $data = $this->allDocumentsReady($user);
            $response = array();
            foreach ($data as $dat){
                if ($dat['docStatus']==-2){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee is not payed');
                }elseif($dat['docStatus']==-1){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'all docs are pending');
                }elseif($dat['docStatus']==0){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee docs are pending');
                }elseif($dat['docStatus']==1){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employer docs are pending');
                }elseif($dat['docStatus']==2){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'message docs ready');
                }elseif($dat['docStatus']==3){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'all documents are in validation');
                }elseif($dat['docStatus']==4){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee documents are in validation');
                }elseif($dat['docStatus']==5){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employer documents are in validation');
                }elseif($dat['docStatus']==6){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee docs validated employer docs error');
                }elseif($dat['docStatus']==7){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employer docs validated employee docs error');
                }elseif($dat['docStatus']==8){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employer documents error');
                }elseif($dat['docStatus']==9){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employer documents error');
                }elseif($dat['docStatus']==10){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'all documents error');
                }elseif($dat['docStatus']==11){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'all docs validated message');
                }elseif($dat['docStatus']==12){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'all docs error message');
                }elseif($dat['docStatus']==13){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'backoffice message');
                }elseif($dat['docStatus']==14){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee contract upload is pending');
                }elseif($dat['docStatus']==15){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'message contract uploaded');
                }elseif($dat['docStatus']==16){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee contract in validation');
                }elseif($dat['docStatus']==17){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee contract error');
                }elseif($dat['docStatus']==18){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'employee contract validated message');
                }elseif($dat['docStatus']==19){
                    $response[] = array('idEmployerHasEmployee'=>$dat['idEHE'],'idDocStatus'=>$dat['docStatus'],'docStatus'=>'backoffice finished');
                }
            }
        }else{
            $data = 'Usuario no encontrado';
            $view->setData($data)->setStatusCode(404);
            return $view;
        }
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Obtener el estado de los documentos de un empleador
     *
     * @ApiDoc(
     *     description="Obtener el estado de los documentos del empleador.",
     *     statusCodes={
     *     200 = "Returned when succesful",
     *     400 = "Returned status when Error"
     *  }
     * )
     *
     * @param integer $idUser - Id del User
     *
     * @return View
     */
    public function getEmployerDocumentsStateAction($idUser)
    {
        /** @var User $user */
        $user = $this->loadClassById($idUser,"User");
        $view = View::create();
        $missingDocs='';

        if($user){
            /** @var Person $employerPerson */
            $employerPerson=$user->getPersonPerson();
            if(!$employerPerson->getDocumentDocument())
                $missingDocs = 'Cedula Empleador, ';
            if(!$employerPerson->getRutDocument())
                $missingDocs = $missingDocs.'Rut Empleador, ';
            if(!$employerPerson->getEmployer()->getMandatoryDocument())
                $missingDocs = $missingDocs.'Mandato Empleador, ';
            $numEmp = 0;
            /** @var EmployerHasEmployee $employerHasEmployee */
            foreach($employerPerson->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                if($employerHasEmployee->getState()<1)
                    continue;
                $numEmp+=1;
                if(!$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocumentDocument()){
                    $missingDocs = $missingDocs.'Cedula Empleado '.$numEmp.', ';
                }
                if(!$employerHasEmployee->getAuthDocument()){
                    $missingDocs = $missingDocs.'Carta Autorizacion Empleado '.$numEmp.', ';
                }
                //if(!$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocByType("Contrato")){
                //    $missingDocs = $missingDocs.'Contrato Empleado '.$numEmp.', ';
                //}
            }
            if($missingDocs==''){
                $msg = array('state'=>true , 'message'=>"Documentos Completos" , 'missingDocs'=>$missingDocs);
            }else{
                $msg = array('state'=>false , 'message'=>"Faltan Documentos" , 'missingDocs'=>$missingDocs);
            }
            $view->setData($msg);
            $view->setStatusCode(200);
        }else{
            $msg = array('state'=>false , 'message'=>"User doesn't exist" , 'missingDocs'=>$missingDocs);
            $view->setData($msg);
            $view->setStatusCode(404);
        }
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
     * Busca el empleado que trabaja menos tiempo y lo marca como gratuito
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $idEmployer - del empoyerHasEmployee
     * @param string $freeTime - tiempo de meses gratis
     * @param boolean $all true:setear todos los empleados a free, false:setear solo el de menor jornada
     *
     * @return View
     *
     */
    public function setEmployeesFreeAction($idEmployer, $freeTime, $all = false)
    {
        $view = View::create();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $repositoryContract = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        /* @var $employerHasEmployees EmployerHasEmployee */
        $employerHasEmployees = $repository->findBy(array('employerEmployer' => $idEmployer));

        if (!empty($employerHasEmployees)) {
            $elmenor = array();
            $em = $this->getDoctrine()->getManager();
            foreach ($employerHasEmployees as $key => $employerHasEmployee) {
                if ($employerHasEmployee->getState() > 0) {
                    if ($all) {
                        $employerHasEmployee->setIsFree($freeTime);
                    } else {
                        $employerHasEmployee->setIsFree(0); /* @var $contract Contract */
                        $contract = $repositoryContract->findOneBy(array('employerHasEmployeeEmployerHasEmployee' => $employerHasEmployee, 'state' => 1));
                        if (empty($elmenor)) {
                            $elmenor = array('employerHasEmployee' => null, 'contrato' => null);
                            $elmenor['employerHasEmployee'] = $employerHasEmployee;
                            $elmenor['contrato'] = $contract;
                        } else {
                            if ($contract->getWorkableDaysMonth() < $elmenor['contrato']->getWorkableDaysMonth()) {
                                $elmenor['employerHasEmployee'] = $employerHasEmployee;
                                $elmenor['contrato'] = $contract;
                            }
                        }
                    }
                    $em->persist($employerHasEmployee);
                }
            }
            if (!$all) {
                $employerHasEmployee = $elmenor['employerHasEmployee'];
                $employerHasEmployee->setIsFree($freeTime);
                $em->persist($employerHasEmployee);
            }
            $em->flush();

            //$view->setData($employerHasEmployee);
            $view->setData("OK");
            $view->setStatusCode(200);
        } else {
            $view->setData("sin empleados");
            $view->setStatusCode(400);
        }
        //return $this->handleView($view);
        return $view;
    }
    /**
     * crear notificaciones iniciales  para empleador
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "crear tramites para backOffice",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="idUser", description="Recibe el id del usuario")
     *
     *
     *
     * @return View
     */
    public function postCreateInitialNotificationsAction(ParamFetcher $paramFetcher)
    {
        $idUser = ($paramFetcher->get('idUser'));
        /* @var $user User */
        $user = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find($idUser);
        $employer=$user->getPersonPerson()->getEmployer();
        $this->validateDocumentsEmployer($user,$employer);
        /** @var EmployerHasEmployee $eHE */
        foreach ($employer->getEmployerHasEmployees() as $eHE) {
            if($eHE->getState()>=2){
                $employee=$eHE->getEmployeeEmployee();
                $this->validateDocumentsEmployee($user,$employee);
            }
        }

        $view = View::create();
        $view->setData(array());
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * hace un query de la clase para instanciarla
     * @param  [type] $parameter id que desea pasar
     * @param  [type] $entity    entidad a la cual hace referencia
     */
    private function loadClassById($parameter, $entity)
    {
        $loadedClass = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:'.$entity)
            ->find($parameter);
        return $loadedClass;
    }
    /**
     * crear notificaciones iniciales  para empleador
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Enviar email requerido",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="email", nullable=false, strict=true, description="email al que se envía la infomación")
     *
     * @return View
     */
    public function postRemainderAction(ParamFetcher $paramFetcher)
    {
        $email = $paramFetcher->get("email");
        $view=View::create();
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $view->setStatusCode(403);
        }
        $smailer = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage(array('emailType'=>'reminder','toEmail'=>$email));

        if ($smailer) {
            return $view->setStatusCode(200)->setData(array());
        } else {
            return $view->setStatusCode(500);
        }

    }

    /**
     * generate referred promotional codes
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Generate referred promotional codes.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     *
     * @return View
     *
     */
    public function postGenerateReferredPromotionalCodesAction()
    {
        $details = "Creando codigos<br>";
        $em=$this->getDoctrine()->getManager();
        $code = new PromotionCodeType();
        $code->setDescription("Referidos");
        $code->setDuration(2);
        $code->setShortName("AC");
        $em->persist($code);
        $em->flush();
        $users = $em->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        /** @var User $user */
        foreach ($users as $user) {
            if($user->getStatus()>=2){
                $strEx = explode(' ',$user->getPersonPerson()->getNames());
                $promoCode = new PromotionCode();
                $promoCode->setCode($strEx[0].$user->getPersonPerson()->getLastName1());
                $promoCode->setPromotionCodeTypePromotionCodeType($code);
                $em->persist($promoCode);
            }
        }
        $em->flush();
        $details = "Terminado<br>";
        $view = View::create();
        $view->setData($details)->setStatusCode(200);

        return $view;
    }




}
