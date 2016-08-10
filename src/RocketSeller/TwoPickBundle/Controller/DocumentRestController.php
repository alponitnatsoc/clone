<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Proxies\__CG__\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
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
    public function postUploadSignatureAction(ParamFetcher $paramFetcher)
    {
        //only works for comprobantes
        $em = $this->getDoctrine()->getManager();
        //todo id person is not necessary
        $idPerson = $paramFetcher->get('idPerson');

        $idPayroll = $paramFetcher->get('idPayroll');
        $payroll = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Payroll')
            ->find($idPayroll);
        $employerHasEmployee = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee();

        $employer = $employerHasEmployee->getEmployerEmployer();
        $personEmployee = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson();

        $rawSignature = $paramFetcher->get('signature');
        $raw2 = $rawSignature;
        $img = str_replace('data:image/png;base64,', '', $rawSignature);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $fileSignatureName = "tempSignature.png";
        file_put_contents("uploads/$fileSignatureName", $data);
        $absPath = getcwd();
        $absPath = str_replace('/', '_', $absPath);
        $signaturePath = $absPath . '_uploads_' . $fileSignatureName;
        $params = array(
            'ref'=> 'comprobante',
            'id' => "$idPayroll,$raw2",
            'type' => 'html',
            'attach' => null
        );
        $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
        $file = $documentResult->getContent();
        $file_path = "uploads/tempComprobanteFile.html";
        file_put_contents($file_path, $file);


        $documentType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:DocumentType')
            ->findOneBy(array("name" => 'Comprobante'));

        $document = new Document();
        $document->setPersonPerson($personEmployee);
        $document->setEmployerEmployer($employer);
        $document->setName('Comprobante');
        $document->setStatus(1);
        $document->setDocumentTypeDocumentType($documentType);
        $em->persist($document);

        $payroll->setPayslip($document);
        $em->persist($payroll);

        $mediaManager = $this->container->get('sonata.media.manager.media');
        $media = $mediaManager->create();
        $media->setBinaryContent($file_path);
        $media->setProviderName('sonata.media.provider.file');
        $media->setName($document->getName());
        $media->setProviderStatus(Media::STATUS_OK);
        $media->setContext('person');
        $media->setDocumentDocument($document);

        $em->persist($media);
        $em->flush();
        unlink("uploads/$fileSignatureName");
        unlink($file_path);

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

        $mediaManager = $this->container->get('sonata.media.manager.media');
        $media = $mediaManager->create();
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
        //delete files that are still inside the directory
        foreach (scandir("uploads/tempDocumentPages/$idPerson") as $file) {
            if ($file == '.' || $file == '..') continue;
            unlink("uploads/tempDocumentPages/$idPerson/$file");
        }
        rmdir("uploads/tempDocumentPages/$idPerson");

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array());
    }

    /**
     * Generate EmployerId for persons with only one employer
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Correction to the Database schema to add EmployerId for documents that belong to a employee with only one Employer",
     *   statusCodes = {
     *     200 = "Correction executed correctly"
     *   }
     * )
     * @return View
     */
    public function postGenerateDocumentEmployerIdsAction()
    {

        $response = "";
        $response = $response . " Corrigiendo tabla de documentos en la base de datos<br/><br/>";
        $em = $this->getDoctrine()->getManager();
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        $response = $response . " Recorriendo los documentos...<br/><br/>";
        /** @var Document $document */
        foreach ($documents as $document) {
            if($document->getPersonPerson()->getEmployee()){
                if($document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->count()==1){
                    /** @var EmployerHasEmployee $eHE */
                    $eHE=$document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->first();
                    $document->setEmployerEmployer($eHE->getEmployerEmployer());
                }
            }
            $em->persist($document);
            $em->flush();
        }
        $response = $response . " Termino.<br/><br/>";

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Link the document contract to it's active contract for employees with only one employer
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Correction to the data base to link the unlinked contract documents for employees with only one employer",
     *   statusCodes = {
     *     200 = "Correction executed correctly"
     *   }
     * )
     * @return View
     */
    public function postLinkContractsAction()
    {

        $response = "";
        $response = $response . " Corrigiendo los contratos sin documento contrato".'<br><br>';
        $em = $this->getDoctrine()->getManager();
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        $response = $response . " Recorriendo los documentos...<br><br>";
        /** @var Document $document */
        foreach ($documents as $document) {
            if($document->getDocumentTypeDocumentType()->getName()=='Contrato'){
                if($document->getPersonPerson()->getEmployee()){
                    if($document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->count()==1){
                        /** @var EmployerHasEmployee $eHE */
                        $eHE=$document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->first();
                        $contracts = $eHE->getContracts();
                        /** @var Contract $contract */
                        foreach ($contracts as $contract){
                            if($contract->getState()==1){
                                $response = $response . " Asignando el documento del contrato...<br><br>";
                                $contract->setDocumentDocument($document);
                                $em->persist($contract);
                            }
                        }
                    }
                }
            }
            $em->persist($document);
            $em->flush();
        }
        $response = $response . " Termino.<br><br>";

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

}
