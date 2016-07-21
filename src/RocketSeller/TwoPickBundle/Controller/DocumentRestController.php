<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use \Application\Sonata\MediaBundle\Entity\Media;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Form\DocumentRegistration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentRestController extends FOSRestController
{

    /**
     * upload signature to a specific payroll
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "upload signature to a specific payroll",
     *   statusCodes = {
     *     200 = "Created successfully"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="signature", nullable=false, strict=true, description="the signature img")
     * @RequestParam(name="idPerson", nullable=false, strict=true, description="id person employer")
     * @RequestParam(name="idPayroll", nullable=false, strict=true, description="id associated payroll")
     *
     * @return View
     */
    public function postUploadSignatureAction(ParamFetcher $paramFetcher) {
      $em = $this->getDoctrine()->getManager();

      $idPerson = $paramFetcher->get('idPerson');
      $person = $this->getDoctrine()
              ->getRepository('RocketSellerTwoPickBundle:Person')
              ->find($idPerson);
      $idPayroll = $paramFetcher->get('idPayroll');
      $payroll = $this->getDoctrine()
              ->getRepository('RocketSellerTwoPickBundle:Payroll')
              ->find($idPayroll);

      $rawSignature = $paramFetcher->get('signature');
      $img = str_replace('data:image/png;base64,', '', $rawSignature);
      $img = str_replace(' ', '+', $img);
      $data = base64_decode($img);
      file_put_contents("tempSignature.png", $data);
      
      $documentType = $this->getDoctrine()
              ->getRepository('RocketSellerTwoPickBundle:DocumentType')
              ->find(27); //Firma

      $document = new Document();
      $document->setPersonPerson($person);
      $document->setName('Signature');
      $document->setStatus(1);
      $document->setDocumentTypeDocumentType($documentType);
      $em->persist($document);

      $payroll->setSignature($document);
      $em->persist($payroll);

      $media = new Media();

      $tmp_file =  new UploadedFile( 'tempSignature.png', 'signature.png', 'image/png', null, null, true);
      $media->setBinaryContent($tmp_file);
      $media->setProviderName('sonata.media.provider.file');
      $media->setName($document->getName());
      $media->setProviderStatus(Media::STATUS_OK);

      $hashFile = hash_file('sha256', $tmp_file);
      $hashName = hash('sha256', $hashFile . date("Y.m.d") . date("h:i:sa"));
      $media->setProviderReference($hashName . ".png");

      $media->setContext('firma');
      $media->setDocumentDocument($document);

      $em->persist($media);
      $em->flush();
      unlink($tmp_file);

      $view = View::create();
      $view->setStatusCode(200);

      return $view->setData(array());
    }

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
     * @RequestParam(name="idPerson", nullable=false, strict=true, description="id person employer")
     *
     * @return View
     */
    public function postUploadSinglePageImageAction(ParamFetcher $paramFetcher) {
        $em = $this->getDoctrine()->getManager();

        $pageImage = $paramFetcher->get('pageImage');
        $idPerson = $paramFetcher->get('idPerson');

        $person = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Person')
            ->find($idPerson);

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

        if (!file_exists("uploads/tempDocumentPages/$idPerson")) {
            mkdir("uploads/tempDocumentPages/$idPerson", 0777, true);
        }

        $path = "uploads/tempDocumentPages/$idPerson/$hashName.$format";
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
     *     200 = "Created successfully"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(array=true, name="pages", nullable=false, strict=true, description="hash name of document pages")
     * @RequestParam(name="idPerson", nullable=false, strict=true, description="id person employer")
     * @RequestParam(name="idDocumentType", nullable=false, strict=true, description="id document type")
     * @RequestParam(name="idNotification", nullable=false, strict=true, description="id of notification to be removed")
     *
     * @return View
     */
    public function postCreateDocumentFromImgPagesAction(ParamFetcher $paramFetcher) {
        $em = $this->getDoctrine()->getManager();

        $pages = $paramFetcher->get('pages');
        $idPerson = $paramFetcher->get('idPerson');
        $idDocumentType = $paramFetcher->get('idDocumentType');
        $idNotification = $paramFetcher->get('idNotification');
        $person = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Person')
            ->find($idPerson);

        $documentType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:DocumentType')
            ->find($idDocumentType);

        $notification = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Notification')
            ->find($idNotification);

        $document = new Document();
        $document->setPersonPerson($person);
        $document->setName('TestDocument');
        $document->setStatus(1);
        $document->setDocumentTypeDocumentType($documentType);
        $em->persist($document);

        $mediaManager = $this->container->get('sonata.media.manager.media');
        $media = $mediaManager->create();
        $absPath = getcwd();
        $absPath = str_replace('/', '_', $absPath);
        $HashNames = "";
        foreach($pages as $hashName) {
            $path = $absPath . '_uploads_tempDocumentPages_' . $idPerson . '_' . $hashName;
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
        $file_path = "uploads/tempDocumentPages/tempFile.png";
        file_put_contents($file_path, $file);

        $media->setBinaryContent($file_path);
        $media->setProviderName('sonata.media.provider.file');
        $media->setName($document->getName());
        $media->setProviderStatus(Media::STATUS_OK);
        $media->setContext('person');
        $media->setDocumentDocument($document);

        $em->persist($media);

        $notification->setStatus(0);
        $em->persist($notification);

        $em->flush();
        unlink($file_path);

        foreach($pages as $hashName) {
            $path = "uploads/tempDocumentPages/$idPerson/$hashName";
            unlink($path);
        }
        rmdir("uploads/tempDocumentPages/$idPerson");

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array());
    }
}
