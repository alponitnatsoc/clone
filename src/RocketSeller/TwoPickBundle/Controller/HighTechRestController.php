<?php
namespace RocketSeller\TwoPickBundle\Controller;


use Application\Sonata\MediaBundle\Entity\Media;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
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
use RocketSeller\TwoPickBundle\Entity\Severances;
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
            if($pay->getPurchaseOrdersDescription()->getProductProduct()->getSimpleName()=="SVR") {
                $request->setMethod("GET");
                $response = $this->forward('RocketSellerTwoPickBundle:HighTechRest:postCheckSeverancesPayment',array(
                    "GSCAccount" => $pay->getPurchaseOrdersDescription()->getPurchaseOrders()->getIdUser()->getPersonPerson()->getEmployer()->getIdHighTech(),
                    "payslipNumber" => $pay->getPurchaseOrdersDescription()->getEnlaceOperativoFileName(),
                    "podId"=>$pay->getPurchaseOrdersDescription()->getIdPurchaseOrdersDescription(),
                ), array('_format' => 'json'));
                $message = "¡Hemos realizado el pago de cesantías con éxito!";
                $title = "Symplifica";
                $longMessage = "¡Hemos realizado El pago de las cesantías con éxito!";

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
     * Correct severances document<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "get all the pods and correct the payslip from each severance",
     *   statusCodes = {
     *     200 = "Ok",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="pod_id", nullable=true, requirements="([0-9])+", description="Pod id to correct")
     * @return View
     */
    public function postCorrectSeverancesDocumentAction(ParamFetcher $paramFetcher){
        $em = $this->getDoctrine()->getManager();
            $pods=array();
        if($paramFetcher->get("pod_id")!=null){
            $pods[]=$em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->find($paramFetcher->get("pod_id"));
        }else{
            $pods = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->findBy(array(
                'productProduct'=>$em->getRepository("RocketSellerTwoPickBundle:Product")->findBy(array("simpleName"=>'SVR')),
                'purchaseOrdersStatus'=>$em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findBy(array('idNovoPay'=>'-1'))
            ));
        }

        $result = array();
        /** @var PurchaseOrdersDescription $pod */
        foreach ($pods as $pod) {
            $cgsAccount = $pod->getPurchaseOrders()->getIdUser()->getPersonPerson()->getEmployer()->getIdHighTech();
            $podId = $pod->getIdPurchaseOrdersDescription();
            $enlaceOperativofileName = $pod->getEnlaceOperativoFileName();
            $em = $this->getDoctrine()->getManager();
            if($pod==null){
                $result[$podId]="error pod";
                continue;
            }
            if($pod->getSeverance()==null){
                $result[$podId]="error severance";
                continue;
            }
            /** @var Severances $severance */
            $severance = $pod->getSeverance();
            if($severance->getPayslip() != null){
                $result[$podId]="ya estaba corregida";
                continue;
            }
            $request  = new Request();
            $request->setMethod("GET");
            $response = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:getSeverancesPayment', array(
                "GSCAccount" => $cgsAccount,
                "filename" => $enlaceOperativofileName
            ), array('_format' => 'json'));
            $data = json_decode($response->getContent(), true);
            if($data['codigoRespuesta'] == "OK" and $data['descripcionRespuesta']=="Archivo Generado"){
                /** @var DocumentType $docType */
                $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array('docCode'=>'CPRCES'));
                $document = new Document();
                $document->setName("Comprobante pago cesantías ".$pod->getIdPurchaseOrdersDescription());
                $document->setStatus(1);
                $document->setDocumentTypeDocumentType($docType);

                if (!file_exists('uploads/temp/comprobantes')) {
                    mkdir('uploads/temp/comprobantes', 0777, true);
                }
                $filename = "tempComprobanteCesantias".$pod->getIdPurchaseOrdersDescription().".pdf";
                $file = "uploads/temp/comprobantes/$filename";
                file_put_contents($file, base64_decode($data['comprobanteBase64']));

                $mediaManager = $this->container->get('sonata.media.manager.media');
                $media = $mediaManager->create();
                $media->setBinaryContent($file);
                $media->setProviderName('sonata.media.provider.file');
                $media->setName($document->getName());
                $media->setProviderStatus(Media::STATUS_OK);
                $media->setContext('person');
                $media->setDocumentDocument($document);
                $document->setMediaMedia($media);
                $em->persist($document);
                $em->flush();
                $severance->setPayslip($document);
                $em->persist($severance);
                $em->flush();
                unlink($file);
                $result[$podId]='Se corrigio el pod '.$podId." con severance ".$severance->getIdSeverances();
            }else{
                $result[$podId]="error servicio, GSCAccount: ".$cgsAccount." fileName: ".$enlaceOperativofileName;
                $result["additional_info"]="codigoRespuesta => ".$data["codigoRespuesta"]." descripcionRespuesta => ".$data["descripcionRespuesta"];
            }
        }
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($result);
        return $view;
    }


    /**
     * Check with hightech if severances were paid <br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Check with hightech if severances were pai",
     *   statusCodes = {
     *     200 = "Ok",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="GSCAccount", nullable=false, requirements="([0-9])+", description="HighTech employer Id.")
     * @RequestParam(name="filename", nullable=true, requirements="([a-z|A-Z|0-9 ])+", description="POD hightech filenem to download the proof of severances payment")
     * @RequestParam(name="podId", nullable=false, requirements="([0-9])+", description="PurchaseOrderDescription Id.")
     *
     * @return View
     */
    public function postCheckSeverancesPaymentAction(ParamFetcher $paramFetcher){

        if($paramFetcher->get("GSCAccount")){
             $GSCAccount = $paramFetcher->get("GSCAccount");
        }else{
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData("Error: GSCAccount not fount in request parameters.");
            return $view;
        }
        if($paramFetcher->get("filename")){
            $filename = trim($paramFetcher->get("filename"));
        }else{
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData("Error: HighTech filename not fount in request parameters.");
            return $view;
        }
        if($paramFetcher->get("podId")){
            $podId = $paramFetcher->get("podId");
        }else{
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData("Error: Purchase Order Description Id not fount in request parameters.");
            return $view;
        }
        $request  = $this->container->get("request");
        $request->setMethod("GET");
        $response = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:getSeverancesPayment', array(
            "GSCAccount" => $GSCAccount,
            "filename" => $filename
        ), array('_format' => 'json'));

        $data = json_decode($response->getContent(), true);

