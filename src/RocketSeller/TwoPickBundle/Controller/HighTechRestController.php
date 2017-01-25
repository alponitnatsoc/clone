<?php
namespace RocketSeller\TwoPickBundle\Controller;


use Application\Sonata\MediaBundle\Entity\Media;
use RocketSeller\TwoPickBundle\Entity\Document;
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
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Transaction;
use RocketSeller\TwoPickBundle\Entity\TransactionState;
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
        $regex['numeroRadicado'] = '(.)+';
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
            $dis->setDateModified(new DateTime());
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
                'userName' => $dis->getIdUser()->getPersonPerson()->getNames(),
                'fechaRecaudo' => $date,
                'value'=>$dis->getValue(),
                'path'=>$path,
                'documentName'=>'Factura '.date_format($date,'d-m-y H:i:s').'.pdf',
            );

            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

            //push notification
            $message = "¡Hemos debitado tu cuenta con éxito!";
            $title = "Symplifica";
            $longMessage = "¡Hemos debitado tu cuenta con éxito! Te avisaremos en cuanto se hayan realizado los pagos correspondientes";

            $request = new Request();
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $dis->getIdUser(),
                "title" => $title,
                "message" => $message,
                "longMessage" => $longMessage
            ));
            $pushNotificationService = $this->get('app.symplifica_push_notification');
            $result = $pushNotificationService->postPushNotificationAction($request);
        } else {
            //nicetohave buscar este ID
            $paymethodId = $dis->getPayMethodId();
            $context=array(
                'emailType'=>'failRecollect',
                'toEmail'=>$dis->getIdUser()->getEmail(),
                'userName'=>$dis->getIdUser()->getPersonPerson()->getFullName(),
                'rejectionDate' => new DateTime(),
                'value'=> $dis->getValue(),
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
            //$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);

            //push notification
            $message = "Hubo un inconveniente al debitar tu cuenta";
            $title = "Symplifica";
            $longMessage = "Tuvimos un inconveniente al realizar el débito de tu cuenta, nos pondremos en contacto.";

            $request = new Request();
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $dis->getIdUser(),
                "title" => $title,
                "message" => $message,
                "longMessage" => $longMessage
            ));
            $pushNotificationService = $this->get('app.symplifica_push_notification');
            $result = $pushNotificationService->postPushNotificationAction($request);

            $pos = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
            //$realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $dis->setPurchaseOrdersStatus($pos);
            /** @var PurchaseOrdersDescription $purchaseOrderDescription */
            foreach ($dis->getPurchaseOrderDescriptions() as $purchaseOrderDescription) {
                $purchaseOrderDescription->setPurchaseOrdersStatus($pos);
            }
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

                //push notification
                $message = "¡Hemos realizado tu pago con éxito!";
                $title = "Symplifica";
                $longMessage = "¡Hemos realizado tu pago con éxito! Puedes entrar y obtener sus comprobantes";

                $request = new Request();
                $request->setMethod("POST");
                $request->request->add(array(
	                  "idUser" => $pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser(),
                    "title" => $title,
                    "message" => $message,
                    "longMessage" => $longMessage
                ));
                $pushNotificationService = $this->get('app.symplifica_push_notification');
                $result = $pushNotificationService->postPushNotificationAction($request);

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

		        if($pay->getPurchaseOrdersDescription()->getProductProduct()->getSimpleName()=="PP"){

			        $request->setMethod("GET");
			        $info = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:getPayslipPilaPayment',array(
				        "GSCAccount" => $pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getEmployer()->getIdHighTech(),
				        "payslipNumber" => $pay->getPurchaseOrdersDescription()->getEnlaceOperativoFileName()
			        ), array('_format' => 'json'));

			        $info = json_decode($info->getContent(),true);

			        if ( $info['codigoRespuesta'] == "OK" ){
				        $documentType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode' => 'EOCP'));

				        /** @var Document $document */
				        $document = new Document();
				        $document->setName("Comprobante pago pila" . $pay->getPurchaseOrdersDescription()->getIdPurchaseOrdersDescription());
				        $document->setStatus(1);
				        $document->setDocumentTypeDocumentType($documentType);

				        $filename = "tempComprobantePlanilla" . $pay->getPurchaseOrdersDescription()->getIdPurchaseOrdersDescription() . ".pdf";
				        $file = "uploads/temp/comprobantes/$filename";

				        file_put_contents($file, base64_decode($info['comprobanteBase64']));

				        $mediaManager = $this->container->get('sonata.media.manager.media');
				        $media = $mediaManager->create();
				        $media->setBinaryContent($file);
				        $media->setProviderName('sonata.media.provider.file');
				        $media->setName($document->getName());
				        $media->setProviderStatus(Media::STATUS_OK);
				        $media->setContext('person');
				        $media->setDocumentDocument($document);

				        $document->setMediaMedia($media);

				        $pay->getPurchaseOrdersDescription()->setDocument($document);
				        $em->persist($pay);

				        $em->flush();

				        $context=array(
					        'emailType'=>'succesDispersion',
					        'toEmail'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getEmail(),
					        'userName'=>$pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getFullName(),
				        );

				        $context['path']=$file;
				        $context['comprobante']=true;
				        $context['pagoPila'] = true;
				        $context['documentName']= 'Comprobante pago aportes '.date_format(new DateTime(),'d-m-y H:i:s').'.pdf';
				        $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

                        //push notification
                        //¡El pago de los aportes a seguridad social se ha realizado con éxito!
                        $message = "¡Hemos realizado tu pago con éxito!";
                        $title = "Symplifica";
                        $longMessage = "¡Hemos realizado El pago de los aportes a seguridad social con éxito!";

                        $request = new Request();
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "idUser" => $pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser(),
                            "title" => $title,
                            "message" => $message,
                            "longMessage" => $longMessage
                        ));
                        $pushNotificationService = $this->get('app.symplifica_push_notification');
                        $result = $pushNotificationService->postPushNotificationAction($request);
			        }
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
            //$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($contextBack);

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

            //push notification
            $message = "Hubo un inconveniente al realizar tu pago";
            $title = "Symplifica";
            $longMessage = "Tuvimos un inconveniente al realizar tu pago, puedes realizar el cambio de tu método de pago";

            $request = new Request();
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $rejectedPurchaseOrderDescription->getPurchaseOrders()->getIdUser(),
                "title" => $title,
                "message" => $message,
                "longMessage" => $longMessage,
                "page" => 'TransactionsInProcessPage'
            ));
            $pushNotificationService = $this->get('app.symplifica_push_notification');
            $result = $pushNotificationService->postPushNotificationAction($request);
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

		/**
		 * @POST("notificacion/pila/registro/empleador")
		 * Get a notification of the register state of an employer, to update in our system.<br/>
		 *
		 * @ApiDoc(
		 *   resource = true,
		 *   description = "Get a notification of the register state of an employer, to update in our system.",
		 *   statusCodes = {
		 *     200 = "OK",
		 *     400 = "Bad Request",
		 *     401 = "Unauthorized",
		 *     422 = "Parameter format invalid"
		 *   }
		 * )
		 *
		 * @param Request $request .
		 * Rest Parameters:
		 *
		 * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="Radicated number, generated by hightech on the service postRegisterEmployerPilaOperatorAction")
		 * (name="registerState", nullable=false, requirements="(.)*")
		 * (name="errorLog", nullable=true)
		 * (name="errorMessage", nullable=true)
		 *
		 * @return View
		 */
		public function postNotifyRegisterEmployerPilaOperatorAction(Request $request)
		{
			$parameters = $request->request->all();
			$regex = array();
			$mandatory = array();
			// Set all the parameters info.
			$regex['radicatedNumber'] = '[0-9]+';
			$mandatory['radicatedNumber'] = true;
			$regex['registerState'] = '(.)*';
			$mandatory['registerState'] = true;

			$this->validateParamters($parameters, $regex, $mandatory);

			$request->setMethod("PUT");
			$request->request->add(array(
				"radicatedNumber"=> $parameters['radicatedNumber'],
				"registerState"=> isset($parameters['registerState']) && $parameters['registerState'] != NULL ? $parameters['registerState'] : -1,
				"errorLog" => isset($parameters['errorLog']) &&  $parameters['errorLog'] != NULL ? $parameters['errorLog'] : "",
				"errorMessage" => isset($parameters['errorMessage']) && $parameters['errorMessage'] ? $parameters['errorMessage'] : ""
			));

			return $this->forward('RocketSellerTwoPickBundle:HighTechRest:putProcessRegisterEmployerPilaOperator', array('_format' => 'json'));
		}

	/**
	 * @POST("notificacion/pila/carga/planilla")
	 * Get a notification of the upload state of a pila payroll, to update in our system.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Get a notification of the upload state of a pila payroll, to update in our system.",
	 *   statusCodes = {
	 *     200 = "OK",
	 *     400 = "Bad Request",
	 *     401 = "Unauthorized",
	 *     422 = "Parameter format invalid"
	 *   }
	 * )
	 *
	 * @param Request $request .
	 * Rest Parameters:
	 *
	 * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="Radicated number, generated by hightech on the service postRegisterEmployerPilaOperatorAction")
	 * (name="payrollState", nullable=false, requirements="(.)*")
	 * (name="payrollNumber", nullable=true, requirements="[0-9]+" , description="Number generated by Enlace Operativo")
	 * (name="errorLog", nullable=true, requirements="(.)*")
	 * (name="errorMessage", nullable=true, requirements="(.)*")
	 *
	 * @return View
	 */
	public function postNotifyUploadPayrollFileAction(Request $request)
	{
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['radicatedNumber'] = '[0-9]+';
		$mandatory['radicatedNumber'] = true;
		$regex['payrollState'] = '(.)*';
		$mandatory['payrollState'] = true;
		$regex['payrollNumber'] = '[0-9]+';
		$mandatory['payrollNumber'] = false;

		$this->validateParamters($parameters, $regex, $mandatory);
		
		/*There are 4 cases...
		The file uploaded (payrollState = 0) and was no problems, errorLog == null
		The file uploaded (payrollState = 0) and was warnings or errors, errorLog != null
		The file was not uploaded (payrollState != 0) and was a big problem, errorLog = null
		The file was not uploaded (payrollState != 0) and was a specific problem , errorLog != null*/
		
		$request->setMethod("PUT");
		$request->request->add(array(
			"radicatedNumber"=> $parameters['radicatedNumber'],
			"planillaState"=> isset($parameters['payrollState']) && $parameters['payrollState'] != NULL ? $parameters['payrollState'] : -1,
			"errorLog" => isset($parameters['errorLog']) && $parameters['errorLog'] != NULL ? $parameters['errorLog'] : "",
			"planillaNumber" => isset($parameters['payrollNumber']) && $parameters['payrollNumber'] != NULL ? $parameters['payrollNumber'] : "",
			"errorMessage" => isset($parameters['errorMessage']) && $parameters['errorMessage'] != NULL ? $parameters['errorMessage'] : ""
		));

		return $this->forward('RocketSellerTwoPickBundle:HighTechRest:putProcessUploadFilePilaOperator', array('_format' => 'json'));
	}

	/**
	 * @PUT("notificacion/pila/registro/empleador/procesar")
	 * Updates the proper data based on the received info.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Updates the proper data based on the received info.",
	 *   statusCodes = {
	 *     200 = "OK",
	 *     400 = "Bad Request",
	 *     401 = "Unauthorized",
	 *     422 = "Parameter format invalid"
	 *   }
	 * )
	 *
	 * @param Request $request .
	 * Rest Parameters:
	 *
	 * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="Radicated number, generated by hightech on the service postRegisterEmployerPilaOperatorAction")
	 * (name="registerState", nullable=false, requirements="(.)*")
	 * (name="errorLog", nullable=true)
	 * (name="errorMessage", nullable=true)
	 *
	 * @return View
	 */
	public function putProcessRegisterEmployerPilaOperatorAction(Request $request)
	{
		$parameters = $request->request->all();
		$view = View::create();

		$em = $this->getDoctrine()->getManager();

		$transactionRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Transaction");
		/** @var Transaction $singleTransaction */
		$singleTransaction = $transactionRepo->findOneBy(array('radicatedNumber' => $parameters['radicatedNumber']));

		if($singleTransaction == NULL){
			$view->setStatusCode(404);
			$view->setData(array("returnCode" => 404 , "returnDescription" => "Radicated number not found"));
			return $view;
		}

		//This means the user was created succesfully
		if( $parameters['registerState'] == 0 ){
			$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-InsOk'));
			$singleTransaction->setPurchaseOrdersStatus($purchaseOrdersStatus);

			$employer = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer')->findOneBy(array('existentPila' => $singleTransaction->getIdTransaction()));
			
			if($employer != null){
				$employer->setExistentPila(-1);
				
				$em->persist($employer);
				$em->flush();
			}
		}
		else if( $parameters['registerState'] != -1 ){
			$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'InsPil-InsRec'));
			$documentType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode' => 'EOCP'));
			$singleTransaction->setPurchaseOrdersStatus($purchaseOrdersStatus);

			$employer = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer')->findOneBy(array('existentPila' => $singleTransaction->getIdTransaction()));

			if($singleTransaction->getTransactionState() != NULL){
				$transactionState = $singleTransaction->getTransactionState();
			}
			else{
				/** @var TransactionState $transactionState */
				$transactionState = new TransactionState();
			}
			
			if($parameters['errorLog'] != "" ){
				/** @var Document $document */
				$document = new Document();
				$document->setName("Enlace operativo employer creation error " . $employer->getIdEmployer());
				$document->setStatus(1);
				$document->setDocumentTypeDocumentType($documentType);
				
				$filename = "tempErrorImg.zip";
				$file = "uploads/temp/$filename";
				
				file_put_contents($file, base64_decode($parameters['errorLog']));
				
				$mediaManager = $this->container->get('sonata.media.manager.media');
				$media = $mediaManager->create();
				$media->setBinaryContent($file);
				$media->setProviderName('sonata.media.provider.file');
				$media->setName($document->getName());
				$media->setProviderStatus(Media::STATUS_OK);
				$media->setContext('person');
				$media->setDocumentDocument($document);
				$media->setContentType('application/zip');
				
				$document->setMediaMedia($media);
				
				$em->persist($document);
				
				$transactionState->setDocument($document);
			}
			
			if($parameters['errorMessage'] != ""){
				$transactionState->setLog($parameters['errorMessage']);
			}
			
			$transactionState->setOriginTransaction($singleTransaction);
			$em->persist($transactionState);

			$singleTransaction->setTransactionState($transactionState);
			$em->persist($singleTransaction);
			$em->flush();
			
			if($parameters['errorLog'] != "" ) {
				unlink($file);
			}
			
		}
		// Succesfull operation.
		$view = View::create();
		$view->setStatusCode(200);
		$view->setData(array("returnCode" => 0 , "returnDescription" => "Message received"));
		return $view;
	}

	/**
	 * @PUT("notificacion/pila/carga/archivo/procesar")
	 * Updates the proper data based on the received info.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Updates the proper data based on the received info.",
	 *   statusCodes = {
	 *     200 = "OK",
	 *     400 = "Bad Request",
	 *     401 = "Unauthorized",
	 *     422 = "Parameter format invalid"
	 *   }
	 * )
	 *
	 * @param Request $request .
	 * Rest Parameters:
	 *
	 * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="Radicated number, generated by hightech on the service postRegisterEmployerPilaOperatorAction")
	 * (name="planillaState", nullable=false, requirements="(.)*")
	 * (name="errorLog", nullable=true)
	 * (name="errorMessage", nullable=true)
	 * (name="planillaNumber", nullable=true)
	 *
	 * @return View
	 */
	public function putProcessUploadFilePilaOperatorAction(Request $request)
	{
		$parameters = $request->request->all();
		$view = View::create();

		$em = $this->getDoctrine()->getManager();

		$transactionRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Transaction");
		/** @var Transaction $singleTransaction */
		$singleTransaction = $transactionRepo->findOneBy(array('radicatedNumber' => $parameters['radicatedNumber']));

		if($singleTransaction == NULL){
			$view->setStatusCode(404);
			$view->setData(array("returnCode" => 404 , "returnDescription" => "Radicated number not found"));
			return $view;
		}
		
		$estadoPlanilla = $parameters['planillaState'];
		$errorLog = $parameters['errorLog'];
		
		if( $estadoPlanilla == 0 && $errorLog == "" ){
			//This means the planilla was created succesfully
			$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaOK'));
			$singleTransaction->setPurchaseOrdersStatus($purchaseOrdersStatus);

			$purchaseOrderDescription = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findOneBy(array('uploadedFile' => $singleTransaction->getIdTransaction()));
			
			if($purchaseOrderDescription != NULL){
				$purchaseOrderDescription->setUploadedFile(-1);
				$purchaseOrderDescription->setEnlaceOperativoFileName($parameters['planillaNumber']);
				
				$em->persist($purchaseOrderDescription);
				$em->flush();
			}
			
		}
		elseif ( $estadoPlanilla == 0 && $errorLog != ""){
			//This means the planilla was created succesfully but has warnings
			$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaWar'));
			$documentType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode' => 'EOIE'));
			$singleTransaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
			
			$purchaseOrderDescription = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findOneBy(array('uploadedFile' => $singleTransaction->getIdTransaction()));
			if($purchaseOrderDescription != NULL){
				$purchaseOrderDescription->setEnlaceOperativoFileName($parameters['planillaNumber']);
			}
			
			if($singleTransaction->getTransactionState() != NULL){
				$transactionState = $singleTransaction->getTransactionState();
			}
			else{
				/** @var TransactionState $transactionState */
				$transactionState = new TransactionState();
			}
			
			/** @var Document $document */
			$document = new Document();
			
			if($purchaseOrderDescription != NULL){
				$document->setName("Enlace operativo uploaded file warning " . $purchaseOrderDescription->getIdPurchaseOrdersDescription());
			}
			else{
				$document->setName("Enlace operativo uploaded file warning ");
			}
			$document->setStatus(1);
			$document->setDocumentTypeDocumentType($documentType);
			
			$filename = "tempWarningImg.zip";
			$file = "uploads/temp/$filename";
			
			file_put_contents($file, base64_decode($parameters['errorLog']));
			
			$mediaManager = $this->container->get('sonata.media.manager.media');
			$media = $mediaManager->create();
			$media->setBinaryContent($file);
			$media->setProviderName('sonata.media.provider.file');
			$media->setName($document->getName());
			$media->setProviderStatus(Media::STATUS_OK);
			$media->setContext('person');
			$media->setDocumentDocument($document);
			$media->setContentType('application/zip');
			
			$document->setMediaMedia($media);
			
			$em->persist($document);
			
			$transactionState->setDocument($document);
			$transactionState->setLog($parameters['errorMessage']);
			$transactionState->setOriginTransaction($singleTransaction);
			$em->persist($transactionState);
			
			$singleTransaction->setTransactionState($transactionState);
			$em->persist($singleTransaction);
			
			if($purchaseOrderDescription != NULL){
				$em->persist($purchaseOrderDescription);
			}
			
			$em->flush();
			
			unlink($file);
		}
		else if( $parameters['planillaState'] != -1  ){
			$purchaseOrdersStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus')->findOneBy(array('idNovoPay' => 'CarPla-PlaErr'));
			$documentType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array('docCode' => 'EOIE'));
			$singleTransaction->setPurchaseOrdersStatus($purchaseOrdersStatus);
			
			$purchaseOrderDescription = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findOneBy(array('uploadedFile' => $singleTransaction->getIdTransaction()));
			
			if($singleTransaction->getTransactionState() != NULL){
				$transactionState = $singleTransaction->getTransactionState();
			}
			else{
				/** @var TransactionState $transactionState */
				$transactionState = new TransactionState();
			}
			
			if($parameters['errorLog'] != "" ) {
				/** @var Document $document */
				$document = new Document();
				if($purchaseOrderDescription != NULL){
					$document->setName("Enlace operativo uploaded file error " . $purchaseOrderDescription->getIdPurchaseOrdersDescription());
				}
				else{
					$document->setName("Enlace operativo uploaded file error ");
				}
				$document->setStatus(1);
				$document->setDocumentTypeDocumentType($documentType);
				
				$filename = "tempErrorImg.zip";
				$file = "uploads/temp/$filename";
				
				file_put_contents($file, base64_decode($parameters['errorLog']));
				
				$mediaManager = $this->container->get('sonata.media.manager.media');
				$media = $mediaManager->create();
				$media->setBinaryContent($file);
				$media->setProviderName('sonata.media.provider.file');
				$media->setName($document->getName());
				$media->setProviderStatus(Media::STATUS_OK);
				$media->setContext('person');
				$media->setDocumentDocument($document);
				$media->setContentType('application/zip');
				
				$document->setMediaMedia($media);
				
				$em->persist($document);
				
				$transactionState->setDocument($document);
			}
			
			if($parameters['errorMessage'] != ""){
				$transactionState->setLog($parameters['errorMessage']);
			}
			
			$transactionState->setOriginTransaction($singleTransaction);
			$em->persist($transactionState);

			$singleTransaction->setTransactionState($transactionState);
			$em->persist($singleTransaction);
			$em->flush();
			
			if($parameters['errorLog'] != "" ) {
				unlink($file);
			}
		}
		// Succesfull operation.
		$view = View::create();
		$view->setStatusCode(200);
		$view->setData(array("returnCode" => 0 , "returnDescription" => "Message received"));
		return $view;
	}
	
	/**
	 * @POST("notificacion/account/state")
	 * set banck account state.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "set banck account state",
	 *   statusCodes = {
	 *     200 = "OK",
	 *     400 = "Bad Request",
	 *     401 = "Unauthorized",
	 *     422 = "Parameter format invalid"
	 *   }
	 * )
	 *
	 * @param Request $request .
	 * Rest Parameters:
	 *
	 * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="Radicated number, generated by hightech on the service postRegisterBankAccountAction")
	 * (name="accountState", nullable=false, requirements="([0-9])+", strict=true, description="state of the operation, where: 0 OK, 1 NOTOk."
	 * (name="errorLog", nullable=true, requirements="(.)*")
	 * (name="errorMessage", nullable=true, requirements="(.)*")
	 *
	 * @return View
	 */
	public function postNotifyAccountStateAction(Request $request)
	{
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['radicatedNumber'] = '[0-9]+';
		$mandatory['radicatedNumber'] = true;
		$regex['accountState'] = '(.)*';
		$mandatory['accountState'] = true;
		
		$radicatedNumber = $parameters['radicatedNumber'];
		$accountState = $parameters['accountState'];
		
		/** @var Transaction $transaction */
		$transaction = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Transaction")
			->findOneBy(array('radicatedNumber' => $radicatedNumber));
		$em = $this->getDoctrine()->getEntityManager();
		
		if($accountState == 0) {
			$podStatusIscripcionAprovada = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")
				->findOneBy(array('idNovoPay' => 'InsCue-Apr'));
			$transaction->setPurchaseOrdersStatus($podStatusIscripcionAprovada);
			
			$em->persist($transaction);
			$em->flush();
		} else {
			$podStatusIscripcionRechazada = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")
				->findOneBy(array('idNovoPay' => 'InsCue-Rec'));
			$transaction->setPurchaseOrdersStatus($podStatusIscripcionRechazada);
		
			$em->persist($transaction);
			$em->flush();

			$employers = $transaction->getEmployers();
			if($employers->count() == 1) {
				
				/** @var Employer $employer */
				$employer = $employers->get(0);
				$phone = '';
				if($employer->getPersonPerson()->getPhones()->count() > 0) {
					$phone = $employer->getPersonPerson()->getPhones()->get(0)->getPhoneNumber();
				}
				$context = array(
					'emailType' => 'errorInBanckAccountRegistration',
					'phone' => $phone,
					'documentType' => $employer->getPersonPerson()->getDocumentType(),
					'documentNumber' => $employer->getPersonPerson()->getDocument(),
					'userName' => $employer->getPersonPerson()->getNames(),
					'radicatedNumber' => $parameters['radicatedNumber'],
					'errorLog' => isset($parameters['errorLog']) ? $parameters['errorLog'] : '',
					'errorMessage' => isset($parameters['errorMessage']) ? $parameters['errorMessage'] : '',
				);
				
				$this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
			}
		}
		$view = View::create();
		$view->setStatusCode(200);
		$view->setData(array());
		return $view;
	}
}
