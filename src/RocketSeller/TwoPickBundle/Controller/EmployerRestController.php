<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Liquidation;
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
     * Obtener el estado de los documentos de un empleador
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
            if(!$employerPerson->getDocByType("Cedula"))
                $missingDocs = 'Cedula Empleador, ';
            if(!$employerPerson->getDocByType("Rut"))
                $missingDocs = $missingDocs.'Rut Empleador, ';
            if(!$employerPerson->getDocByType("Mandato"))
                $missingDocs = $missingDocs.'Mandato Empleador, ';
            $numEmp = 0;
            /** @var EmployerHasEmployee $employerHasEmployee */
            foreach($employerPerson->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                $numEmp+=1;
                if(!$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocByType("Cedula")){
                    $missingDocs = $missingDocs.'Cedula Empleado '.$numEmp.', ';
                }
                if(!$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocByType("Carta autorización Symplifica")){
                    $missingDocs = $missingDocs.'Carta Autorizacion Empleado '.$numEmp.', ';
                }
                if(!$employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getDocByType("Contrato")){
                    $missingDocs = $missingDocs.'Contrato Empleado '.$numEmp.', ';
                }
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

        $smailer = $this->get('symplifica.mailer.twig_swift');
        
        $send = $smailer->remainderEmail('@RocketSellerTwoPick/SendEmail/recuerdatos.html.twig', 'registro@symplifica.com', $email,'Recordatorio datos para registro');
        
        if ($send) {
            return $view->setStatusCode(200)->setData(array());
        } else {
            return $view->setStatusCode(500);
        }

    }


}
