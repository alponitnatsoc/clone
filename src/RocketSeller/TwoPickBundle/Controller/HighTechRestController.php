<?php
namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;
use GuzzleHttp\Client;

use EightPoints\Bundle\GuzzleBundle;

/**
 * Contains the web service to be exposed to novopayment, it will be called
 * by them when the dispersion process if finished.
 *
 */
class HighTechRestController extends FOSRestController
{
    /**
     * Verifies that the parameters to the web services, are in place and that
     * have the ccorrect format.
     * @param array $parameters , Contains the parameters by the client.
     * @param array $regex , contains the key as parameter and a regex.
     * @param array $mandatory , contains a bool indicating if it is mandatory.
     */
    public function validateParamters($parameters, $regex, $mandatory)
    {

        foreach ($mandatory as $key => $value) {
            if (array_key_exists($key, $mandatory) &&
                $mandatory[$key] &&
                (!array_key_exists($key, $parameters))
            )
                throw new HttpException(422, "The parameter " . $key . " is empty");

            if (array_key_exists($key, $regex) &&
                array_key_exists($key, $parameters) &&
                !preg_match('/^' . $regex[$key] . '$/', $parameters[$key])
            )
                throw new HttpException(422, "The format of the parameter " .
                    $key . " is invalid, it doesn't match" .
                    $regex[$key]);

            if (!$mandatory[$key] && (!array_key_exists($key, $parameters)))
                $parameters[$key] = '';
        }
    }

    /**
     * @POST("notificacion/recaudo")
     * Get a notification of the collection of the money, to update in our system.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a notification of the collection of the money,
     *                  to update in our system.",
     *   statusCodes = {
     *     200 = "OK",
     *     201 = "Accepted",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request .
     * Rest Parameters:
     *
     * (name="numeroRadicado", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
     * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
     *                                                   0 OK, 90 Fondos Insuficientes, 91 Cuenta Embargada, 92 No Autorizada, 93 Cuenta No Existe."
     *
     * @return View
     */
    public function postCollectionNotificationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['numeroRadicado'] = '([0-9])+';
        $mandatory['numeroRadicado'] = true;
        $regex['estado'] = '([0-9])+';
        $mandatory['estado'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);
        // Validate that the id exists.


        $this->validateParamters($parameters, $regex, $mandatory);
        $id = $parameters['numeroRadicado'];
        $state = $parameters['estado'];
        // Validate that the id exists.
        $dispersion = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

