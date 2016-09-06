<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\DocumentRegistration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\Bundle\DemoBundle\Model\MediaPreview;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Application\Sonata\MediaBundle\Entity\Media;
use Application\Sonata\MediaBundle\Entity\Gallery;
use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\ContractMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\BasicPersonDataMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\EmployerMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use Symfony\Component\HttpFoundation\JsonResponse;
use ZipArchive;
use DateTime;
use FOS\RestBundle\View\View;

class DocumentController extends Controller
{

    use ContractMethodsTrait;

use BasicPersonDataMethodsTrait;

use EmployerHasEmployeeMethodsTrait;

use EmployeeMethodsTrait;

use EmployerMethodsTrait;

    public function showDocumentsAction($id)
    {
        $person = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
        $documents = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Document')
                ->findByPersonPerson($person);
        return $this->render(
                        'RocketSellerTwoPickBundle:Employee:documents.html.twig', array(
                    'person' => $person,
                    'documents' => $documents
        ));
    }

    public function downloadContractAction($id)
    {
        switch ($id) {
            case 1:
                $filename = "terminoFijo.pdf";
                break;
            case 2:
                $filename = "terminoIndefinido.pdf";
                break;
        }
        $path = $this->get('kernel')->getRootDir() . "/../web/public/";
        $content = file_get_contents($path . $filename);

        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
    }

    public function downloadAuthAction()
    {

        $filename = "cartaAuth.pdf";
        $path = $this->get('kernel')->getRootDir() . "/../web/public/";
        $content = file_get_contents($path . $filename);

        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
    }

    public function addDocumentAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
        $document = new Document();
        $document->setPersonPerson($person);
        $document->setName('nombre');
        $form = $this->createForm(new DocumentRegistration(), $document);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $medias = $document->getMediaMedia();
            /** @var Media $media */
            foreach ($medias as $media) {
                $media->setBinaryContent($media);
                $media->setName('documento');
                $media->setProviderStatus(Media::STATUS_OK);
                $media->setProviderReference($media->getBinaryContent());
                $em->persist($media);
                $em->flush();
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();

            return $this->redirect('/pages?redirector=/matrix/choose');
        }
        return $this->render(
                        'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig', array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'idDocumentType' => 39,
                    'idNotification' => 0
        ));
    }

    //JPG,PNG,TIF, BMP, DOC,PDF

    /**
     * Function to upload documents from the notification
     * @param String $entityType name of the entity related with the document
     * @param Integer $entityId id of the row from the entitytype table
     * @param String $docCode unique code of the documentType
     * @param Integer $idNotification id of the notification to change status
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addDocAction($entityType, $entityId, $docCode, $idNotification, Request $request)
    {
        // setting the document types alowed by the application
        $fileTypePermitted = array(
            'image/png',
            'image/jpeg',
            'application/pdf',
            //'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $data = $this->verifyDocument($entityType, $entityId, $docCode, $idNotification);
        /** @var Document $document */
        $document = $data['document'];
        /** @var Notification $notification */
        $notification = $data['notification'];
        /** @var string $personName */
        $personName = $data['personName'];
        /** @var DocumentType $documentType */
        $documentType = $data['documentType'];