//        for local test

//        if(true){
//            $data =array();
//            $data["codigoRespuesta"] = "OK";
//            $data["descripcionRespuesta"] = "Archivo Generado";
//            $data["comprobanteBase64"] = "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YS9UeXBlL0ZvbnQvRW5jb2RpbmcvV2luQW5zaUVuY29kaW5nL1N1YnR5cGUvVHlwZTE+PgplbmRvYmoKMyAwIG9iajw8L0Jhc2VGb250L0hlbHZldGljYS1Cb2xkL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iago2IDAgb2JqPDwvVHlwZS9QYWdlcy9Db3VudCAxL0tpZHNbMSAwIFJdPj4KZW5kb2JqCjggMCBvYmo8PC9OYW1lc1soSlJfUEFHRV9BTkNIT1JfMF8xKSA3IDAgUl0+PgplbmRvYmoKOSAwIG9iajw8L0Rlc3RzIDggMCBSPj4KZW5kb2JqCjEwIDAgb2JqPDwvTmFtZXMgOSAwIFIvVHlwZS9DYXRhbG9nL1ZpZXdlclByZWZlcmVuY2VzPDwvUHJpbnRTY2FsaW5nL0FwcERlZmF1bHQ+Pi9QYWdlcyA2IDAgUj4+CmVuZG9iagoxMSAwIG9iajw8L0NyZWF0b3IoSmFzcGVyUmVwb3J0cyBcKFByb3B1ZXN0YV9JbmZvcm1lQ29tcGxldG9cKSkvUHJvZHVjZXIoaVRleHQgMi4xLjAgXChieSBsb3dhZ2llLmNvbVwpKS9Nb2REYXRlKEQ6MjAxNzAyMDkxNzA2NDAtMDUnMDAnKS9DcmVhdGlvbkRhdGUoRDoyMDE3MDIwOTE3MDY0MC0wNScwMCcpPj4KZW5kb2JqCnhyZWYKMCAxMgowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDc3NzQgMDAwMDAgbiAKMDAwMDAwODA3MyAwMDAwMCBuIAowMDAwMDA4MTYwIDAwMDAwIG4gCjAwMDAwMDAwMTUgMDAwMDAgbiAKMDAwMDAwNDg2NSAwMDAwMCBuIAowMDAwMDA4MjUyIDAwMDAwIG4gCjAwMDAwMDgwMzkgMDAwMDAgbiAKMDAwMDAwODMwMiAwMDAwMCBuIAowMDAwMDA4MzU1IDAwMDAwIG4gCjAwMDAwMDgzODYgMDAwMDAgbiAKMDAwMDAwODQ4OSAwMDAwMCBuIAp0cmFpbGVyCjw8L1Jvb3QgMTAgMCBSL0lEIFs8NmVjYWVkZDVjNzFmMDVjNDI3YjE1N2ExNjQxNmJjMzM+PDkyMjQxMDBjNWYwMDQ5ZjM2ZmRhOWJkZmQ3NmE0ZDllPl0vSW5mbyAxMSAwIFIvU2l6ZSAxMj4+CnN0YXJ0eHJlZgo4Njc0CiUlRU9GCg==";
//        }


        if($data['codigoRespuesta'] == "OK" and $data['descripcionRespuesta']=="Archivo Generado"){
            $em = $this->getDoctrine()->getManager();
            /** @var PurchaseOrdersDescription $pod */
            $pod = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription")->find($podId);
            if($pod==null){
                $view = View::create();
                $view->setStatusCode(404);
                $view->setData("Error: Purchase Order Description with Id ".$podId." not found.");
                return $view;
            }
            if($pod->getSeverance()==null){
                $view = View::create();
                $view->setStatusCode(404);
                $view->setData("Error: Severances not found.");
                return $view;
            }
            /** @var Severances $severance */
            $severance = $pod->getSeverance();
            /** @var DocumentType $docType */
            $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array('docCode'=>'CPRCES'));

            $document = new Document();
            $document->setName("Comprobante pago cesantías ".$pod->getIdPurchaseOrdersDescription());
            $document->setStatus(1);
            $document->setDocumentTypeDocumentType($docType);

            if (!file_exists('uploads/temp/comprobantes')) {
                mkdir('uploads/temp/comprobantes', 0777, true);
            }
            $filename = "tempComprobanteCesantias".$pod->getIdPurchaseOrdersDescription().".pdf";
            $file = "uploads/temp/comprobantes/$filename";
            file_put_contents($file, base64_decode($data['comprobanteBase64']));

            $mediaManager = $this->container->get('sonata.media.manager.media');
            $media = $mediaManager->create();
            $media->setBinaryContent($file);
            $media->setProviderName('sonata.media.provider.file');
            $media->setName($document->getName());
            $media->setProviderStatus(Media::STATUS_OK);
            $media->setContext('person');
            $media->setDocumentDocument($document);
            $document->setMediaMedia($media);
            $em->persist($document);
            $em->flush();
            $severance->setPayslip($document);
            $em->persist($severance);
            $em->flush();
            unlink($file);
            $file = $this->get("app.symplifica_utils")->getLocalDocumentPath($document);
            $context=array(
                'emailType'=>'successSeverancesPayment',
                'userEmail'=>$pod->getPurchaseOrders()->getIdUser()->getEmail(),
                'toEmail'=>$pod->getPurchaseOrders()->getIdUser()->getEmail(),
                'userName'=>$pod->getPurchaseOrders()->getIdUser()->getPersonPerson()->getNames(),
                'path'=>$file,
                'comprobante'=>true,
                'documentName'=> 'Comprobante Pago Cesantias_'.$pod->getIdPurchaseOrdersDescription().'_'.date_format(new DateTime(),'d-m-y H:i:s').'.pdf'
            );
            $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            $view = View::create()->setStatusCode(200)->setData("Ceverances payment document atached correctly");
            return $view;

        }else{
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData($data);
            return $view;
        }

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