        /** @var PurchaseOrders $dis */
        $dis = $dispersion->findOneBy(array('radicatedNumber' => $id));
        if ($dis == null) {
            throw new HttpException(404, "The id: " . $id . " was not found.");
        }
        $em = $this->getDoctrine()->getManager();
        if ($dis->getAlreadyRecived() == 1) {
            $view = View::create();
            $retorno = $view->setStatusCode(200)->setData(array('already' => "sent"));
            return $retorno;
        } else {
            $dis->setAlreadyRecived(1);
            $em->persist($dis);
            $em->flush();
        }
        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $retorno = null;
        if ($state == 0) {
            // I will update it to id 5.
            $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => '00'));
            //$realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $dis->setPurchaseOrdersStatus($pos);
            $answer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getDispersePurchaseOrder', ['idPurchaseOrder' => $dis->getIdPurchaseOrders()]);
            if ($answer->getStatusCode() != 200) {
                $mesange = "not so good man";
            } else {
                $mesange = "all good man";
            }

            //this will happen on the recaudo
            /** @var Config $ucfg */
            $ucfg = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config")->findOneBy(array('name' => 'ufg'));
            $invoiceNumber = intval($ucfg->getValue()) + 1;
            $ucfg->setValue($invoiceNumber);
            $dis->setInvoiceNumber($invoiceNumber);
            $em->persist($ucfg);
            $em->persist($dis);
            $em->flush();

            /** @var \DateTime $date */
            $date = new DateTime();
            $date->setTimezone(new \DateTimeZone('America/Bogota'));
            $params = array(
                'ref'=> 'factura',
                'id' => $dis->getIdPurchaseOrders(),
                'type' => 'pdf',
                'attach' => null
            );
            $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
            $file =  $documentResult->getContent();
            if (!file_exists('uploads/temp/facturas')) {
                mkdir('uploads/temp/facturas', 0777, true);
            }
            $path = 'uploads/temp/facturas/'.$dis->getIdUser()->getPersonPerson()->getIdPerson().'_tempFacturaFile.pdf';
            file_put_contents($path, $file);
            $context = array(
                'emailType'=>'succesRecollect',
                'toEmail' => $dis->getIdUser()->getEmail(),
                'userName' => $dis->getIdUser()->getPersonPerson()->getFullName(),
                'fechaRecaudo' => $date,
                'value'=>$dis->getValue(),
                'path'=>$path,
                'documentName'=>'Factura '.date_format($date,'d-m-y H:i:s').'.pdf',
            );

            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

        } else {
            //nicetohave buscar este ID
            $paymethodId = $dis->getPayMethodId();
            $context=array(
                'emailType'=>'failDispersion',
                'userEmail'=>$dis->getIdUser()->getEmail(),
                'toEmail'=>$dis->getIdUser()->getEmail(),
                'userName'=>$dis->getIdUser()->getPersonPerson()->getFullName()
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            $contextBack=array(
                'emailType'=>'regectionCollect',
                'userEmail'=>$dis->getIdUser()->getEmail(),
                'userName'=>$dis->getIdUser()->getPersonPerson()->getFullName(),
                'rejectionDate'=>new DateTime(),
                'toEmail'=> 'backOfficeSymplifica@gmail.com',
                'phone'=>$dis->getIdUser()->getPersonPerson()->getPhones()->first()->getPhoneNumber(),
	              'value'=>$dis->getValue()
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);

            $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
            //$realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $dis->setPurchaseOrdersStatus($pos);
            $date = new DateTime('01-01-0001 00:00:00');
            $dis->setDatePaid($date);
	          $mesange = "not so good man";
        }
        $em->persist($dis);
        $em->flush();
        $view = View::create();
        $retorno = $view->setStatusCode(200)->setData(array("mesange" => $mesange));
        return $retorno;


    }

    /**
     * @POST("notificacion/pago")
     * Get a notification of the payment, to update in our system.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a notification of the payment, to update in our system.",
     *   statusCodes = {
     *     200 = "OK",
     *     201 = "Accepted",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request .
     * Rest Parameters:
     *
     * (name="numeroRadicado", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
     * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
     *                                                   0 OK, 90 Fondos Insuficientes, 91 Cuenta Embargada, 92 No Autorizada, 93 Cuenta No Existe."
     *
     * @return View
     */
    public function postPaymentNotificationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['numeroRadicado'] = '([0-9])+';
        $mandatory['numeroRadicado'] = true;
        $regex['estado'] = '([0-9])+';
        $mandatory['estado'] = true;


        $this->validateParamters($parameters, $regex, $mandatory);

        $id = $parameters['numeroRadicado'];
        $state = $parameters['estado'];
        $payRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Pay");

        /** @var Pay $pay */
        $pay = $payRepository->findOneBy(array('idDispercionNovo' => $id));
        if ($pay == null) {
            throw new HttpException(404, "The id: " . $id . " was not found.");
        }
        $em = $this->getDoctrine()->getManager();
        $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $retorno = null;
        if ($state == 0) {
            $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => '-1'));
            //$realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $pay->setPurchaseOrdersStatusPurchaseOrdersStatus($pos);
            $pay->getPurchaseOrdersDescription()->setPurchaseOrdersStatus($pos);


            if($pay->getPurchaseOrdersDescription()->getProductProduct()->getSimpleName()=="PN"){
                $context=array(
                    'emailType'=>'succesDispersion',
                    'toEmail'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getEmail(),
                    'userName'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getFullName(),
                );
                $params = array(
                    'ref'=> 'comprobante',
                    'id' => $pay->getPurchaseOrdersDescription()->getPayrollPayroll()->getIdPayroll(),
                    'type' => 'pdf',
                    'attach' => null
                );
                $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
                $file =  $documentResult->getContent();
                if (!file_exists('uploads/temp/comprobantes')) {
                    mkdir('uploads/temp/comprobantes', 0777, true);
                }
                $path = 'uploads/temp/comprobantes/'.$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getIdPerson().'_tempFacturaFile.pdf';
                file_put_contents($path, $file);
                $context['path']=$path;
                $context['comprobante']=true;
                $context['documentName']='Comprobante '.date_format(new DateTime(),'d-m-y H:i:s').'.pdf';
                $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

                //here i create the comprobante
                /* This is not longer necesary

                $actualPayroll = $pay->getPurchaseOrdersDescription()->getPayrollPayroll();
                $person = $pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson();
                $dUrl = $this->generateUrl("download_documents", array('id' => $actualPayroll->getIdPayroll(), 'ref' => "comprobante", 'type' => 'pdf'));
                $dAction = "Bajar";
                $action = "Subir";
                $employeePerson=$actualPayroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                $url = $this->generateUrl("documentos_employee", array("entityType"=>'Payroll',"entityId"=>$actualPayroll->getIdPayroll(),"docCode"=>'CPR'));
                $documentType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'CPR'));
                $msj = "Subir comprobante de " . $utils->mb_capitalize(explode(" ", $employeePerson->getNames())[0] . " " . $employeePerson->getLastName1()) . " " . $utils->period_number_to_name($actualPayroll->getPeriod()) . " " . $utils->month_number_to_name($actualPayroll->getMonth());

                $notification = new Notification();
                $notification->setPersonPerson($person);
                $notification->setStatus(1);
                $notification->setDocumentTypeDocumentType($documentType);
                $notification->setType('alert');
                $notification->setDescription($msj);
                $notification->setRelatedLink($url);
                $notification->setAccion($action);
                $notification->setDownloadAction($dAction);
                $notification->setDownloadLink($dUrl);
                $em = $this->getDoctrine()->getManager();
                //$em->persist($notification);*/

            }

        } else {
            $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => '-2'));
            //$realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $pay->setPurchaseOrdersStatusPurchaseOrdersStatus($pos);
            $pay->getPurchaseOrdersDescription()->setPurchaseOrdersStatus($pos);
            $view = View::create();


            $rejectedPurchaseOrderDescription = $pay->getPurchaseOrdersDescription();
            $employerPerson = $rejectedPurchaseOrderDescription->getPurchaseOrders()->getIdUser()->getPersonPerson();
            $rejectDate = new DateTime();
            $value = $rejectedPurchaseOrderDescription->getValue();
            $product = $rejectedPurchaseOrderDescription->getProductProduct();

            $context=array(
                'emailType'=>'failDispersion',
                'userEmail'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getEmail(),
                'toEmail'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getEmail(),
                'userName'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getFullName(),
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            $contextBack=array(
                'emailType'=>'regectionDispersion',
                'userEmail'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getEmail(),
                'userName'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getFullName(),
                'rejectionDate'=>$rejectDate,
                'toEmail'=> 'backOfficeSymplifica@gmail.com',
                'phone'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getPhones()->first()->getPhoneNumber(),
                'rejectedProduct'=>$product->getName(),
                'idPOD'=>$rejectedPurchaseOrderDescription->getIdPurchaseOrdersDescription(),
                'value'=>$value
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);

            $notification= new Notification();
            $notification->setAccion("Ver");
            $notification->setStatus("1");
            $notification->setDescription("El item de ". $rejectedPurchaseOrderDescription->getProductProduct()->getName()." presentó un error");
            $notification->setType("alert");
            $notification->setPersonPerson($rejectedPurchaseOrderDescription->getPurchaseOrders()->getIdUser()->getPersonPerson());
            $em->persist($notification);
            $em->flush();
            $notification->setRelatedLink($this->generateUrl("show_pod_description" ,array(
                'idPOD'=>$rejectedPurchaseOrderDescription->getIdPurchaseOrdersDescription(),
                'notifRef'=>$notification->getId())));
            $em->persist($notification);
            $em->flush();

        }
        $em->persist($pay);
        $em->flush();

        // Succesfull operation.
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData([]);
        return $view;

    }

    /**
     * @POST("notificacion/registro")
     * Get a notification of the payment, to update in our system.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a notification of the payment, to update in our system.",
     *   statusCodes = {
     *     200 = "OK",
     *     201 = "Accepted",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request .
     * Rest Parameters:
     *
     * (name="numeroRadicado", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
     * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
     *                                                   0 OK, 90 Fondos Insuficientes, 91 Cuenta Embargada, 92 No Autorizada, 93 Cuenta No Existe."
     *
     * @return View
     */
    public function postPaymentSubscriptionAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['numeroRadicado'] = '([0-9])+';
        $mandatory['numeroRadicado'] = true;
        $regex['estado'] = '([0-9])+';
        $mandatory['estado'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Succesfull operation.
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData([]);
        return $view;

    }

    /**
     * @POST("notificacion/registro")
     * Get a notification of the payment, to update in our system.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a notification of the payment, to update in our system.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request .
     * Rest Parameters:
     *
     * (name="methodId", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
     * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
     *                                                   0 OK, 90 NOTOk."
     *
     * @return View
     */
    public function postAccountSubscriptionAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['methodId'] = '([0-9])+';
        $mandatory['methodId'] = true;
        $regex['estado'] = '([0-9])+';
        $mandatory['estado'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Succesfull operation.
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData([]);
        return $view;

    }

}
