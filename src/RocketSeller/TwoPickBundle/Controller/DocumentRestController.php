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

class DocumentRestController extends FOSRestController
{

    /**
     * upload signature to a specific payroll
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "upload signature to a specific payroll",
     *   statusCodes = {
     *     200 = "Created successfuly",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param aramFetcher $paramFetcher Paramfetcher
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

      $mediaManager = $this->container->get('sonata.media.manager.media');
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

      $tmp_file =  new UploadedFile( 'tempSignature.png', 'signature.png', 'image/png',null,null,true);
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
      $view->setData(array(
          'signature' => $hashName
        ))->setStatusCode(200);

      return $this->handleView($view);
    }

}
