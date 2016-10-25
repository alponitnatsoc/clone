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
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;

class DocumentRestSecuredController extends FOSRestController
{
    use EmployeeMethodsTrait;

  /**
   * upload single page of document (image)
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "upload single page of document (image)",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="pageImage", nullable=false, strict=true, description="image of a document page (base64)")
   * @RequestParam(name="entityType", nullable=false, strict=true, description="entity type name")
   * @RequestParam(name="entityId", nullable=false, strict=true, description="id of the instance of entity type")
   *
   * @return View
   */
  public function postUploadSinglePageImageAction(ParamFetcher $paramFetcher) {
      $em = $this->getDoctrine()->getManager();

      $pageImage = $paramFetcher->get('pageImage');
      $entityType = $paramFetcher->get('entityType');
      $entityId = $paramFetcher->get('entityId');


      $format = null;
      $len = strlen($pageImage);
      if (strrpos($pageImage, 'png', (21-$len))) $format = 'png';
      elseif (strrpos($pageImage, 'jpg', (21-$len))) $format = 'jpg';
      elseif (strrpos($pageImage, 'jpeg', (21-$len))) $format = 'jpeg';

      if($format == null) {
          $view = View::create();
          $view->setStatusCode(400);
          return $view->setData(array());
      }

      $img = str_replace("data:image/$format;base64,", '', $pageImage);
      $img = str_replace(' ', '+', $img);
      $data = base64_decode($img);

      $hashName= hash('sha256', $pageImage . date("Y.m.d") . date("h:i:sa"));

      $dir = "uploads/tempDocumentPages";
      if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
      }
      $dir .= "/$entityType";
      if (!file_exists($dir)) {
          mkdir($dir, 0777, true);
      }
      $dir .= "/$entityId";
      if (!file_exists($dir)) {
          mkdir($dir, 0777, true);
      }

      $path = "$dir/$hashName.$format";
      file_put_contents($path, $data);
      $view = View::create();
      $view->setStatusCode(200);

      return $view->setData(array(
          'hashName' => "$hashName.$format"
      ));
  }

  /**
   * create document from multiple image pages in pdf format
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "create document from multiple image pages in pdf format",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(array=true, name="pages", nullable=false, strict=true, description="hash name of document pages")
   * @RequestParam(name="entityType", nullable=false, strict=true, description="entity type name")
   * @RequestParam(name="entityId", nullable=false, strict=true, description="id of the instance of entity type")
   *
   * @return View
   */
  public function postCreateDocumentFromImgPagesAction(ParamFetcher $paramFetcher) {

      $em = $this->getDoctrine()->getManager();
      $pages = $paramFetcher->get('pages');
      $entityType = $paramFetcher->get('entityType');
      $entityId = $paramFetcher->get('entityId');

      $absPath = getcwd();
      $absPath = str_replace('/', '_', $absPath);
      $HashNames = "";
      foreach($pages as $hashName) {
          if($hashName == null || $hashName == "") {
            $view = View::create();
            $view->setStatusCode(400);

            return $view->setData(array());
          }
          $path = $absPath . '_uploads_tempDocumentPages_' . $entityType .
                             '_' . $entityId . '_' . $hashName;
          $HashNames .= $path . ',';
      }
      $params = array(
          'ref'=> 'joiner',
          'id' => $HashNames,
          'type' => 'pdf',
          'attach' => null
      );
      $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
      $file = $documentResult->getContent();
      $fileName = "tempFile.pdf";
      $file_path = "uploads/tempDocumentPages/$fileName";
      file_put_contents($file_path, $file);

      foreach($pages as $hashName) {
          $path = "uploads/tempDocumentPages/$entityType/$entityId/$hashName";
          unlink($path);
      }
      // scan for other elements in the folder
      $dir = "uploads/tempDocumentPages/$entityType/$entityId";
      foreach(scandir($dir) as $file) {
          if ('.' === $file || '..' === $file) continue;
          unlink("$dir/$file");
      }
      rmdir($dir);

      $view = View::create();
      $view->setStatusCode(200);

      return $view->setData(array("fileName" => $fileName));
  }

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

        $data = $this->forward('RocketSellerTwoPickBundle:Document:verifyAndPersitDocument', $params);
        $path = "uploads/tempDocumentPages/$fileName";
        unlink($path);

        $notification = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Notification')
            ->find($idNotification);
        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->findOneBy(array('personPerson' => $notification->getPersonPerson()));

        $em = $this->getDoctrine()->getManager();

        foreach ($this->allDocumentsReady($user) as $docStat ) {

            $eHE  = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                    ->find($docStat['idEHE']);

            // Se envia el Email diahabil y se muestra el modal de documentos en validación
            if( $docStat['docStatus'] == 2) {
                $pushNotificationService = $this->get('app.symplifica_push_notification');
                $pushNotificationService->sendMessageValidatingDocuments($this->getUser()->getId());
                $eHE->setDocumentStatus(3);
                $em->persist($eHE);
                $em->flush();
            } elseif ($docStat['docStatus'] == 3) {
                //TODO
            }

            //si el usuario no tiene tareas pendientes y algun empleado fue completamente validado por backoffice
            if($docStat['docStatus'] == 13) {
                //TODO
            }

            if($docStat['docStatus'] == 11) {
                //TODO
            }
            if($docStat['docStatus'] > 13) {
                //TODO
            }
        }

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array("data" => $data));
     }

 }
