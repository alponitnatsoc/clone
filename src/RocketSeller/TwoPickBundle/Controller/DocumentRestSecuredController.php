<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use \Application\Sonata\MediaBundle\Entity\Media;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Form\DocumentRegistration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentRestSecuredController extends FOSRestController
{

  /**
   * download document <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "get download document",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param String $ref type of document.
   * @param String $id id payroll.
   * @param String $type html | pdf.
   *
   * @return View
   */
  public function getDownloadDocumentAction($ref,$id, $type = "html")
  {
      $params = array(
        'ref' => $ref,
        'id' => $id,
        'type' => $type,
        'attach'=> null
      );

      $result = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
      $view = View::create();
      $view->setStatusCode(200);

      return $result;
   }

   /**
    * upload payslip <br/>
    *
    * @ApiDoc(
    *   resource = true,
    *   description = "upload payslip",
    *   statusCodes = {
    *     200 = "OK",
    *     400 = "Bad Request",
    *     401 = "Unauthorized",
    *     404 = "Not Found"
    *   }
    * )
    *
    * @RequestParam(name="idPayroll", nullable=false, strict=true, description="id Payroll")
    * @RequestParam(name="idPerson", nullable=false, strict=true, description="id person employer")
    * @RequestParam(name="idDocumentType", nullable=false, strict=true, description="id document type")
    * @RequestParam(name="idNotification", nullable=false, strict=true, description="id of notification to be removed")
    *
    * @return View
    */
   public function postUploadPayslipAction(ParamFetcher $paramFetcher)
   {
       $idPerson = $paramFetcher->get('idPerson');
       $idPayroll = $paramFetcher->get('idPayroll');
       $idDocumentType = $paramFetcher->get('idDocumentType');
       $idNotification = $paramFetcher->get('idNotification');

       $em = $this->getDoctrine()->getManager();

       $person = $this->getDoctrine()
           ->getRepository('RocketSellerTwoPickBundle:Person')
           ->find($idPerson);

       $payroll = $this->getDoctrine()
           ->getRepository('RocketSellerTwoPickBundle:Payroll')
           ->find($idPayroll);

       $documentType = $this->getDoctrine()
           ->getRepository('RocketSellerTwoPickBundle:DocumentType')
           ->find($idDocumentType);

       $notification = $this->getDoctrine()
           ->getRepository('RocketSellerTwoPickBundle:Notification')
           ->find($idNotification);

       $params = array(
         'ref' => 'comprobante',
         'id' => $idPayroll,
         'type' => 'pdf',
         'attach'=> null
       );

       $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
       $file = $documentResult->getContent();
       $file_path = "uploads/tempDocumentPages/paysliptempFile.pdf";
       file_put_contents($file_path, $file);

       $document = new Document();
       $document->setPersonPerson($person);
       $document->setName('Comprobante_idperson_' + $idPerson);
       $document->setStatus(1);
       $document->setDocumentTypeDocumentType($documentType);
       $em->persist($document);


       $mediaManager = $this->container->get('sonata.media.manager.media');
       $media = $mediaManager->create();
       $media->setBinaryContent($file_path);
       $media->setProviderName('sonata.media.provider.file');
       $media->setName($document->getName());
       $media->setProviderStatus(Media::STATUS_OK);
       $media->setContext('person');
       $media->setDocumentDocument($document);

       $em->persist($media);


       $payroll->setPayslip($document);
       $em->persist($payroll);

       $notification->setStatus(0);
       $em->persist($notification);

       $em->flush();
       unlink($file_path);

       $view = View::create();
       $view->setStatusCode(200);

       return $view->setData(array());
    }

    /**
     * upload document <br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "upload document",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @RequestParam(name="entityType", nullable=false, strict=true, description="name of entity type")
     * @RequestParam(name="entityId", nullable=false, strict=true, description="id of object of type entityType")
     * @RequestParam(name="docCode", nullable=false, strict=true, description="three letter document code")
     * @RequestParam(name="idNotification", nullable=false, strict=true, description="id of notification to be removed")
     * @RequestParam(name="fileName", nullable=false, strict=true, description="name of temp file to be saved in db")
     *
     * @return View
     */
    public function postUploadDocumentAction(ParamFetcher $paramFetcher)
    {
        $entityType = $paramFetcher->get('entityType');
        $entityId = $paramFetcher->get('entityId');
        $docCode = $paramFetcher->get('docCode');
        $idNotification = $paramFetcher->get('idNotification');
        $fileName = $paramFetcher->get("fileName");

        $params = array(
          'entityType' => $entityType,
          'entityId' => $entityId,
          'docCode' => $docCode,
          'idNotification'=> $idNotification,
          'fileName' => $fileName
        );
        // dump($params);
        $data = $this->forward('RocketSellerTwoPickBundle:Document:verifyAndPersitDocument', $params);
        //TODO unlink temp file
        $path = "uploads/tempDocumentPages/$fileName";
        unlink($path);
        // dump($data);
        // dump($data->getContent());
        // $data = $data->getContent();
        // $data = json_decode($data->getContent());
        // $document = $data->getContent()['document'];
        // dump($data->document);
        // die;
        // $documentController = $this->get('document_service');
        // $data = $documentController->verifyDocument($entityType, $entityId, $docCode, $idNotification);
        // $idDocumentType = $data->documentType->id_document_type;
        //
        // $documentType = $this->getDoctrine()
        //     ->getRepository('RocketSellerTwoPickBundle:DocumentType')
        //     ->find($idDocumentType);
        /** @var Document $document */


        // $document->setName($data->document->name);
        // $document->setStatus($data->document->status);
        // $document->setDocumentTypeDocumentType($documentType);

        /** @var Notification $notification */
        // $notification = $this->getDoctrine()
        //     ->getRepository('RocketSellerTwoPickBundle:Notification')
        //     ->find($idNotification);

        /** @var string $personName */
        // $personName = $data->personName;
        /** @var DocumentType $documentType */


        // $file_path = "/Users/alfredo-santamaria/Desktop/comprobante.pdf";
        // $mediaManager = $this->container->get('sonata.media.manager.media');
        // $media = $mediaManager->create();
        // $media->setBinaryContent($file_path);
        // $media->setProviderName('sonata.media.provider.file');
        // $media->setName($document->getName());
        // $media->setProviderStatus(Media::STATUS_OK);
        // $media->setContext('person');
        // $media->setDocumentDocument($document);
        //
        // $document->setMediaMedia($media);

        // $params2 = array(
        //   'document' => $document,
        //   'notification' => $notification
        // );
        // $this->forward('RocketSellerTwoPickBundle:Document:persitDocument', $params2);

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array("data" => $data));
     }

 }
