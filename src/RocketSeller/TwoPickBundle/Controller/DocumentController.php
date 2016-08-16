<?php

namespace RocketSeller\TwoPickBundle\Controller;

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
     * @param integer $id id of the person who owns the document being added, only if the doctype is Comprobante this function receives instead a payrollId so it can associate the document to the payroll
     * @param integer $idDocumentType id to match the document type with the table DocumentType
     * @param integer $idNotification id to change the status of the notification after the document has been addded
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addDocAction($id, $idDocumentType, $idNotification, Request $request)
    {
        // setting the document types alowed by the application
        $fileTypePermitted = array(
            'image/png',
            'image/jpeg',
            'application/pdf',
            //'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $em = $this->getDoctrine()->getManager();
        // getting the documentType from the database and chequing is a valid document type
        /** @var DocumentType $documentType */
        $documentType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:DocumentType')
            ->find($idDocumentType);
        /**
         * If the document type is Comprobante we obtain the person with the payroll employerHasEmployee relation
         */
        // getting the person that owns the document
        if($documentType->getName()=="Comprobante"){
            /** @var Payroll $payroll */
            $payroll = $em->getRepository("RocketSellerTwoPickBundle:Payroll")->find($id);
            /** @var Person $person */
            $person = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
        }else{
            /** @var Person $person */
            $person = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
        }
        // setting the person name for the modalform
        $name = $person->getNames();

        if ($idNotification != 0) {
            $em = $this->getDoctrine()->getManager();
            $notification = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Notification')
                ->find($idNotification);
        }else{
            return $this->redirectToRoute('matrix_choose', array('tab' => 3), 301);
        }

        //checking if the document alredy exist in the database
        if($documentType->getName()=='Contrato'){
            $bdDoc=$person->getDocByType($documentType->getName(),$notification->getPersonPerson()->getEmployer()->getIdEmployer());
        }else{
            $bdDoc=$person->getDocByType($documentType->getName());
        }
        $document = new Document();
        $document->setPersonPerson($person);
        $document->setStatus(1);
        $document->setName('Diferente');
        $document->setDocumentTypeDocumentType($documentType);
        if($person->getEmployee()){
            $document->setEmployerEmployer($notification->getPersonPerson()->getEmployer());
        }
        $form = $this->createForm(new DocumentRegistration(), $document);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (in_array($document->getMediaMedia()->getContentType(), $fileTypePermitted)) {

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

                    // if documentType is contract it sets the document contract to the active contract of the employerHasEmployee that matchs with the owner of the notification
                    if($documentType->getName() == 'Contrato'){
                        $eHEs= $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()->getIdPerson()==$notification->getPersonPerson()->getIdPerson()){
                                $contracts = $eHE->getContracts();
                                /** @var Contract $contract */
                                foreach ($contracts as $contract) {
                                    if($contract->getState()==1){
                                        $contract->setDocumentDocument($document);
                                        $em->persist($contract);
                                        $em->flush();
                                    }
                                }
                            }
                        }
                    }
                    $notification->setStatus(0);
                    $em->flush();
                    $request = $this->container->get('request');
                    $request->setMethod("GET");
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:EmployerRest:getEmployerDocumentsState', array('idUser' => $this->getUser()->getId()), array('_format' => 'json'));
                    $responsePaymentsMethods = json_decode($insertionAnswer->getContent(), true);
                    return $this->redirectToRoute('show_dashboard');
            } else {
                $this->addFlash('fail_format', 'NVF');
                return $this->redirectToRoute('show_dashboard');
            }
        }
        return $this->render(
            'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig', array('form' => $form->createView(), 'id' => $id, 'idDocumentType' => $idDocumentType, 'documentName'=>$documentType->getName(),'personName'=>$name, 'idNotification' => $idNotification));
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
        /** @var Person $user */
        $user = $this->getUser()->getPersonPerson();
        /** @var EmployerHasEmployee $eHE */
        $valid = false;
        foreach ($user->getEmployer()->getEmployerHasEmployees() as $eHE){
            if ($eHE->getEmployeeEmployee()->getPersonPerson()->getIdPerson()==$id){
                $valid=true;
            }
        }
        if($valid) {
            $person = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($id);
            $document = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Document')
                ->find($idDocument);

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
        }else{
            throw $this->createAccessDeniedException("No tiene suficientes permisos");
        }
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

                $employeeInfo = array(
                    'name' => $this->fullName($employeePerson->getIdPerson()),
                    'docType' => $employeePerson->getDocumentType(),
                    'docNumber' => $employeePerson->getDocument(),
                    'residencia' => $employeePerson->getMainAddress(),
                    'tel' => $employeePerson->getPhones()[0]
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
                    'email' => $employerPerson->getEmail(),
                    'tel' => $employerPerson->getPhones()[0]
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
                // document is already stored in db
                if($document != null && $signatureUrl == null) {

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
