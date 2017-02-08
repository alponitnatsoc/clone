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
use RocketSeller\TwoPickBundle\Entity\Prima;
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
     * upload signature to a specific entity
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
     * @RequestParam(name="idEntity", nullable=false, strict=true, description="id associated entity")
     * @RequestParam(name="entityType", nullable=false, strict=true, description="entity type")
     *
     * @return View
     */
    public function postUploadSignatureAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $entityType = $paramFetcher->get('entityType');
        $idEntity = $paramFetcher->get('idEntity');

        $rawSignature = $paramFetcher->get('signature');
        $raw2 = $rawSignature;
        $img = str_replace('data:image/png;base64,', '', $rawSignature);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $fileSignatureName = "tempSignature.png";
        file_put_contents("uploads/$fileSignatureName", $data);

        $documentType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:DocumentType')
            ->findOneBy(array("name" => 'Firma'));

        switch($entityType) {
            case 'Payroll':
                $payroll = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Payroll')
                ->find($idEntity);
                $employerHasEmployee = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee();

                $employer = $employerHasEmployee->getEmployerEmployer();
                $personEmployee = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson();

                $document = new Document();
                $document->setPersonPerson($personEmployee);
                $document->setEmployerEmployer($employer);
                $document->setName('Firma');
                $document->setStatus(1);
                $document->setDocumentTypeDocumentType($documentType);
                $em->persist($document);

                $payroll->setSignature($document);
                $em->persist($payroll);
                break;
            case 'Prima':
                /** @var Prima $prima */
                $prima = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:Prima')
                    ->find($idEntity);
                $employerHasEmployee = $prima->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee();
                $employer = $employerHasEmployee->getEmployerEmployer();
                $personEmployee = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson();

                $document = new Document();
                $document->setPersonPerson($personEmployee);
                $document->setEmployerEmployer($employer);
                $document->setName('Firma');
                $document->setStatus(1);
                $document->setDocumentTypeDocumentType($documentType);
                $em->persist($document);

                $prima->setSignature($document);
                $em->persist($prima);
                break;
	        case 'Employer':
		        /** @var Employer $employer */
		        $employer = $this->getDoctrine()
		          ->getRepository('RocketSellerTwoPickBundle:Employer')
		          ->find($idEntity);
		        $personEmployee = $employer->getPersonPerson();
		
		        $document = new Document();
		        $document->setPersonPerson($personEmployee);
		        $document->setEmployerEmployer($employer);
		        $document->setName('Firma');
		        $document->setStatus(1);
		        $document->setDocumentTypeDocumentType($documentType);
		        $em->persist($document);
		
		        $employer->setSignature($document);
		        $em->persist($employer);
		        break;
        }
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $media = $mediaManager->create();
        $media->setBinaryContent("uploads/$fileSignatureName");
        $media->setProviderName('sonata.media.provider.file');
        $media->setName($document->getName());
        $media->setProviderStatus(Media::STATUS_OK);
        $media->setContext('person');
        $media->setDocumentDocument($document);

        $em->persist($media);
        $em->flush();
        unlink("uploads/$fileSignatureName");

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
        $response = $response . " Corrigiendo tabla de documentos en la base de datos<br><br>";
        $em = $this->getDoctrine()->getManager();
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        $response = $response . " Recorriendo los documentos...<br><br>";
        /** @var Document $document */
        foreach ($documents as $document) {
            /** @var Person $person */
            $person = $document->getPersonPerson();
            if($person){
                /** @var Employer $employer */
                $employer = $person->getEmployer();
                /** @var Employee $employee */
                $employee = $person->getEmployee();
                if($employer and $employee){
                    if($document->getEmployerEmployer()== $employer){
                        $document->setEmployerEmployer(NULL);
                        $em->persist($document);
                    }
                    if($document->getDocumentTypeDocumentType()->getName()=="Carta autorización Symplifica"){
                        $response = $response . "Entro.<br>";
                        if($employer->getEmployerHasEmployees()->count()==1){
                            $response = $response . "Entro2. <br>";
                            /** @var EmployerHasEmployee $eHE */
                            $eHE = $employer->getEmployerHasEmployees()->first();
                            $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                            $document->setEmployerEmployer($employer);
                            $em->persist($document);
                        }
                    }
                }elseif ($employer and !$employee){
                    if($document->getDocumentTypeDocumentType()->getName()=="Carta autorización Symplifica"){
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($employer->getEmployerHasEmployees() as $eHE){
                            $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                            $document->setEmployerEmployer($employer);
                            $em->persist($document);
                        }
                    }
                    if($document->getDocumentTypeDocumentType()->getName()=="Contrato"){
                        if($employer->getEmployerHasEmployees()->count()==1){
                            /** @var EmployerHasEmployee $eHE */
                            $eHE = $employer->getEmployerHasEmployees()->first();
                            $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                            $document->setEmployerEmployer($employer);
                            $em->persist($document);
                        }
                    }
                    if($document->getEmployerEmployer()!=NULL){
                        $document->setEmployerEmployer(NULL);
                        $em->persist($document);
                    }
                }elseif ($employee and !$employer){
                    if($employee->getEmployeeHasEmployers()->count()==1){
                        /** @var EmployerHasEmployee $eHE */
                        $eHE = $employee->getEmployeeHasEmployers()->first();
                        $document->setEmployerEmployer($eHE->getEmployerEmployer());
                        $em->persist($document);
                    }
                }else{
                    $response = $response . " ERROR.<br/><br/>";
                }
            }

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
                        /** @var EmployerHasEmployee$eHE */
                        $eHE=$document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->first();
                        $contracts = $eHE->getContracts();
                        /** @var Contract $contract */
                        foreach ($contracts as $contract){
                            if($contract->getState()==1){
                                $response = $response . " Asignando el documento del contrato...<br><br>";
                                $contract->setDocumentDocument($document);
                                $document->setPersonPerson(null);
                                $document->setEmployerEmployer(null);
                                $em->persist($contract);
                                $em->persist($document);
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

    /**
     * Correct All documents
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Correction to the data base Documents",
     *   statusCodes = {
     *     200 = "Correction executed correctly",
     *     400 = "Error in correction",
     *   }
     * )
     * @return View
     */
    public function postCorrectDataBaseDocumentAction()
    {
        $response ="";
        $response = $response . " CORRIGIENDO BASE DE DATOS DE DOCUMENTOS...<br><br>";
        //Obtaining the doctrine manager
        $em = $this->getDoctrine()->getManager();
        //geting the novelties from the database;
        $novelties = $em->getRepository("RocketSellerTwoPickBundle:Novelty")->findAll();
        //Crossing the novelties to unlink the person from the novelty documents
        /** @var Novelty $novelty */
        foreach ($novelties as $novelty){
            //getting the novelty docs
            $novDocs = $novelty->getDocuments();
            //if array of docs size is greater or equal to 1
            if($novDocs->count()>=1){
                //crossing the novelty documents
                /** @var Document $novDoc */
                foreach ($novDocs as $novDoc){
                    //unlinking persons and employers from novelty docs
                    $novDoc->setPersonPerson(null);
                    $novDoc->setEmployerEmployer(null);
                    $novDoc->setName($novDoc->getDocumentTypeDocumentType()->getName());
                    $em->persist($novDoc);
                }
            }
        }

        //getting the documents from the data base
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        //Crossing all the documents the first time to correct the relations
        /** @var Document $document */
        foreach ($documents as $document) {
            $document->setName($document->getDocumentTypeDocumentType()->getName());
            //getting the person from the document
            /** @var Person $person */
            $person = $document->getPersonPerson();
            //if person is not null
            if($person){
                //getting the employer and the employee if exist
                /** @var Employer $employer */
                $employer = $person->getEmployer();
                /** @var Employee $employee */
                $employee = $person->getEmployee();
                //if the person is both employer and employee
                if($employer and $employee){
                    //if document type is CAS finding the first employee to assign the document to it
                    if($document->getDocumentTypeDocumentType()->getName()=="Carta autorización Symplifica" and $document->getEmployerEmployer() == $employer){
                        /** @var EmployerHasEmployee $eHE */
                        $eHE = $employer->getEmployerHasEmployees()->first();
                        //if employerHasEmployee exists linking the document to the antique employerHasEmployee relation
                        if($eHE){
                            $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                            $document->setEmployerEmployer($employer);
                            $em->persist($document);
                        }
                    }
                    //unlinking employer from the doc
                    if($document->getEmployerEmployer()== $employer){
                        $document->setEmployerEmployer(NULL);
                        $em->persist($document);
                    }

                //if person is only a employer
                }elseif ($employer and !$employee){
                    //if document type is CAS linking to the employerHasEmployee antique relation
                    if($document->getDocumentTypeDocumentType()->getName()=="Carta autorización Symplifica"){
                        /** @var EmployerHasEmployee $eHE */
                        $eHE = $employer->getEmployerHasEmployees()->first();
                        $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                        $document->setEmployerEmployer($employer);
                        $em->persist($document);
                    }
                    //if document tipe is CTR and employer only has one employee linking the contract to the employerHasEmployee antique relation
                    if($document->getDocumentTypeDocumentType()->getName()=="Contrato"){
                        if($employer->getEmployerHasEmployees()->count()==1){
                            /** @var EmployerHasEmployee $eHE */
                            $eHE = $employer->getEmployerHasEmployees()->first();
                            $document->setPersonPerson($eHE->getEmployeeEmployee()->getPersonPerson());
                            $document->setEmployerEmployer($employer);
                            $em->persist($document);
                        }
                    }
                    //if the document belows to a employer and has set a employer in the relation deleting the relation
                    if($document->getEmployerEmployer()!=NULL){
                        $document->setEmployerEmployer(NULL);
                        $em->persist($document);
                    }
                //if person is only a employee
                }elseif ($employee and !$employer){
                    //if employee has only one employer
                    if($employee->getEmployeeHasEmployers()->count()==1){
                        /** @var EmployerHasEmployee $eHE */
                        $eHE = $employee->getEmployeeHasEmployers()->first();
                        //setting the document employer to the employer existent in the employerHasEmployee
                        $document->setEmployerEmployer($eHE->getEmployerEmployer());
                        $em->persist($document);
                    }
                //if person is neither employer or employee
                }else{
                    //print error
                    $response = $response . " ERROR ID PERSONA: " . $person. ".<br/><br/>";
                }
            }

            $em->flush();
        }

        $response = $response . " CORRIGIENDO CONTRATOS...".'<br><br>';
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        //Crossing all documents
        /** @var Document $document */
        foreach ($documents as $document) {
            //if document type is CTR
            if($document->getDocumentTypeDocumentType()->getName()=='Contrato'){
                //getting the employee
                if($document->getPersonPerson()->getEmployee()){
                    if($document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->count()==1){
                        /** @var EmployerHasEmployee$eHE */
                        $eHE=$document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers()->first();
                        //getting the contracts
                        $contracts = $eHE->getContracts();
                        $response = $response . " ASIGNANDO DOCUMENTOS DE CONTRATOS...<br>";
                        /** @var Contract $contract */
                        //finding the active contract
                        foreach ($contracts as $contract){
                            if($contract->getState()==1){
                                $contract->setDocumentDocument($document);
                                $document->setPersonPerson(null);
                                $document->setEmployerEmployer(null);
                                $em->persist($contract);
                                $em->persist($document);
                            }
                        }
                    }
                }
            }
            $em->flush();
        }
        //getting all the documents
        $documents = $em->getRepository("RocketSellerTwoPickBundle:Document")->findAll();
        $response = $response . "<BR> CORRIGIENDO DOCUMENTOS..." . "<br>";
        //macking corrections to all the documents to avoid wrong types
        /** @var Document $document */
        foreach ($documents as $document){
            if($document->getDocumentTypeDocumentType()->getName()=="Cedula"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(1));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Rut"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(2));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Contrato"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(3));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Carta autorización Symplifica"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(4));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="REGISTRO CIVIL DE NACIMIENTO COTIZANTE"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(5));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="REGISTRO CIVIL DE NACIMIENTO HIJO (BENEFICIARIO)"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(5));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="LICENCIA DE MATERNIDAD EN ORGINAL"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(6));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="LICENCIA DE PATERNIDAD EN ORIGINAL"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(7));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="HISTORIA CLINICA DEL EVENTO"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(8));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="DOCUMENTOS SOPORTES DE LA NOVEDAD"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(9));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="FORMATOS SOPORTES DE LA NOVEDAD"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(10));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="INCAPACIDAD"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(11));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="REPORTE DE ACCIDENTE DE TRABAJO"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(12));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="CERTIFICADO DEFUNCION DE (PADRES, COMPAÑERA, HIJOS, HERMANOS)"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(13));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Carta de renuncia"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(14));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Solicitud de vacaciones"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(15));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Suspencion"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(16));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Version libre de hechos"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(17));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Autorización de descuento"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(18));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Soporte entrega de dotación"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(19));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Licencia no remunerada"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(20));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Notificación de despido"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(21));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Mandato"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(22));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="OtroSi"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(23));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Comprobante"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(24));
            }
            if($document->getDocumentTypeDocumentType()->getName()=="Firma"){
                $document->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(25));
            }
            $em->persist($document);
        }
        $response = $response . "<br> DOCUMENTOS CORREGIDOS.<br><br>";
        $response = $response . "<br> CREANDO CODIGOS DE DOCUMENTYPE...<br>";
        //creating all the docType codes
        /** @var DocumentType $docType */
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(1);
        $docType->setName('Cédula');
        $docType->setDocCode('CC');
        $em->persist($docType);
        $response = $response . " CC CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(2);
        $docType->setName('Rut');
        $docType->setDocCode('RUT');
        $em->persist($docType);
        $response = $response . " RUT CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(3);
        $docType->setName('Contrato');
        $docType->setDocCode('CTR');
        $em->persist($docType);
        $response = $response . " CTR CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(4);
        $docType->setName('Carta autorización Symplifica');
        $docType->setDocCode('CAS');
        $em->persist($docType);
        $response = $response . " CAS CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(5);
        $docType->setName('Registro civil de nacimiento');
        $docType->setDocCode('RCDN');
        $em->persist($docType);
        $response = $response . " RCDN CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(6);
        $docType->setName('Licencia de maternidad en original');
        $docType->setDocCode('LDM');
        $em->persist($docType);
        $response = $response . " LDM CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(7);
        $docType->setName('Licencia de paternidad en original');
        $docType->setDocCode('LDP');
        $em->persist($docType);
        $response = $response . " LDP CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(8);
        $docType->setName('Historia clinica del suceso');
        $docType->setDocCode('HCDE');
        $em->persist($docType);
        $response = $response . " HCDE CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(9);
        $docType->setName('DOCUMENTOS SOPORTES DE LA NOVEDAD');
        $docType->setDocCode('DSN');
        $em->persist($docType);
        $response = $response . " DSN CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(10);
        $docType->setName('FORMATOS SOPORTE DE LA NOVEDAD');
        $docType->setDocCode('FSN');
        $em->persist($docType);
        $response = $response . " FSN CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(11);
        $docType->setName('Soporte de Incapacidad General');
        $docType->setDocCode('INC');
        $em->persist($docType);
        $response = $response . " INC CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(12);
        $docType->setName('Reporte de accidente de trabajo');
        $docType->setDocCode('RADT');
        $em->persist($docType);
        $response = $response . " RADT CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(13);
        $docType->setName('CERTIFICADO DE DEFUNCIÓN');
        $docType->setDocCode('CERD');
        $em->persist($docType);
        $response = $response . " CERD CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(14);
        $docType->setName('Carta de renuncia');
        $docType->setDocCode('CDR');
        $em->persist($docType);
        $response = $response . " CDR CREADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(15);
        $docType->setName('Solicitud de vacaciones');
        $docType->setDocCode('SDV');
        $docType->setRefPdf("vacaciones");
        $em->persist($docType);
        $response = $response . " SDV CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(16);
        $docType->setName('Suspensión');
        $docType->setDocCode('SUSP');
        $docType->setRefPdf("suspencion");
        $response = $response . " SUSP CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(17);
        $docType->setName('Version libre de hechos');
        $docType->setDocCode('VLDH');
        $docType->setRefPdf("descargo");
        $response = $response . " VLDH CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(18);
        $docType->setName('Autorización de descuento');
        $docType->setDocCode('ADES');
        $docType->setRefPdf("aut-descuento");
        $response = $response . " ADES CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(19);
        $docType->setName('Soporte entrega de dotación');
        $docType->setDocCode('DOT');
        $docType->setRefPdf("dotacion");
        $response = $response . " DOT CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(20);
        $docType->setName('Licencia no remunerada');
        $docType->setDocCode('LNRE');
        $docType->setRefPdf("permiso");
        $response = $response . " LNRE CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(21);
        $docType->setName('Notificación de despido');
        $docType->setDocCode('NDSP');
        $docType->setRefPdf("not-despido");
        $response = $response . " NDSP CREADO<br>";
        $response = $response . " PDF ASOCIADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(22);
        $docType->setName('Mandato');
        $docType->setDocCode('MAND');
        $docType->setRefPdf(null);
        $response = $response . " MAND CREADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(23);
        $docType->setName('OtroSi');
        $docType->setDocCode('OTRS');
        $docType->setRefPdf(null);
        $response = $response . " OTRS CREADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(24);
        $docType->setName('Comprobante');
        $docType->setDocCode('CPR');
        $docType->setRefPdf(null);
        $response = $response . " CPR CREADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(25);
        $docType->setName('Firma');
        $docType->setDocCode('FIRM');
        $docType->setRefPdf(null);
        $response = $response . " FIRM CREADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(26);
        $docType->setName('Tarjeta de Identidad');
        $docType->setDocCode('TI');
        $docType->setRefPdf(null);
        $response = $response . " TI CREADO<br>";
        $em->persist($docType);
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->find(27);
        $docType->setName('Soporte de Incapacidad Profecional');
        $docType->setDocCode('INCP');
        $docType->setRefPdf(null);
        $response = $response . " INCP CREADO<br>";
        $em->persist($docType);
        $docType = new DocumentType();
        $docType->setName('Registro civil de hijo nacido vivo');
        $docType->setDocCode('RCNV');
        $docType->setRefPdf(null);
        $response = $response . " RCNV CREADO<br>";
        $em->persist($docType);
        $docType = new DocumentType();
        $docType->setName('Licencia remunerada');
        $docType->setDocCode('LREM');
        $docType->setRefPdf("formato-licencia");
        $response = $response . " LREM CREADO<br>";
        $em->persist($docType);
        $docType = new DocumentType();
        $docType->setName('Solicitud vacaciones en dinero');
        $docType->setDocCode('SDVD');
        $docType->setRefPdf("vacaciones-dinero");
        $response = $response . " SDVD CREADO<br>";
        $em->persist($docType);
        $em->flush();
        $response = $response . " CODIGOS CREADOS.<br><br>";
        //Linking unique documents to the person and the employer
        $response = $response . "<br> RELACIONANDO DOCUMENTOS DE IDENTIDAD A LA PERSONA...<br><br>";
        $persons = $em->getRepository("RocketSellerTwoPickBundle:Person")->findAll();
        /** @var Person $person */
        foreach ($persons as $person){
            $documents = $person->getDocs();
            $CC = false;
            $RUT = false;
            $TI = false;
            $MAND = false;
            foreach ($documents as $document){
                //if document type is CC and flag CC alredy exist printing duplicate message
                if($document->getDocumentTypeDocumentType()->getDocCode()=="CC" and $CC){
                    $response = $response . " DOCUMENTO DUPLICADO ID DOC: ".$document->getIdDocument()."...". "<br>";
                }
                //if document type is RUT and flag RUT alredy exist printing duplicate message
                if($document->getDocumentTypeDocumentType()->getDocCode()=="RUT" and $RUT){
                    $response = $response . " DOCUMENTO DUPLICADO ID DOC: ".$document->getIdDocument()."...". "<br>";
                }
                //if document type is TI and flag TI alredy exist printing duplicate message
                if($document->getDocumentTypeDocumentType()->getDocCode()=="TI" and $TI){
                    $response = $response . " DOCUMENTO DUPLICADO ID DOC: ".$document->getIdDocument()."...". "<br>";
                }
                //if document type is MAND and flag MAND alredy exist printing duplicate message
                if($document->getDocumentTypeDocumentType()->getDocCode()=="MAND" and $MAND){
                    $response = $response . " DOCUMENTO DUPLICADO ID DOC: ".$document->getIdDocument()."...". "<br>";
                }
                //if document type is CC linking the document directly to the person
                if($document->getDocumentTypeDocumentType()->getDocCode()=="CC" and !$CC){
                    $CC = true;
                    $response = $response . " PERSON ID:  ".$person->getIdPerson()." DOCUMENT ID: ".$document->getIdDocument()." DOC CODE:".$document->getDocumentTypeDocumentType()->getDocCode()."<br>";
                    $person->setDocumentDocument($document);
                    $document->setPersonPerson(null);
                    $document->setEmployerEmployer(null);
                }
                //if document type is RUT linking the document directly to the person
                if($document->getDocumentTypeDocumentType()->getDocCode()=="RUT" and !$RUT){
                    $RUT = true;
                    $response = $response . " PERSON ID:  ".$person->getIdPerson()." DOCUMENT ID: ".$document->getIdDocument()." DOC CODE:".$document->getDocumentTypeDocumentType()->getDocCode()."<br>";
                    $person->setRutDocument($document);
                    $document->setPersonPerson(null);
                    $document->setEmployerEmployer(null);
                }
                //if document type is TI linking the document directly to the person
                if($document->getDocumentTypeDocumentType()->getDocCode()=="TI" and !$TI){
                    $TI = true;
                    $response = $response . " PERSON ID:  ".$person->getIdPerson()." DOCUMENT ID: ".$document->getIdDocument()." DOC CODE:".$document->getDocumentTypeDocumentType()->getDocCode()."<br>";
                    $person->setDocumentDocument($document);
                    $document->setPersonPerson(null);
                    $document->setEmployerEmployer(null);
                }
                //if document type is RCDN linking the document directly to the person
                if($document->getDocumentTypeDocumentType()->getDocCode()=="RCDN"){
                    $response = $response . " PERSON ID:  ".$person->getIdPerson()." DOCUMENT ID: ".$document->getIdDocument()." DOC CODE:".$document->getDocumentTypeDocumentType()->getDocCode()."<br>";
                    $person->setBirthRegDocument($document);
                    $document->setPersonPerson(null);
                    $document->setEmployerEmployer(null);
                }
                //if document type is MAND finding the employer and linking the document directly to the employer
                if($document->getDocumentTypeDocumentType()->getDocCode()=="MAND" and !$MAND){
                    $employer = $person->getEmployer();
                    if($employer){
                        $MAND = true;
                        $response = $response . " PERSON ID:  ".$person->getIdPerson()." DOCUMENT ID: ".$document->getIdDocument()." DOC CODE:".$document->getDocumentTypeDocumentType()->getDocCode()."<br>";
                        $employer->setMandatoryDocument($document);
                        $document->setPersonPerson(null);
                        $document->setEmployerEmployer(null);
                    }
                }
                $em->persist($person);
                $em->persist($document);
            }
        }
        $response = $response . "<br> DOCUMENTOS DE LA PERSONA ASIGNADOS.<br><br>";
        $response = $response . "<br> ASIGNANDO CARTAS DE AUTORIZACION...<br><br>";
        //macking corrections to the authLetters
        $eHES = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findAll();
        //crossing all the employerHasEmployees
        /** @var EmployerHasEmployee $eHE */
        foreach ($eHES as $eHE){
            $documents = $eHE->getEmployeeEmployee()->getPersonPerson()->getDocs();
            $CAS = false;
            //crossing the employeeDocuments
            foreach ($documents as $document){
                //if flag has been changed printing the duplicated message
                if($document->getDocumentTypeDocumentType()->getDocCode()=="CAS" and $document->getEmployerEmployer()==$eHE->getEmployerEmployer() and $CAS){
                    $response = $response . " DOCUMENTO DUPLICADO ID DOC: ".$document->getIdDocument()."...". "<br>";
                }
                //if document type is CAS and employer exist setting the document directly to the employer
                if($document->getDocumentTypeDocumentType()->getDocCode()=="CAS" and $document->getEmployerEmployer()==$eHE->getEmployerEmployer()and !$CAS){
                    $CAS = true;
                    $response = $response ." EHE ID: ".$eHE->getIdEmployerHasEmployee(). " CODE: ".$document->getDocumentTypeDocumentType()->getDocCode()." EMPLOYEE: ".$eHE->getEmployeeEmployee()->getIdEmployee()." EMPLOYER: ".$eHE->getEmployerEmployer()->getIdEmployer()."<br>";
                    $eHE->setAuthDocument($document);
                    $document->setPersonPerson(null);
                    $document->setEmployerEmployer(null);
                    $em->persist($eHE);
                    $em->persist($document);
                }
            }
        }
        $response = $response . "<br> CARTAS DE AUTORIZACION ASIGNADAS<br><br>";
        $response = $response . " FINALIZADO.<br><br>";
        $em->flush();
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

}