//        $form2 = $this->createFormBuilder()
//            ->add('archivo','file')
//            ->getForm();
//        $form2 = $this->createFormBuilder()
//            ->add('archivo','collection',array(
//                'type'=>'file',
//                'required'=>false,
//            ))
//            ->getForm();

        $form = $this->createForm(new DocumentRegistration(), $document);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (in_array($document->getMediaMedia()->getContentType(), $fileTypePermitted)) {
                $this->persitDocument($document ,$notification);
                return $this->redirectToRoute('show_dashboard');
            } else {
                $this->addFlash('fail_format', 'NVF');
                return $this->redirectToRoute('show_dashboard');
            }
        }
        return $this->render(
            'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig', array('form' => $form->createView(), "entityType" => $entityType, "entityId" => $entityId, "docCode" => $docCode, "idNotification" => $idNotification, 'personName' => $personName, "documentName" => $documentType->getName()));
    }

    public function persitDocument(Document $document, Notification $notification){
        $em = $this->getDoctrine()->getManager();
        /** @var Media $media */
        $medias = $document->getMediaMedia();
        foreach ($medias as $media) {
            $media->setBinaryContent($media);
            $media->setName($document->getName());
            $media->setProviderStatus(Media::STATUS_OK);
            $media->setProviderReference($media->getBinaryContent());
            $em->persist($media);
            $em->flush();
        }

        $em = $this->getDoctrine()->getManager();
        $document->setStatus(1);
        $em->persist($document);
        $em->flush();
        $notification->setStatus(0);
        $em->persist($notification);
        $em->flush();
        $request = $this->container->get('request');
        $request->setMethod("GET");
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:EmployerRest:getEmployerDocumentsState', array('idUser' => $this->getUser()->getId()), array('_format' => 'json'));
        $responsePaymentsMethods = json_decode($insertionAnswer->getContent(), true);
    }

    public function verifyDocument($entityType, $entityId, $docCode, $idNotification){
        $em = $this->getDoctrine()->getManager();
        /** @var Notification $notification */
        $notification = $em->getRepository("RocketSellerTwoPickBundle:Notification")->find($idNotification);
        // getting the documentType from the database and checking if it is a valid document type
        /** @var DocumentType $documentType */
        $documentType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:DocumentType')
            ->findOneBy(array("docCode"=>$docCode));
        $exists = false;
        /** @var Document $document */
        //switching between entities
        switch ($entityType){
            case "Person":
                $corrected = false;
                /** @var Person $person */
                $person = $em->getRepository("RocketSellerTwoPickBundle:Person")->find($entityId);
                $name = $person->getFullName();
                //switching between doctypes
                switch ($docCode){
                    case "CC":
                        if($person->getDocumentDocument()){
                            $document = $person->getDocumentDocument();
                            $corrected = true;
                            if($document->getMediaMedia()){
                                /** @var Media $media */
                                $media = $document->getMediaMedia();
                                if($media->getProviderName()){
                                    $provider = $this->get($media->getProviderName());
                                    $provider->removeThumbnails($media);
                                }
                                $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                                $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                                $em->flush();
                            }
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);

                        }else{
                            $document = new Document();
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                            $person->setDocumentDocument($document);
                        }
                        break;
                    case "RUT":
                        if($person->getRutDocument()){
                            $document = $person->getRutDocument();
                            $corrected = true;
                            if($document->getMediaMedia()){
                                /** @var Media $media */
                                $media = $document->getMediaMedia();
                                if($media->getProviderName()){
                                    $provider = $this->get($media->getProviderName());
                                    $provider->removeThumbnails($media);
                                }
                                $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                                $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                                $em->flush();
                            }
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                        }else{
                            $document = new Document();
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                            $person->setRutDocument($document);
                        }
                        break;
                    case "RCDN":
                        if($person->getBirthRegDocument()){
                            $document = $person->getBirthRegDocument();
                            $corrected = true;
                            if($document->getMediaMedia()){
                                /** @var Media $media */
                                $media = $document->getMediaMedia();
                                if($media->getProviderName()){
                                    $provider = $this->get($media->getProviderName());
                                    $provider->removeThumbnails($media);
                                }
                                $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                                $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                                $em->flush();
                            }
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                        }else{
                            $document = new Document();
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                            $person->setBirthRegDocument($document);
                        }
                        break;
                    case "TI":
                        if($person->getDocumentDocument()){
                            $document = $person->getDocumentDocument();
                            $corrected = true;
                            if($document->getMediaMedia()){
                                /** @var Media $media */
                                $media = $document->getMediaMedia();
                                if($media->getProviderName()){
                                    $provider = $this->get($media->getProviderName());
                                    $provider->removeThumbnails($media);
                                }
                                $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                                $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                                $em->flush();
                            }
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                        }else{
                            $document = new Document();
                            $document->setName($documentType->getName());
                            $document->setDocumentTypeDocumentType($documentType);
                            $document->setStatus(0);
                            $person->setDocumentDocument($document);
                        }
                        break;
                }
                if($corrected){
                    /** @var User $user */
                    $user= $this->getUser();
                    if($user->getPersonPerson()->getIdPerson()==$notification->getPersonPerson()->getIdPerson()){
                        $actions = $em->getRepository('RocketSellerTwoPickBundle:Action')->findBy(array(
                            'userUser'=>$user,
                            'personPerson'=>$person
                        ));
                        /** @var Action $action */
                        foreach ($actions as $action){
                            if($action->getStatus()=='Error'){
                                $action->setStatus('Corregido');
                                $em->persist($action);
                            }
                        }
                    }

                }
                $em->persist($person);
                break;
            case "Employer":
                $corrected = false;
                /** @var Employer $employer */
                $employer = $em->getRepository("RocketSellerTwoPickBundle:Employer")->find($entityId);
                $name = $employer->getPersonPerson()->getFullName();
                if($employer->getMandatoryDocument()){
                    $document= $employer->getMandatoryDocument();
                    $corrected = true;
                    if($document->getMediaMedia()){
                        /** @var Media $media */
                        $media = $document->getMediaMedia();
                        if($media->getProviderName()){
                            $provider = $this->get($media->getProviderName());
                            $provider->removeThumbnails($media);
                        }
                        $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                        $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                        $em->flush();
                    }
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                }else{
                    $document = new Document();
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                    $employer->setMandatoryDocument($document);
                }
                if($corrected){
                    /** @var User $user */
                    $user= $this->getUser();
                    if($user->getPersonPerson()->getIdPerson()==$notification->getPersonPerson()->getIdPerson()){
                        $actions = $em->getRepository('RocketSellerTwoPickBundle:Action')->findBy(array(
                            'userUser'=>$user,
                            'personPerson'=>$employer->getPersonPerson()
                        ));
                        /** @var Action $action */
                        foreach ($actions as $action){
                            if($action->getStatus()=='Error'){
                                $action->setStatus('Corregido');
                                $em->persist($action);
                            }
                        }
                    }

                }
                $em->persist($employer);
                break;
            case "EmployerHasEmployee":
                $corrected = false;
                /** @var EmployerHasEmployee $eHE */
                $eHE = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($entityId);
                $name = $eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                if($eHE->getAuthDocument()){
                    $document = $eHE->getAuthDocument();
                    $corrected = true;
                    if($document->getMediaMedia()){
                        /** @var Media $media */
                        $media = $document->getMediaMedia();
                        if($media->getProviderName()){
                            $provider = $this->get($media->getProviderName());
                            $provider->removeThumbnails($media);
                        }
                        $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                        $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                        $em->flush();
                    }
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                }else{
                    $document = new Document();
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                    $eHE->setAuthDocument($document);
                }
                $em->persist($eHE);
                break;
            case "Contract":
                $corrected = false;
                /** @var Contract $contract */
                $contract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($entityId);
                $name = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                if($contract->getDocumentDocument()){
                    $document = $contract->getDocumentDocument();
                    $corrected = true;
                    if($document->getMediaMedia()){
                        /** @var Media $media */
                        $media = $document->getMediaMedia();
                        if($media->getProviderName()){
                            $provider = $this->get($media->getProviderName());
                            $provider->removeThumbnails($media);
                        }
                        $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                        $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                        $em->flush();
                    }
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                    $contract->setDocumentDocument($document);
                    $contract->setBackStatus(1);
                }else{
                    $document = new Document();
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                    $contract->setDocumentDocument($document);
                    $contract->setBackStatus(1);
                }
                $em->persist($contract);
                break;
            case "Payroll":
                $corrected = false;
                /** @var Payroll $payroll */
                $payroll = $em->getRepository("RocketSellerTwoPickBundle:Payroll")->find($entityId);
                $name = "pago de ".$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                if($payroll->getPayslip()){
                    $document = $payroll->getPayslip();
                    $corrected = true;
                    if($document->getMediaMedia()){
                        /** @var Media $media */
                        $media = $document->getMediaMedia();
                        if($media->getProviderName()){
                            $provider = $this->get($media->getProviderName());
                            $provider->removeThumbnails($media);
                        }
                        $em->remove($em->getRepository('\Application\Sonata\MediaBundle\Entity\Media')->find($media->getId()));
                        $em->remove($em->getRepository('ApplicationSonataMediaBundle:Media')->find($media->getId()));
                        $em->flush();
                    }
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                }else{
                    $document = new Document();
                    $document->setName($documentType->getName());
                    $document->setDocumentTypeDocumentType($documentType);
                    $document->setStatus(0);
                    $payroll->setPayslip($document);
                }
                $em->persist($payroll);
                break;
        }
        return array('document'=>$document,'notification'=>$notification,'personName'=> $name,'documentType'=>$documentType);
    }

    /**
     * to be called by the api consumed by the mobile app
     */
     /**
      * to be called by the api consumed by the mobile app
      */
     public function verifyAndPersitDocumentAction($entityType, $entityId, $docCode, $idNotification, $fileName) {
       $data = $this->verifyDocument($entityType, $entityId, $docCode, $idNotification);

       /** @var Document $document */
       $document = $data['document'];
       /** @var Notification $notification */
       $notification = $data['notification'];
       /** @var string $personName */
       $personName = $data['personName'];
       /** @var DocumentType $documentType */
       $documentType = $data['documentType'];

       $absPath = getcwd();

       $file_path = "$absPath/uploads/tempDocumentPages/$fileName";
       $mediaManager = $this->container->get('sonata.media.manager.media');
       $media = $mediaManager->create();
       $media->setBinaryContent($file_path);
       $media->setProviderName('sonata.media.provider.file');
       $media->setName($document->getName());
       $media->setProviderStatus(Media::STATUS_OK);
       $media->setContext('person');
       $media->setDocumentDocument($document);

       $document->setMediaMedia($media);

       $this->persitDocument($document ,$notification);

       $view = View::create();
       $view->setStatusCode(200);

       return $view->setData(array());
     }

    public function addDocModalAction($id, $idDocumentType, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
        $documentType = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:DocumentType')
                ->find($idDocumentType);
        $document = new Document();
        $document->setPersonPerson($person);
        $document->setStatus(1);
        $document->setName('nombre');
        $document->setDocumentTypeDocumentType($documentType);

        $form = $this->createForm(new DocumentRegistration(), $document);

        $form->handleRequest($request);
        return $this->render(
                        'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig', array('form' => $form->createView()));
    }

    public function editDocumentAction($id, $idDocument, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $OldDocument = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Document')
                ->find($idDocument);
        $OldDocument->setStatus(0);
        $em = $this->getDoctrine()->getManager();

        $person = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
        $documentType = $OldDocument->getDocumentTypeDocumentType();
        $document = new Document();
        $document->setPersonPerson($person);
        $document->setStatus(1);
        $document->setDocumentTypeDocumentType($documentType);
        $form = $this->createForm(new DocumentRegistration(), $document);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $medias = $document->getMediaMedia();
            /** @var Media $media */
            foreach ($medias as $media) {
                $media->setBinaryContent($media);
                $media->setName($document->getName());
                $media->setProviderStatus(Media::STATUS_OK);
                $media->setProviderReference($media->getBinaryContent());
                $em->persist($media);
                $em->flush();
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();
            $em->persist($OldDocument);
            $em->flush();
            return $this->redirectToRoute('employees_documents');
        }
        return $this->render(
                        'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig', array('form' => $form->createView()));
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
    }

    public function downloadDocAction($id,$idDocument)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        /** @var Document $document */
        $document = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Document')
            ->find($idDocument);
        if(!$document)
            throw $this->createNotFoundException();
        $backoffice=false;
        if($this->isGranted('ROLE_BACK_OFFICE', $this->getUser())){
            $backoffice=true;
        }else{
            /** @var Person $person */
            $person = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
            /** @var User $user */
            $user = $this->getUser();
            /** @var Person $uPerson */
            $uPerson = $user->getPersonPerson();
            $auth = false;
            $eHEExist=false;
            $eHERep=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
            if(!$person)
                throw $this->createNotFoundException();
            if($person != $uPerson){
                /** @var Employer $employer */
                $employer = $uPerson->getEmployer();
                if($employer== null)
                    throw $this->createNotFoundException();
                /** @var Employee $employee */
                $employee = $person->getEmployee();
                if($employee== null)
                    throw $this->createNotFoundException();
                /** @var EmployerHasEmployee $eHE */
                $eHE = $eHERep->findOneBy(array('employerEmployer'=>$employer,'employeeEmployee'=>$employee));
                if($eHE==null)
                    throw $this->createNotFoundException();
                $eHEExist = true;
            }
            $docCode=$document->getDocumentTypeDocumentType()->getDocCode();
            if($person == $uPerson and ($docCode == 'CC' or $docCode =='RUT')){
                $auth=true;
            }elseif($eHEExist){
                $auth=true;
            }
            if(!$auth)
                throw $this->createAccessDeniedException();
            switch ($document->getDocumentTypeDocumentType()->getDocCode()){
                case 'CC':
                    if($person->getDocumentDocument()!=$document)
                        throw $this->createAccessDeniedException();
                    break;
                case 'RUT':
                    if($person->getRutDocument()!=$document)
                        throw $this->createAccessDeniedException();
                    break;
                case 'MAND':
                    if($employer->getMandatoryDocument()!=$document)
                        throw $this->createAccessDeniedException();
                    break;
                case 'CTR':
                    /** @var Contract $contract */
                    foreach ($eHE->getContracts() as $contract){
                        if($contract->getState()==1){
                            /** @var Contract $activeContract */
                            $activeContract = $contract;
                            break;
                        }
                    }
                    if(!$activeContract)
                        throw $this->createNotFoundException();
                    if( $activeContract->getDocumentDocument()!= $document)
                        throw $this->createAccessDeniedException();
                    break;
                case 'CAS':
                    if($eHE->getAuthDocument()!=$document)
                        throw $this->createAccessDeniedException();
                    break;
            }
        }
        if($backoffice){
            switch($document->getDocumentTypeDocumentType()->getDocCode()){
                case 'CC':
                    $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('documentDocument'=>$document));
                    break;
                case 'RUT':
                    $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('rutDocument'=>$document));
                    break;
                case 'MAND':
                    /** @var Employer $employer */
                    $employer = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer")->findOneBy(array('mandatoryDocument'=>$document));
                    $person = $employer->getPersonPerson();
                    break;
                case 'CTR':
                    /** @var Contract $contract */
                    $contract = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('documentDocument'=>$document));
                    $person = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                    break;
                case 'CAS':
                    $eHE = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array('authDocument'=>$document));
                    $person = $eHE->getEmployeeEmployee()->getPersonPerson();
                    break;
            }
            if(!$person)
                throw $this->createNotFoundException();
        }
        $media = $document->getMediaMedia();
        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
            $docUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
        }
        $docName = $document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
        # create new zip opbject
        $zip = new ZipArchive();
        # create a temp file & open it
        $tmp_file =$person->getNames()."_".$document->getDocumentTypeDocumentType()->getName().".zip";
        if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
            # loop through each file
            $zip->addFile($docUrl,$docName);
            # close zip
            if($zip->close()!==TRUE)
                echo "no permisos";
            # send the file to the browser as a download
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-type: application/zip');
            header("Content-disposition: attachment; filename=$tmp_file");
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.filesize($tmp_file));
            ob_clean();
            ob_end_flush();
            readfile($tmp_file);
            ignore_user_abort(true);
            unlink($tmp_file);
        }
        return $this->redirectToRoute('ajax', array(), 301);

    }

    public function downloadDocumentPDFAction($document)
    {

        switch ($document):
            case "renuncia":
                $filename = "carta-renuncia.pdf";
                break;
            case "aceptacion":
                $filename = "carta-aceptacion.pdf";
                break;
            default:
                $filename = "cartaAuth.pdf";
                break;
        endswitch;

        $path = $this->get('kernel')->getRootDir() . "/../web/public/docs/";
        $content = file_get_contents($path . $filename);

        $response = new Response();

        //set headers
        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
    }

    public function downloadDocumentsAction($ref, $id, $type, $attach)
    {
        switch ($ref) {
            case "contrato":
                $ref = "contracts/" . $ref;
                // id del contrato
                /** @var Contract $contract */
                $contract = $this->contractDetail($id);
                $employerHasEmployee = $contract->getEmployerHasEmployeeEmployerHasEmployee();
                $employee = $employerHasEmployee->getEmployeeEmployee();
                $employeePerson = $employee->getPersonPerson();
                $employer = $employerHasEmployee->getEmployerEmployer();
                $employerPerson = $employer->getPersonPerson();

								$replaceOldContracts = false;
								$stillOnTest = false;

								/** @var Configuration $singleConfig */
		            foreach ($employee->getPersonPerson()->getConfigurations() as $singleConfig){
										if($singleConfig->getValue() == "PreLegal-SocialSecurityPayment"){
											$replaceOldContracts = true;
											break;
										}
		            }

		            /** @var DateTime $today */
		            $today = new DateTime();
								if($today <= $contract->getTestPeriod()){
									$stillOnTest = true;
								}

                $employeeInfo = array(
                    'name' => $this->fullName($employeePerson->getIdPerson()),
                    'docType' => $employeePerson->getDocumentType(),
                    'docNumber' => $employeePerson->getDocument(),
                    'residencia' => $employeePerson->getMainAddress(),
                    'tel' => $employeePerson->getPhones()[0],
	                  'replaceOldContracts' => $replaceOldContracts,
	                  'stillOnTest' => $stillOnTest
                );

                $employerInfo = array(
                    'name' => $this->fullName($employerPerson->getIdPerson()),
                    'docType' => $employerPerson->getDocumentType(),
                    'docNumber' => $employerPerson->getDocument(),
                    'tel' => $employerPerson->getPhones()->getValues()[0],
                    'email' => $employerPerson->getEmail()
                );

                $position = $contract->getPositionPosition()->getName();
//     	        $positionCode = $contract->getPositionPosition()->getPayrollCoverageCode();
                $identBy = $contract->getPositionPosition()->getIdentBy();

                $interno = $contract->getTransportAid();

                if ($interno) {
                    if ($identBy == "m") { // Si el cargo es mayordomo
                        $ref .= "-m";
                    }
                    $ref .= "-interno";
                }

                $timeCommitmentCode = $contract->getTimeCommitmentTimeCommitment()->getCode();
                if ($timeCommitmentCode == "XD") { // Si el contrato es por dias
                    $ref .= "-xdias";
                }

                $contractType = $contract->getContractTypeContractType()->getName();

                $startDate = $contract->getStartDate();
                $endDate = $contract->getEndDate();

                //contrato termino fijo contractType payroll_code 2, indefinido payroll_code 1
                $payrollCode = $contract->getContractTypeContractType()->getPayrollCode();
                switch ($payrollCode) {
                    default:
                    case 1:
                        $years = $months = $days = $years_months = null;
                        $ref .= '-indefinido';
                        break;
                    case 2:
                        $diff = $endDate->diff($startDate);
                        $years = $diff->format("%y");
                        $months = $diff->format("%m");
                        $days = $diff->format("%d");
                        $years_months = ($diff->format("%y") * 12) + $diff->format("%m");
                        $ref .= '-fijo';
                        break;
                }

                $contractInfo = array(
                    "endDate" => $endDate,
                    "startDate" => $startDate,
                    "position" => $position,
                    "salary" => $contract->getSalary(),
                    "frequency" => $contract->getFrequencyFrequency()->getName(),
                    "timeCommitment" => $contract->getTimeCommitmentTimeCommitment()->getName(),
                    "interno" => $interno,
                    "contractType" => $contractType,
                    "workplace" => $contract->getWorkplaceWorkplace()->getMainAddress()." ".$contract->getWorkplaceWorkplace()->getCity()->getName(),
                    "numero" => $contract->getIdContract(),
                    "years" => $years,
                    "months" => $months,
                    "years_months" => $years_months,
                    "days" => $days,
                    "workDays" => $contract->getWeekWorkableDays()->getValues(),
                    "nWorkDays" => $contract->getWorkableDaysMonth(),
                    "obligations" => $contract->getPositionPosition()->getObligations(),
                    "identBy" => $identBy
                );

                $data = array(
                    'employee' => $employeeInfo,
                    'employer' => $employerInfo,
                    'contract' => $contractInfo
                );
                // dump($data);
                // exit();
                break;
            case "otrosi":
            case "cert-laboral-activo":
            case "cert-laboral-retiro":
            case "retiro-cesantias":
            case "suspencion":
            case "llamado-atencion":
            case "aut-afiliacion-ss":
            case "trato-datos":
                //$id de la relacion employerhasempployee
                /** @var Employee $employee */
                $employee = $this->getEmployee($id);
                /** @var Employer $employer */
                $employer = $this->getEmployer($id);
                /** @var Contract $contract */
                $contract = $this->getActiveContract($id);

                $employeePerson = $employee->getPersonPerson();
                $employeeInfo = array(
                    'name' => $this->fullName($employeePerson->getIdPerson()),
                    'docType' => $employeePerson->getDocumentType(),
                    'docNumber' => $employeePerson->getDocument(),
                    'docExpPlace' => $employeePerson->getDocumentExpeditionPlace(),
                    'eps' => $this->getEmployeeEps($employee->getIdEmployee()),
                    'afp' => $this->getEmployeeAfp($employee->getIdEmployee()),
                    'fces' => $this->getEmployeeFces($employee->getIdEmployee())
                );

                $employerPerson = $employer->getPersonPerson();
                $employerInfo = array(
                    'name' => $this->fullName($employerPerson->getIdPerson()),
                    'docType' => $employerPerson->getDocumentType(),
                    'docNumber' => $employerPerson->getDocument(),
                    'docExpPlace' => $employerPerson->getDocumentExpeditionPlace(),
                    'arl' => $this->getEmployerArl($employer->getIdEmployer()),
                    'ccf' => $this->getEmployerCcf($employer->getIdEmployer()),
                    'email' => $this->getUser()->getEmail(),
                    'tel' => $employerPerson->getPhones()[0]->getPhoneNumber()
                );
                $contractInfo = array(
                    'city' => $contract[0]->getWorkplaceWorkplace()->getCity()->getName(),
                    'position' => $contract[0]->getPositionPosition()->getName(),
                    'fechaInicio' => $contract[0]->getStartDate(),
                    'fechaFin' => $contract[0]->getEndDate(),
                    'numero' => $contract[0]->getIdContract(),
                    'type' => $contract[0]->getContractTypeContractType()->getName(),
                    'salary' => $contract[0]->getSalary()
                );

                $data = array(
                    'employee' => $employeeInfo,
                    'employer' => $employerInfo,
                    'contract' => $contractInfo
                );
                break;
            case "not-despido":
            case "permiso":
            case "dotacion":
            case "aut-descuento":
            case "descargo":
            case "vacaciones":
                //$id de la novedad
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Novelty');
                /** @var Novelty $novelty */
                $novelty = $repository->find($id);
                $contract = $novelty->getPayrollPayroll()->getContractContract();
                $empleHasEmplr = $contract->getEmployerHasEmployeeEmployerHasEmployee();
                $employee = $empleHasEmplr->getEmployeeEmployee();
                $employeePerson = $employee->getPersonPerson();
                $employer = $empleHasEmplr->getEmployerEmployer();
                $employerPerson = $employer->getPersonPerson();

                $employeeInfo = array(
                    'name' => $this->fullName($employeePerson->getIdPerson()),
                    'docType' => $employeePerson->getDocumentType(),
                    'docNumber' => $employeePerson->getDocument(),
                    'docExpPlace' => $employeePerson->getDocumentExpeditionPlace()
                );
                $employerInfo = array(
                    'name' => $this->fullName($employerPerson->getIdPerson()),
                    'docType' => $employerPerson->getDocumentType(),
                    'docNumber' => $employerPerson->getDocument(),
                    'docExpPlace' => $employerPerson->getDocumentExpeditionPlace()
                );
                $contractInfo = array(
                    'city' => $contract->getWorkplaceWorkplace()->getCity()->getName(),
                    'position' => $contract->getPositionPosition()->getName(),
                    'fechaInicio' => $contract->getStartDate(),
                    'fechaFin' => $contract->getEndDate()
                );
                $noveltyInfo = array(
                    'id' => $novelty->getIdNovelty(),
                    'units' => $novelty->getUnits(),
                    'dateStart' => $novelty->getDateStart(),
                    'dateEnd' => $novelty->getDateEnd(),
                    'amount' => money_format("$%!i", $novelty->getAmount()),
                    'motivo' => $novelty->getDescription()
                );

                $data = array(
                    'employee' => $employeeInfo,
                    'employer' => $employerInfo,
                    'contract' => $contractInfo,
                    'novelty' => $noveltyInfo
                );
                break;
            case "mandato":
                //$id del empleador
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Person');
                $repositoryE = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer');
                $repositoryU = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User');
                /** @var \RocketSeller\TwoPickBundle\Entity\Employer $employer */
                $employerPerson = $repository->find($id);
                $employer = $repositoryE->findByPersonPerson($employerPerson);
                $user = $repositoryU->findByPersonPerson($employerPerson);
                $user = $user[0]->getId();

                $employerInfo = array(
                    'name' => $this->fullName($employerPerson->getIdPerson()),
                    'docType' => $employerPerson->getDocumentType(),
                    'docNumber' => $employerPerson->getDocument(),
                    'docExpPlace' => $employerPerson->getDocumentExpeditionPlace(),
                    'arl' => $this->getEmployerArl($employer[0]->getIdEmployer()),
                    'ccf' => $this->getEmployerCcf($employer[0]->getIdEmployer()),
                    'tel' => $employerPerson->getPhones()->getValues()[0],
                    'address' => $employerPerson->getMainAddress(),
                    'city' => $employerPerson->getCity()->getName()
                );

                $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user), array('_format' => 'json'));
                $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

                $data = array(
                    'employer' => $employerInfo,
                    'accountInfo' => $responsePaymentsMethods,
                    'rootDir' => $this->get('kernel')->getRootDir().'/..'
                );
                break;
            case "factura":
                //id de la orden de compra
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrders');
                /** @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrders */
                $purchaseOrders = $repository->find($id);



                $client = $purchaseOrders->getIdUser()->getPersonPerson();
                $clientInfo = array(
                    'name' => $this->fullName($client->getIdPerson()),
                    'docType' => $client->getDocumentType(),
                    'docNumber' => $client->getDocument(),
                    'address' => $client->getMainAddress(),
                    'phone' => $client->getPhones()->getValues(),
                    'city' => $client->getCity()->getName()
                );

                $descriptions = $purchaseOrders->getPurchaseOrderDescriptions();
                $items=array();
                $ivaTotal=0;
                $productsPrice = 0;
                /** @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $desc */
                foreach ($descriptions as $desc) {
                    if(!($desc->getProductProduct()->getSimpleName()=="PN"||$desc->getProductProduct()->getSimpleName()=="PP")){

                        $unitValue = 0;
                        if( $desc->getProductProduct()->getSimpleName()=="CT" ){
                          $ivaTotal+= round($desc->getValue()-($desc->getValue() / 1.16),0);
                          $unitValue = round(($desc->getValue() / 1.16),0);
                          $productsPrice += round(($desc->getValue() / 1.16));
                        }
                        else {
                          $ivaTotal+=$desc->getValue()-$desc->getProductProduct()->getPrice();
                          $unitValue = $desc->getProductProduct()->getPrice();
                          $productsPrice += $desc->getProductProduct()->getPrice();
                        }

                        $items[] = array(
                            'desc' => $desc->getDescription(),
                            'product' => $desc->getProductProduct(),
                            'pays' => $desc->getPayPay(),
                            'status' => $desc->getPurchaseOrdersStatus(),
                            'totalValue' => $desc->getValue(),
                            'unitValue' => $unitValue
                        );

                    }

                }

                $purchaseInfo = array(
                    'number' => $purchaseOrders->getIdPurchaseOrders(),
                    'city' => 'bogotá',
                    'endDate' => null,
                    'center' => null,
                    'total' => $ivaTotal+$productsPrice
                );

                $purchaseInfo['iva'] = $ivaTotal;
                $purchaseInfo['subTotal'] = $productsPrice;

                $data = array(
                    'invoiceNumber' => $purchaseOrders->getInvoiceNumber(),
                    'client' => $clientInfo,
                    'purchaseOrder' => $purchaseInfo,
                    'items' => $items
                );
                break;
            case "comprobante":
                $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Payroll');
                $isMobile = false;
                if(strpos($id, ",")) {
                   $arr = explode(',', $id);
                   $id = $arr[0];
                   $isMobile = true;
                }
                $payroll = $repository->find($id);
                if($payroll->getPaid() == 0){
                    return $this->redirectToRoute("show_dashboard");
                }
                $signatureUrl = null;

                $document = $payroll->getSignature();
                // signatre is already stored in db
                if($document != null) {

                    $fileUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                    $data = file_get_contents($fileUrl);
                    $signatureUrl = 'data:image/png;base64,' . base64_encode($data);
                }
                /** @var Person $employer */
                $employer = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                $employeePerson=$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                /** @var Contract $contract */
                $contract=$payroll->getContractContract();
                $pods=$payroll->getPurchaseOrdersDescription();
                $sqlNovelties=$payroll->getSqlNovelties();
                if($pods->count()>0){
                    /** @var PurchaseOrdersDescription $pod */
                    $pod=$pods->get(0);
                    $purchaseOrder=$pod->getPurchaseOrders();
                    if($purchaseOrder->getPayMethodId()==null)
                        $payMM="Efectivo";
                    else
                        $payMM=$contract->getPayMethodPayMethod()->getPayTypePayType()->getName();
                }
                else
                    $payMM=$contract->getPayMethodPayMethod()->getPayTypePayType()->getName();

                $clientInfo = array(
                    'name' => $this->fullName($employer->getIdPerson()),
                    'docType' => $employer->getDocumentType(),
                    'docNumber' => $employer->getDocument(),
                );
                $employeeInfo = array(
                    'name' => $this->fullName($employeePerson->getIdPerson()),
                    'docType' => $employeePerson->getDocumentType(),
                    'docNumber' => $employeePerson->getDocument(),
                    'position' => $contract->getPositionPosition()->getName(),
                    'payMethod' => $payMM,
                    'salary' => $contract->getTimeCommitmentTimeCommitment()->getCode()=="XD"?$contract->getSalary()/$contract->getWorkableDaysMonth():$contract->getSalary(),
                    'workCity' => $contract->getWorkplaceWorkplace()->getCity()->getName(),
                    'type' => $contract->getTimeCommitmentTimeCommitment()->getCode()=="XD"?"Diario":"Mensual",
                    'period'=>$payroll->getPeriod(),
                    'month'=>$payroll->getMonth(),
                    'year'=>$payroll->getYear()
                );
                $devengado= array();
                $deducido= array();
                $totalDevengado=$totalDeducido=$total=0;
                /** @var Novelty $sqlNovelty */
                foreach ($sqlNovelties as $sqlNovelty) {
                    if($sqlNovelty->getNoveltyTypeNoveltyType()->getNaturaleza()=="DEV"){
                        $devengado[]=$sqlNovelty;
                        $totalDevengado+=$sqlNovelty->getSqlValue();
                    }else{
                        $deducido[]=$sqlNovelty;
                        $totalDeducido+=$sqlNovelty->getSqlValue();

                    }
                }
                $discriminatedInfo= array(
                    'devengado'=>$devengado,
                    'deducido'=>$deducido,
                    'totalDevengado'=>$totalDevengado,
                    'totalDeducido'=>$totalDeducido,
                    'total'=>$totalDevengado-$totalDeducido
                );

                $data = array(
                    'employeeInfo' => $employeeInfo,
                    'client' => $clientInfo,
                    'discriminatedInfo' => $discriminatedInfo,
                    'signatureUrl' => $signatureUrl,
                    'isMobile' => $isMobile
                );
                break;
            case "joiner":
                $data = array();
                $arrHashNames = explode(",", $id);
                foreach ($arrHashNames as $hashName) {
                    if($hashName == "") continue;
                    $hashName = str_replace('_', '/', $hashName);
                    array_push($data, $hashName);
                }
                break;
            default:
                break;
        };

        $template = 'RocketSellerTwoPickBundle:Document:' . $ref . '.html.twig';

        switch ($type) {
            case "html":
                return $this->render($template, array(
                            'data' => $data
                ));
                break;
            default:
            case "pdf":
                $html = $this->renderView($template, array(
                    'data' => $data
                ));

                if ($attach) {
                    if (isset($data["invoiceNumber"]) && $data["invoiceNumber"] != null) {
                        $data["invoiceNumber"] = 123;
                        $docName = $data['client']['docNumber'] . "-" . $data["invoiceNumber"] . ".pdf";
                    } else {
                        $docName = $data['client']['docNumber'] . "-" . $data['purchaseOrder']['number'] . ".pdf";
                    }
                    $ds = DIRECTORY_SEPARATOR;
                    $path = $this->get('kernel')->getRootDir() . $ds . ".." . $ds . "web" . $ds . "public" . $ds . "docs" . $ds . "tmp" . $ds . "invoices" . $ds . $docName;

                    if (!file_exists($path)) {

                        $this->get('knp_snappy.pdf')->generateFromHtml(
                                $this->renderView(
                                        $template, array(
                                    'data' => $data
                                        )
                                ), $path
                        );
                    }

                    return new JsonResponse(array("name-path" => $docName));
                }

                //return new Response($html);
                return new Response(
                        $this->get('knp_snappy.pdf')->getOutputFromHtml($html), 200, array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $ref . '.pdf"',
                        )
                );
                break;
        };
    }

}
