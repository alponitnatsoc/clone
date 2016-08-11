<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use FOS\RestBundle\View\View;
use PHPExcel;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;


class ExportController extends Controller
{
    /**
     * Function to export all the documents related to a entity
     * @param String $entityType name of the entity
     * @param Integer $idEntity id of the entity row
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exportDocumentsAction($entityType, $idEntity){
        //checking user is authenticated
        if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            throw $this->createAccessDeniedException();
        }
        //flag for backoffice user
        $backOffice=false;
        //flag for permissions
        $auth=false;
        $count = 0;
        //if user is backoffice changing the backoffice flag
        if($this->isGranted('ROLE_BACK_OFFICE', $this->getUser())){
            $backOffice= true;
        }
        //getting the user
        /** @var User $user */
        $user = $this->getUser();
        //switching between enityTypes
        switch ($entityType){
            case 'Person':
                //getting the person from the idEntity parameter
                /** @var Person $person */
                $person = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Person")->find($idEntity);
                if(!$person)
                    throw $this->createNotFoundException();
                //if person is equal to the user person changing the permission flag to true
                if($person->getIdPerson() == $user->getPersonPerson()->getIdPerson()){
                    $auth=true;
                //if person is diferent for the user person looking for the person in the employees of the employer
                }elseif($person != $user->getPersonPerson() and $person->getEmployee()){
                    $eHES = $person->getEmployee()->getEmployeeHasEmployers();
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($eHES as $eHE){
                        //if the employee person belongs to the employerHasEmployee relation for the user changing the permission flag to true
                        if($eHE->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson()){
                            $auth = true;
                            break;
                        }

                    }
                }
                //if the user is not allowed to download the documents throwing the exception
                if(!$backOffice and !$auth){
                    throw $this->createAccessDeniedException();
                //if the user is allowed to download the documents getting all the documents related to the entity person
                }else{
                    $name= "Documentos de ".$person->getFullName();
                    $cedula = $person->getDocumentDocument();
                    $rut= $person->getRutDocument();
                    $registro = $person->getBirthRegDocument();
                    if($cedula){
                        $media= $cedula->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                        }
                        $docName[] = $cedula->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                    if($rut){
                        $media= $rut->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                        }
                        $docName[] = $rut->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                    if($registro){
                        $media= $registro->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($registro->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($registro->getMediaMedia(), 'reference');
                        }
                        $docName[] = $registro->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }
                break;
            case 'Employer':
                //getting the employer from the idEntity parameter
                /** @var Employer $employer */
                $employer = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer")->find($idEntity);
                $name = "Mandato ".$employer->getPersonPerson()->getFullName();
                if(!$employer)
                    throw $this->createNotFoundException();
                //if the employer person is equal to the user person changing auth flag to true
                if($employer->getPersonPerson()==$user->getPersonPerson()){
                    $auth=true;
                }
                //if user is not allowed to download the document throwing the exception
                if(!$backOffice and !$auth){
                    throw $this->createAccessDeniedException();
                    //if the user is allowed to download getting all the documents related to the entity employer
                }else{
                    $mandato = $employer->getMandatoryDocument();
                    if($mandato){
                        $media= $mandato->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($mandato->getMediaMedia(), 'reference');
                        }
                        $docName[] = $mandato->getDocumentTypeDocumentType()->getName().' '.$employer->getPersonPerson()->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }
                break;
            case 'EmployerHasEmployee':
                //getting the employerHsEmployee from the idEntity parameter
                /** @var EmployerHasEmployee $eHE */
                $eHE = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($idEntity);
                $name = "Carta de autorización de ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                if(!$eHE)
                    throw $this->createNotFoundException();
                //if the employerPerson is equal to the user person changing the auth flag to true
                if($eHE->getEmployeeEmployee()->getPersonPerson()==$user->getPersonPerson()){
                    $auth=true;
                }
                if(!$backOffice and !$auth){
                    throw $this->createAccessDeniedException();
                    //if the user is allowed to download getting all the documents related to the entity employerHasEmployee
                }else{
                    $carta = $eHE->getAuthDocument();
                    if($carta){
                        $media= $carta->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($carta->getMediaMedia(), 'reference');
                        }
                        $docName[] = $carta->getDocumentTypeDocumentType()->getName().' '.$eHE->getEmployerEmployer()->getPersonPerson()->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }

                break;
            case 'Contract':

                //getting the contract from the idEntity parameter
                /** @var Contract $contract */
                $contract = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->find($idEntity);
                if(!$contract)
                    throw $this->createNotFoundException();
                $name = "Contrato de ".$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                //if the employer person from the relation is equal to the user person changing the auth flag to true
                if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson()){
                    $auth=true;
                }
                //if user is not alowed to download the document throwing the exception
                if(!$backOffice and !$auth){
                    throw $this->createAccessDeniedException();
                //if the user is alowed to download the document getting all the documents related to the entity contract
                }else{
                    $contract = $contract->getDocumentDocument();
                    if($contract){
                        $media= $contract->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($contract->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($contract->getMediaMedia(), 'reference');
                        }
                        $docName[] = $contract->getDocumentTypeDocumentType()->getName().' '.$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }
                break;
            case 'Payroll':
                /** @var Payroll $payroll */
                $payroll = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->find($idEntity);
                if(!$payroll)
                    throw $this->createNotFoundException();
                $name = "Comprobante de pago de ".$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                if($payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson())
                    $auth=true;
                if(!$backOffice and !$auth) {
                    throw $this->createAccessDeniedException();
                }else{
                    /** @var Document $comprobante */
                    $comprobante = $payroll->getPayslip();
                    if($comprobante){
                        $media= $comprobante->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($comprobante->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($comprobante->getMediaMedia(), 'reference');
                        }
                        $docName[] = $comprobante->getDocumentTypeDocumentType()->getName().' '.$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }
                break;
            case 'Novelty':
                /** @var Novelty $novelty */
                $novelty = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Payroll")->find($idEntity);
                if(!$novelty)
                    throw $this->createNotFoundException();
                $name = 'Documentos de la novedad '.$novelty->getNoveltyTypeNoveltyType()->getName();
                if($novelty->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson())
                    $auth=true;
                if(!$backOffice and !$auth){
                    throw $this->createAccessDeniedException();
                }else{
                    $documents = $novelty->getDocuments();
                    /** @var Document $document */
                    foreach ($documents as $document){
                        $media= $document->getMediaMedia();
                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                        }
                        $docName[] = $document->getDocumentTypeDocumentType()->getName().' '.$novelty->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName().'.'.$media->getExtension();
                        $count++;
                    }
                }
                break;
        }
        //if document count is greater than zero
        if ($count>0){
            // create new zip opbject
            $zip = new ZipArchive();
            //create a temp file & open it
            $tmp_file =$name.".zip";
            if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
                for($i = 0 ; $i<$count;$i++){
                   // loop through each file
                   $zip->addFile($docUrl[$i],$docName[$i]);

               }
                //close zip
                if($zip->close()!==TRUE)
                    echo "no permisos";
                //send the file to the browser as a download
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-type: application/zip');
                header("Content-disposition: attachment; filename=$tmp_file");
                header('Content-Transfer-Encoding: binary');
                ob_clean();
                ob_end_flush();
                readfile($tmp_file);
                ignore_user_abort(true);
                unlink($tmp_file);
            }

        }
        return $this->redirectToRoute('ajax', array(), 301);
    }

    /**
     * Funcion que crea el archivo zip con el contrato del employerHasEmployee que se desea descargar.
     * @param $idContract id del contrato que desea descargarse
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exportContractAction($idContract){
        if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {
            /** @var Contract $contract */
            $contract = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->find($idContract);
            if($contract->getDocumentDocument()){
                $docContract = $contract->getDocumentDocument();
                $media = $docContract->getMediaMedia();
                if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($docContract->getMediaMedia(), 'reference'))){
                    $docUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($docContract->getMediaMedia(), 'reference');
                }
                $docName = $docContract->getDocumentTypeDocumentType()->getName().' '.$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName().'.'.$media->getExtension();
                # create new zip opbject
                $zip = new ZipArchive();
                # create a temp file & open it
                $tmp_file =$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getNames()."_Contract.zip";
                if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
                    # loop through each file
                    $zip->addFile($docUrl,$docName);
                    # close zip
                    if($zip->close()!==TRUE)
                        echo "no permisos";
                    # send the file to the browser as a download
                    header("Content-disposition: attachment; filename=$tmp_file");
                    header('Content-type: application/zip');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    ob_clean();
                    flush();

                    readfile($tmp_file);
                    ignore_user_abort(true);
                    unlink($tmp_file);
                }
			    return $this->redirectToRoute('ajax', array(), 301);
            }
        }else{
            throw $this->createAccessDeniedException("No tiene suficientes permisos");
        }
    }

    /**
     * Funcion que crea el archivo zip con los documentos que ha subido la persona para backoffice.
     * @param $idPerson id de la persona de la que se quieren descargar documentos
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exportDocumentsByPersonAction($idPerson)
    {
    	if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {

			$person = $this->getdoctrine()
			->getRepository('RocketSellerTwoPickBundle:Person')
			->find($idPerson);
            $count = 1;
            if($person->getDocumentDocument()){
                /** @var Document $cedula */
                $cedula = $person->getDocumentDocument();
                /** @var Media $media */
                $media = $cedula->getMediaMedia();
                if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference'))){
                    $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($cedula->getMediaMedia(), 'reference');
                }
                $docName[] = $count.'. '.$cedula->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                $count++;
            }
			if($person->getRutDocument()){
                /** @var Document $rut */
                $rut = $person->getRutDocument();
                /** @var Media $media */
                $media = $rut->getMediaMedia();
                if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference'))){
                    $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($rut->getMediaMedia(), 'reference');
                }
                $docName[] = $count.'. '.$rut->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                $count++;
            }
            if($person->getBirthRegDocument()){
                /** @var Document $registro */
                $registro = $person->getBirthRegDocument();
                /** @var Media $media */
                $media = $registro->getMediaMedia();
                if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($registro->getMediaMedia(), 'reference'))){
                    $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($registro->getMediaMedia(), 'reference');
                }
                $docName[] = $count.'. '.$registro->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                $count++;
            }
			# create new zip opbject
			$zip = new ZipArchive();
            $count--;
			# create a temp file & open it
			$tmp_file =$person->getNames()."_Documents.zip";
			if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
				# loop through each file
				for($i=0;$i<$count;$i++){
					$zip->addFile($docUrl[$i],$docName[$i]);
				}
				# close zip

				if($zip->close()!==TRUE)
					echo "no permisos";
				# send the file to the browser as a download
				header("Content-disposition: attachment; filename=$tmp_file");
				header('Content-type: application/zip');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				ob_clean();
				flush();

				readfile($tmp_file);
				ignore_user_abort(true);
				unlink($tmp_file);
			}
			return $this->redirectToRoute('ajax', array(), 301);
		}else{
			throw $this->createAccessDeniedException("No tiene suficientes permisos");
		}
    }

    /**
     * Funcion que crea el archivo zip con el documento que se desea descargar.
     * @param Integer $idDoc id del documento que se desea descargar
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exportDocumentByIdDocumentAction($idDoc)
    {
        //checking user is authenticated
        if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            throw $this->createAccessDeniedException();
        }
        //flag for backoffice user
        $backOffice=false;
        //flag for permissions
        $auth=false;
        //if user is backoffice changing the backoffice flag
        if($this->isGranted('ROLE_BACK_OFFICE', $this->getUser())){
            $backOffice= true;
        }
        //getting the user
        /** @var User $user */
        $user = $this->getUser();
        /** @var Document $document */
        $document = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Document")->find($idDoc);
        if(!$document)
            throw $this->createNotFoundException();
        switch($document->getDocumentTypeDocumentType()->getDocCode()){
            case 'CC':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('documentDocument'=>$document));
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                    $name = $person->getFullName();
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$person){
                                $auth=true;
                                $name = $eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                                break;
                            }
                        }
                    }
                }
                break;
            case 'RUT':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('rutDocument'=>$document));
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                    $name = $person->getFullName();
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$person){
                                $auth=true;
                                $name = $eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                                break;
                            }
                        }
                    }
                }
                break;
            case 'CTR':
                /** @var Contract $contract */
                $contract = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('documentDocument'=>$document));
                if(!$contract)
                    throw $this->createNotFoundException();
                if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson() == $user->getPersonPerson())
                    $auth=true;
                $name = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                break;
            case 'CAS':
                /** @var EmployerHasEmployee $eHE */
                $eHE = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findOneBy(array('authDocument'=>$document));
                if(!$eHE)
                    throw $this->createNotFoundException();
                if($eHE->getEmployerEmployer()->getPersonPerson() == $user->getPersonPerson())
                    $auth=true;
                $name=$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                break;
            case 'RCDN':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('birthRegDocument'=>$document));
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                    $name = $person->getFullName();
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$person){
                                $auth=true;
                                $name = $eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                                break;
                            }
                        }
                    }
                }
                break;
            case 'MAND':
                /** @var Employer $employer */
                $employer =$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer")->findOneBy(array('mandatoryDocument'=>$document));
                if(!$employer)
                    throw $this->createNotFoundException();
                if($employer->getPersonPerson() == $user->getPersonPerson())
                    $auth=true;
                $name=$employer->getPersonPerson()->getFullName();
                break;
            case 'CPR':
                /** @var Payroll $payroll */
                $payroll = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll")->findOneBy(array('payslip'=>$document));
                if(!$payroll)
                    throw $this->createNotFoundException();
                if($payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson() == $user->getPersonPerson())
                    $auth=true;
                $name=$payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
                break;
            case 'TI':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('documentDocument'=>$document));
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                    $name = $person->getFullName();
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$person){
                                $auth=true;
                                $name = $eHE->getEmployeeEmployee()->getPersonPerson()->getFullName();
                                break;
                            }
                        }
                    }
                }
                break;
        }
        if(!$backOffice and !$auth)
            throw $this->createAccessDeniedException();
        $media = $document->getMediaMedia();
        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
            $docUrl = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
        }
        $docName = $document->getDocumentTypeDocumentType()->getName().' '.$name.'.'.$media->getExtension();
        # create new zip opbject
        $zip = new ZipArchive();
        # create a temp file & open it
        $tmp_file ="Download_".$document->getDocumentTypeDocumentType()->getName().".zip";
        if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
            # loop through each file
            $zip->addFile($docUrl,$docName);
            # close zip
            if($zip->close()!==TRUE)
                echo "no permisos";
            # send the file to the browser as a download
            header("Content-disposition: attachment; filename=$tmp_file");
            header('Content-type: application/zip');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($tmp_file);
            ignore_user_abort(true);
            unlink($tmp_file);
        }
        return $this->redirectToRoute('ajax', array(), 301);
    }


    /**
     * Export all the documents related to an action
     * @param $idAction
     */
    public function exportAllDocumentsAction($idAction)
    {
        if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {
            $action = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Action')
                ->find($idAction);
            // getting the person that owns the action
            /** @var Person $person */
            $person = $action->getPersonPerson();
            //getting the person documents
            $personDocuments=$person->getDocs();
            //seting the document count to 1 for the documents names
            $count = 1;
            // if the action belongs to a employee we obtain the employer with the action user
            if($person->getIdPerson()!= $action->getUserUser()->getPersonPerson()->getIdPerson()){
                //Getting the employer person
                /** @var Person $user */
                $user = $action->getUserUser()->getPersonPerson();
                //getting the eployer documents
                $userDocuments = $user->getDocs();
                //adding all the documents to the document array that will load archives to the zip file
                /** @var Document $document */
                foreach ($userDocuments as $document) {
                    /** @var Media $media */
                    //getting the document media
                    $media = $document->getMediaMedia();
                    //if the reference to the media exist in the DataBase we asign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                    }
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$document->getDocumentTypeDocumentType()->getName().' '.$user->getFullName().'.'.$media->getExtension();
                    //document count add
                    $count++;
                }
            }
            //searching the documents for the employee if the action belongs to a employee else getting the employer documents
            /** @var Document $document */
            foreach ($personDocuments as $document) {
                if($document->getDocumentTypeDocumentType()->getName()=='Carta autorización Symplifica'){
                    $eHEs= $document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers();
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($eHEs as $eHE){
                        if($eHE->getEmployerEmployer()==$document->getEmployerEmployer() and $eHE->getEmployeeEmployee()->getPersonPerson()==$document->getPersonPerson()){
                            /** @var Media $media */
                            $media = $document->getMediaMedia();
                            if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
                                $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                            }
                            $docName[] = $count.'. '.$document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                            $count++;
                        }
                    }
                }elseif($document->getDocumentTypeDocumentType()->getName()=='Contrato'){
                    $eHEs= $document->getPersonPerson()->getEmployee()->getEmployeeHasEmployers();
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($eHEs as $eHE){
                        if($eHE->getEmployerEmployer()==$document->getEmployerEmployer() and $eHE->getEmployeeEmployee()->getPersonPerson()==$document->getPersonPerson()) {
                            $contracts = $eHE->getContracts();
                            /** @var Contract $contract */
                            foreach ($contracts as $contract) {
                                if ($contract->getState() == 1) {
                                    if ($contract->getDocumentDocument()==$document){
                                        /** @var Media $media */
                                        $media = $document->getMediaMedia();
                                        if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
                                            $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                                        }
                                        $docName[] = $count.'. '.$document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                                        $count++;
                                    }
                                }
                            }
                        }
                    }
                }elseif($document->getDocumentTypeDocumentType()->getName()=='Comprobante'){
                    // do nothing
                    dump("comprobante");
                }else{
                    /** @var Media $media */
                    $media = $document->getMediaMedia();
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                    }
                    $docName[] = $count.'. '.$document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
            }
            $count--;

            /** @var Person $person */
            $person= $action->getUserUser()->getPersonPerson();
            /** @var Person $employee */
            $employee = $action->getPersonPerson();

            // $tmp_file=$person->getNames()."_fields.csv";
            $csv=$this->container->getParameter('kernel.cache_dir') .$person->getNames()."_fields.csv";
            $handle = fopen($csv, 'w+');
            fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

            // Add the header of the CSV file
            fputcsv($handle, array('INFORMACIÓN DEL EMPLEADOR'),';');
            fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),';');

            //first the user info
            fputcsv($handle, array('Nombre Completo del Empleador',$person->getFullName()),';');
            if($person->getDocumentType()=='CC'){
                fputcsv($handle, array('Tipo de Documento del Empleador','Cedula de Ciudadania'),';');
            }elseif ($person->getDocumentType()=='CE') {
                fputcsv($handle, array('Tipo de Documento del Empleador','Cedula de Extranjeria'),';');
            }elseif ($person->getDocumentType()=='TI'){
                fputcsv($handle, array('Tipo de Documento del Empleador','Tarjeta de Identidad'),';');
            }
            fputcsv($handle, array(' '.'Numero de Documento del Empleador',$person->getDocument().' '),';');
            fputcsv($handle, array('Fecha de Expedición del Documento del Empleador',$person->getDocumentExpeditionDate()->format('d/m/y')),';');
            fputcsv($handle, array('Fecha de nacimiento del Empleador',$person->getBirthDate()->format('d/m/y')),';');
            fputcsv($handle, array('Dirección del Empleador',$person->getMainAddress()),';');
            /** @var Phone $phone */
            foreach ($person->getPhones() as $phone){
                fputcsv($handle, array('Telefono/celular del Empleador',$phone->getPhoneNumber()),';');
            }
            fputcsv($handle, array('Ciudad/Municipio del Empleador',$person->getCity()),';');
            fputcsv($handle, array('Departamento del Empleador',$person->getDepartment()),';');
            fputcsv($handle, array('ENTIDADES',''),';');
            /** @var EmployerHasEntity $employerHasEntity */
            foreach ($person->getEmployer()->getEntities() as $employerHasEntity){
                if($employerHasEntity->getState()==0){
                    fputcsv($handle, array($employerHasEntity->getEntityEntity()->getEntityTypeEntityType().' del Empleador',$employerHasEntity->getEntityEntity()->getName()),';');
                }elseif($employerHasEntity->getState()==1){
                    fputcsv($handle, array($employerHasEntity->getEntityEntity()->getEntityTypeEntityType().' a la que desea inscribirse el Empleador',$employerHasEntity->getEntityEntity()->getName()),';');
                }
            }
            fputcsv($handle, array('Nombre Completo del Representante Legal',$person->getFullName()),';');
            if($person->getDocumentType()=='CC'){
                fputcsv($handle, array('Tipo de Documento del Representante Legal','Cedula de Ciudadania'),';');
            }elseif ($person->getDocumentType()=='CE') {
                fputcsv($handle, array('Tipo de Documento del Representante Legal','Cedula de Extranjeria'),';');
            }elseif ($person->getDocumentType()=='TI'){
                fputcsv($handle, array('Tipo de Documento del Representante Legal','Tarjeta de Identidad'),';');
            }
            fputcsv($handle, array(' '.'Numero de Documento del Representante Legal',$person->getDocument().' '),';');
            fputcsv($handle, array('Fecha de Expedición del Documento del Representante Legal',$person->getDocumentExpeditionDate()->format('d/m/y')),';');
            fputcsv($handle, array('Fecha de nacimiento del Representante Legal',$person->getBirthDate()->format('d/m/y')),';');

            if($employee->getEmployee()){
                fputcsv($handle, array('',''),';');
                fputcsv($handle, array('INFORMACIÓN DEL EMPLEADO',''),';');
                fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),';');
                fputcsv($handle, array('Nombre Completo del empleado',$employee->getFullName()),';');
                if($employee->getDocumentType()=='CC'){
                    fputcsv($handle, array('Tipo de Documento del Empleado','Cedula de Ciudadania'),';');
                }elseif ($employee->getDocumentType()=='CE') {
                    fputcsv($handle, array('Tipo de Documento del Empleado','Cedula de Extranjeria'),';');
                }elseif ($employee->getDocumentType()=='TI'){
                    fputcsv($handle, array('Tipo de Documento del Empleado','Tarjeta de Identidad'),';');
                }
                fputcsv($handle, array('Numero de Documento del empleado',$employee->getDocument().' '),';');
                fputcsv($handle, array('Fecha de Expedición del Documento del empleado',$employee->getDocumentExpeditionDate()->format('d/m/y')),';');
                fputcsv($handle, array('Fecha de nacimiento del Empleado',$employee->getBirthDate()->format('d/m/y')),';');
                fputcsv($handle, array('Lugar de nacimiento del Empleado',$employee->getBirthCity().';'.$employee->getBirthCountry()),';');
                fputcsv($handle, array('Genero del Empleado',$employee->getGender()),';');
                fputcsv($handle, array('Dirección del Empleado',$employee->getMainAddress()),';');
                fputcsv($handle, array('Ciudad/Municipio del Empleado',$employee->getCity()),';');
                fputcsv($handle, array('Departamento del Empleado',$employee->getDepartment()),';');
                /** @var Phone $phone */
                foreach ($employee->getPhones() as $employeePhone){
                    fputcsv($handle, array('Telefono/celular del Empleado',$employeePhone->getPhoneNumber()),';');
                }
                if($employee->getEmail()) fputcsv($handle, array('Correo Electrónico del Empleado',$employee->getEmail()),';');
                fputcsv($handle, array('ENTIDADES',''),';');
                /** @var EmployeeHasEntity $employeeHasEntity */
                foreach ($employee->getEmployee()->getEntities() as $employeeHasEntity){
                    if($employeeHasEntity->getEntityEntity()->getName()!='severances'){
                        if($employeeHasEntity->getState()==0){
                            fputcsv($handle, array($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType().' del Empleado',$employeeHasEntity->getEntityEntity()->getName()),';');
                        }elseif($employeeHasEntity->getState()==1){
                            fputcsv($handle, array($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType().' a la que desea inscribirse el Empleado',$employeeHasEntity->getEntityEntity()->getName()),';');
                        }
                    }

                }

                fputcsv($handle, array('',''),';');
                fputcsv($handle, array('INFORMACIÓN DEL CONTRATO',''),';');
                fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),';');
                /** @var EmployerHasEmployee $employerHasEmployee */
                foreach ($person->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                    if($employerHasEmployee->getEmployeeEmployee()->getIdEmployee()==$employee->getEmployee()->getIdEmployee() and $employerHasEmployee->getEmployerEmployer()->getIdEmployer()==$person->getEmployer()->getIdEmployer()){
                        /** @var Contract $contract */
                        foreach($employerHasEmployee->getContracts() as $contract){
                            if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee() == $employerHasEmployee->getIdEmployerHasEmployee()){
                                fputcsv($handle, array('Dirección de trabajo',$contract->getWorkplaceWorkplace()->getMainAddress()),';');
                                fputcsv($handle, array('Departamento de la dirección de trabajo',$contract->getWorkplaceWorkplace()->getDepartment()),';');
                                fputcsv($handle, array('Ciudad de la dirección de trabajo',$contract->getWorkplaceWorkplace()->getCity()),';');
                                fputcsv($handle, array('Jornada Laboral',$contract->getContractTypeContractType()->getName()),';');
                                fputcsv($handle, array('Tiempo de trabajo',$contract->getTimeCommitmentTimeCommitment()->getName()),';');
                                fputcsv($handle, array('Dias que trabaja al mes',$contract->getWorkableDaysMonth()),';');
                                fputcsv($handle, array('Salario del empleado',$contract->getSalary()),';');
                                fputcsv($handle, array('Cargo del empleado',$contract->getPositionPosition()->getName()),';');
                                fputcsv($handle, array('Fecha de inicio del contrato',$contract->getStartDate()->format('d/m/y')),';');
                                if($contract->getEndDate()) {
                                    fputcsv($handle, array('Fecha de fin del contrato',$contract->getEndDate()->format('d/m/y')),';');
                                }
                                break;
                            }
                        }
                    }
                }
            }

            fclose($handle);

            # create new zip opbject
            $zip = new ZipArchive();
            # create a temp file & open it
            $tmp_file =$action->getUserUser()->getPersonPerson()->getNames()."_Documents.zip";
            if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
                # loop through each file
                for($i=0;$i<$count;$i++){
                    $zip->addFile($docUrl[$i],$docName[$i]);
                }
                $zip->addFile($csv,$user->getFullName().'.csv');
                if($zip->close()!==TRUE)
                    echo "no permisos";
                # send the file to the browser as a download

                header("Content-disposition: attachment; filename=$tmp_file");
                header('Content-type: application/zip');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                ob_clean();
                flush();

                readfile($tmp_file);
                ignore_user_abort(true);
                unlink($tmp_file);
            }

        }else{
            throw $this->createAccessDeniedException("No tiene suficientes permisos");
        }
    }

	public function generateCsvAction(){
		/** @var User $user */
		$user = $this->getUser();
		$tmp_file=$user->getPersonPerson()->getNames()."_fields.csv";
		$handle = fopen($tmp_file, 'w+');

		// Add the header of the CSV file
		fputcsv($handle, array('sep=;'));
		fputcsv($handle, array('Campo', 'Dato'),';');
		fputcsv($handle, array('Persona', 'Empleador'),';');
		//first the user info
		/** @var User $user */
		$person=$user->getPersonPerson();
		$em = $this->getDoctrine()->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare("SELECT * FROM person WHERE id_person = :id");
		$statement->bindValue('id', $person->getIdPerson());
		$statement->execute();
		// Add the data queried from database
		while( $row = $statement->fetch() )
		{
			foreach ($row as $key => $value) {
				fputcsv(
					$handle, // The file pointer
					array($key, $value), // The fields
					';' // The delimiter
				);
			}
		}
		//now for his empoyees
		$employer=$person->getEmployer();
		$employerHasEmployees=$employer->getEmployerHasEmployees();
		/** @var EmployerHasEmployee $eHE */
		foreach ($employerHasEmployees as $eHE) {
			fputcsv($handle, array('Persona', 'Empleado'),';');
			$employee=$eHE->getEmployeeEmployee();
			$statement = $connection->prepare("SELECT * FROM person WHERE id_person = :id");
			$statement->bindValue('id', $employee->getPersonPerson()->getIdPerson());
			$statement->execute();
			// Add the data queried from database
			while( $row = $statement->fetch() )
			{
				foreach ($row as $key => $value) {
					fputcsv(
						$handle, // The file pointer
						array($key, $value), // The fields
						';' // The delimiter
					);
				}
			}
			$benefs=$employee->getEmployeeHasBeneficiary();
			/** @var EmployeeHasBeneficiary $beneficiary */
			foreach ($benefs as $beneficiary) {
				fputcsv($handle, array('Persona', 'Beneficiario'),';');
				$beneficiaryPerson=$beneficiary->getBeneficiaryBeneficiary()->getPersonPerson();
				$statement = $connection->prepare("SELECT * FROM person WHERE id_person = :id");
				$statement->bindValue('id', $beneficiaryPerson->getIdPerson());
				$statement->execute();
				// Add the data queried from database
				while( $row = $statement->fetch() )
				{
					foreach ($row as $key => $value) {
						fputcsv(
							$handle, // The file pointer
							array($key, $value), // The fields
							';' // The delimiter
						);
					}
				}
			}


		}



		fclose($handle);

		header("Content-disposition: attachment; filename=$tmp_file");
		header('Content-type: text/csv');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		ob_clean();
		flush();
		readfile($tmp_file);
		ignore_user_abort(true);
		unlink($tmp_file);
	}

	public function generateCsvByActionAction($idAction){
		$em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository('RocketSellerTwoPickBundle:Action')
                ->find($idAction);
        /** @var Person $person */
        $person= $action->getUserUser()->getPersonPerson();
        /** @var Person $employee */
        $employee = $action->getPersonPerson();

//        // ask the service for a Excel5
//        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
//
//        $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
//            ->setLastModifiedBy("Symplifica")
//            ->setTitle("general info")
//            ->setSubject("details")
//            ->setDescription("generated document with the employerHasEmployee information")
//            ->setKeywords("employee employeer contract")
//            ->setCategory("Information");
//        $phpExcelObject->setActiveSheetIndex(0)
//            ->setCellValue('A1', 'Hello')
//            ->setCellValue('B2', 'world!');
//        $phpExcelObject->getActiveSheet()->setTitle('Simple');
//        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
//        $phpExcelObject->setActiveSheetIndex(0);
//
//        // create the writer
//        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
//        // create the response
//        $response = $this->get('phpexcel')->createStreamedResponse($writer);
//        // adding headers
//        $dispositionHeader = $response->headers->makeDisposition(
//            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
//            'Info_'.$employee->getFullName().'_'.date('d-m-y').'.xlsx'
//        );
//        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
//        $response->headers->set('Pragma', 'public');
//        $response->headers->set('Cache-Control', 'maxage=1');
//        $response->headers->set('Content-Disposition', $dispositionHeader);
//        return $response;

        // $tmp_file=$person->getNames()."_fields.csv";
		$tmp_file=$this->container->getParameter('kernel.cache_dir') .$person->getNames()."_fields.csv";
        $handle = fopen($tmp_file, 'w+');
        fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        // Add the header of the CSV file
		fputcsv($handle, array('INFORMACIÓN DEL EMPLEADOR'),',');
		fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),',');

        //first the user info
		fputcsv($handle, array('Nombre Completo del Empleador',$person->getFullName()),',');
		if($person->getDocumentType()=='CC'){
			fputcsv($handle, array('Tipo de Documento del Empleador','Cedula de Ciudadania'),',');
		}elseif ($person->getDocumentType()=='CE') {
			fputcsv($handle, array('Tipo de Documento del Empleador','Cedula de Extranjeria'),',');
		}elseif ($person->getDocumentType()=='TI'){
			fputcsv($handle, array('Tipo de Documento del Empleador','Tarjeta de Identidad'),',');
		}
		fputcsv($handle, array(' '.'Numero de Documento del Empleador',$person->getDocument().' '),',');
		fputcsv($handle, array('Fecha de Expedición del Documento del Empleador',$person->getDocumentExpeditionDate()->format('d/m/y')),',');
		fputcsv($handle, array('Fecha de nacimiento del Empleador',$person->getBirthDate()->format('d/m/y')),',');
		fputcsv($handle, array('Dirección del Empleador',$person->getMainAddress()),',');
		/** @var Phone $phone */
		foreach ($person->getPhones() as $phone){
			fputcsv($handle, array('Telefono/celular del Empleador',$phone->getPhoneNumber()),',');
		}
		fputcsv($handle, array('Ciudad/Municipio del Empleador',$person->getCity()),',');
		fputcsv($handle, array('Departamento del Empleador',$person->getDepartment()),',');
        fputcsv($handle, array('ENTIDADES',''),',');
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($person->getEmployer()->getEntities() as $employerHasEntity){
            if($employerHasEntity->getState()==0){
                fputcsv($handle, array($employerHasEntity->getEntityEntity()->getEntityTypeEntityType().' del Empleador',$employerHasEntity->getEntityEntity()->getName()),',');
            }elseif($employerHasEntity->getState()==1){
                fputcsv($handle, array($employerHasEntity->getEntityEntity()->getEntityTypeEntityType().' a la que desea inscribirse el Empleador',$employerHasEntity->getEntityEntity()->getName()),',');
            }
        }
        fputcsv($handle, array('Nombre Completo del Representante Legal',$person->getFullName()),',');
        if($person->getDocumentType()=='CC'){
            fputcsv($handle, array('Tipo de Documento del Representante Legal','Cedula de Ciudadania'),',');
        }elseif ($person->getDocumentType()=='CE') {
            fputcsv($handle, array('Tipo de Documento del Representante Legal','Cedula de Extranjeria'),',');
        }elseif ($person->getDocumentType()=='TI'){
            fputcsv($handle, array('Tipo de Documento del Representante Legal','Tarjeta de Identidad'),',');
        }
        fputcsv($handle, array(' '.'Numero de Documento del Representante Legal',$person->getDocument().' '),',');
        fputcsv($handle, array('Fecha de Expedición del Documento del Representante Legal',$person->getDocumentExpeditionDate()->format('d/m/y')),',');
        fputcsv($handle, array('Fecha de nacimiento del Representante Legal',$person->getBirthDate()->format('d/m/y')),',');

        if($employee->getEmployee()){
            fputcsv($handle, array('',''),',');
            fputcsv($handle, array('INFORMACIÓN DEL EMPLEADO',''),',');
            fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),',');
            fputcsv($handle, array('Nombre Completo del empleado',$employee->getFullName()),',');
            if($employee->getDocumentType()=='CC'){
                fputcsv($handle, array('Tipo de Documento del Empleado','Cedula de Ciudadania'),',');
            }elseif ($employee->getDocumentType()=='CE') {
                fputcsv($handle, array('Tipo de Documento del Empleado','Cedula de Extranjeria'),',');
            }elseif ($employee->getDocumentType()=='TI'){
                fputcsv($handle, array('Tipo de Documento del Empleado','Tarjeta de Identidad'),',');
            }
            fputcsv($handle, array('Numero de Documento del empleado',$employee->getDocument().' '),',');
            fputcsv($handle, array('Fecha de Expedición del Documento del empleado',$employee->getDocumentExpeditionDate()->format('d/m/y')),',');
            fputcsv($handle, array('Fecha de nacimiento del Empleado',$employee->getBirthDate()->format('d/m/y')),',');
            fputcsv($handle, array('Lugar de nacimiento del Empleado',$employee->getBirthCity().';'.$employee->getBirthCountry()),',');
            fputcsv($handle, array('Genero del Empleado',$employee->getGender()),',');
            fputcsv($handle, array('Dirección del Empleado',$employee->getMainAddress()),',');
            fputcsv($handle, array('Ciudad/Municipio del Empleado',$employee->getCity()),',');
            fputcsv($handle, array('Departamento del Empleado',$employee->getDepartment()),',');
            /** @var Phone $phone */
            foreach ($employee->getPhones() as $employeePhone){
                fputcsv($handle, array('Telefono/celular del Empleado',$employeePhone->getPhoneNumber()),',');
            }
            if($employee->getEmail()) fputcsv($handle, array('Correo Electrónico del Empleado',$employee->getEmail()),',');
            fputcsv($handle, array('ENTIDADES',''),',');
            /** @var EmployeeHasEntity $employeeHasEntity */
            foreach ($employee->getEmployee()->getEntities() as $employeeHasEntity){
                if($employeeHasEntity->getEntityEntity()->getName()!='severances'){
                    if($employeeHasEntity->getState()==0){
                        fputcsv($handle, array($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType().' del Empleado',$employeeHasEntity->getEntityEntity()->getName()),',');
                    }elseif($employeeHasEntity->getState()==1){
                        fputcsv($handle, array($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType().' a la que desea inscribirse el Empleado',$employeeHasEntity->getEntityEntity()->getName()),',');
                    }
                }

            }

            fputcsv($handle, array('',''),',');
            fputcsv($handle, array('INFORMACIÓN DEL CONTRATO',''),',');
            fputcsv($handle, array('CAMPO', 'VALOR DEL CAMPO'),',');
            /** @var EmployerHasEmployee $employerHasEmployee */
            foreach ($person->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                if($employerHasEmployee->getEmployeeEmployee()->getIdEmployee()==$employee->getEmployee()->getIdEmployee() and $employerHasEmployee->getEmployerEmployer()->getIdEmployer()==$person->getEmployer()->getIdEmployer()){
                    /** @var Contract $contract */
                    foreach($employerHasEmployee->getContracts() as $contract){
                        if($contract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee() == $employerHasEmployee->getIdEmployerHasEmployee()){
                            fputcsv($handle, array('Dirección de trabajo',$contract->getWorkplaceWorkplace()->getMainAddress()),',');
                            fputcsv($handle, array('Departamento de la dirección de trabajo',$contract->getWorkplaceWorkplace()->getDepartment()),',');
                            fputcsv($handle, array('Ciudad de la dirección de trabajo',$contract->getWorkplaceWorkplace()->getCity()),',');
                            fputcsv($handle, array('Jornada Laboral',$contract->getContractTypeContractType()->getName()),',');
                            fputcsv($handle, array('Tiempo de trabajo',$contract->getTimeCommitmentTimeCommitment()->getName()),',');
                            fputcsv($handle, array('Dias que trabaja al mes',$contract->getWorkableDaysMonth()),',');
                            fputcsv($handle, array('Salario del empleado',$contract->getSalary()),',');
                            fputcsv($handle, array('Cargo del empleado',$contract->getPositionPosition()->getName()),',');
                            fputcsv($handle, array('Fecha de inicio del contrato',$contract->getStartDate()->format('d/m/y')),',');
                            $date = $contract->getEndDate();
                            if($date != ""){
                                echo "entro";die;
                                fputcsv($handle, array('Fecha de fin del contrato',$contract->getEndDate()->format('d/m/y')),',');
                            }

                            break;
                        }
                    }
                }
            }
        }

		fclose($handle);
        header("Content-Disposition: attachment; filename=$tmp_file");
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($tmp_file);
        ignore_user_abort(true);
        unlink($tmp_file);
	}

    public function exportLandingAction(){

        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $landings = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:LandingRegistration')
            ->findAll();
        $tmp_file="Landing.csv";
        $handle = fopen($tmp_file, 'w+');
        fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        fputcsv($handle, array('INFORMACIÓN DEL LANDING SYMPLIFICA'),';');
        fputcsv($handle, array('TIPO DE INSCRIPCIÓN', 'NOMBRE','E-MAIL','TELEFONO','FECHA DE INSCRIPCIÓN'),';');
        foreach ($landings as $landing){
            fputcsv($handle, array($landing->getEntityType(), $landing->getName(),$landing->getEmail(),$landing->getPhone(),$landing->getCreatedAt()->format('d/m/y')),';');
        }

        fclose($handle);
        header("Content-Disposition: attachment; filename=$tmp_file");
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header('Content-Transfer-Encoding: binary');
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        ob_clean();
        ob_end_flush();
        flush();
        readfile($tmp_file);
        ignore_user_abort(true);
        unlink($tmp_file);
    }
}
