<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use PHPExcel;
use PHPExcel_RichText;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Font;
use PHPExcel_Writer_Excel2007;
use RocketSeller\TwoPickBundle\Entity\EmailInfo;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
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
                //if($zip->close()!==TRUE)
                  //  dump("no permisos");
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
            case 'CE':
            case 'TI':
            case 'PASAPORTE':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('documentDocument'=>$document));
                $name = $person->getFullName();
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson()){
                                $auth=true;
                                break;
                            }
                        }
                    }
                }
                break;
            case 'RUT':
                /** @var Person $person */
                $person = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array('rutDocument'=>$document));
                $name = $person->getFullName();
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson()){
                                $auth=true;
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
                $name = $person->getFullName();
                if(!$person)
                    throw $this->createNotFoundException();
                if($person == $user->getPersonPerson()){
                    $auth=true;
                }else{
                    if($person->getEmployee()){
                        $eHEs = $person->getEmployee()->getEmployeeHasEmployers();
                        /** @var EmployerHasEmployee $eHE */
                        foreach ($eHEs as $eHE){
                            if($eHE->getEmployerEmployer()->getPersonPerson()==$user->getPersonPerson()){
                                $auth=true;
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
    function is_url_exist($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($code == 200){
            $status = true;
        }else{
            $status = false;
        }
        curl_close($ch);
        return $status;
    }


    /**
     * Export all the documents related to an action
     * @param $idAction
     */
    public function exportAllDocumentsAction($idAction)
    {
        if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {
            //getting the action
            /** @var Action $action */
            $action = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Action')
                ->find($idAction);
            // getting the person that owns the action
            /** @var Person $person */
            $person = $action->getPersonPerson();
            //getting the user that owns the action
            /** @var User $user */
            $user = $action->getUserUser();
            //seting the document count to 1 for the documents names
            $count = 1;
            //if the user person is the same that the action person means its the employer
            if($user->getPersonPerson()==$person){
                /** @var Document $cedula */
                $cedula = $person->getDocumentDocument();
                if($cedula){
                    /** @var Media $media */
                    //getting the document media
                    $media = $cedula->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $rut = $person->getRutDocument();
                if($rut){
                    /** @var Media $media */
                    //getting the document media
                    $media = $rut->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $registro= $person->getBirthRegDocument();
                if($registro){
                    /** @var Media $media */
                    //getting the document media
                    $media = $registro->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Employer $employer */
                //getting the employer
                $employer=$person->getEmployer();
                /** @var Document $mandatory */
                //getting the mandatory from the employer
                $mandatory = $employer->getMandatoryDocument();
                if($mandatory){
                    /** @var Media $media */
                    //getting the document media
                    $media = $mandatory->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
            //else person is the employee
            }else{
                /** @var Document $cedula */
                $cedula = $user->getPersonPerson()->getDocumentDocument();
                if($cedula){
                    /** @var Media $media */
                    //getting the document media
                    $media = $cedula->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $rut = $user->getPersonPerson()->getRutDocument();
                if($rut){
                    /** @var Media $media */
                    //getting the document media
                    $media = $rut->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $registro= $user->getPersonPerson()->getBirthRegDocument();
                if($registro){
                    /** @var Media $media */
                    //getting the document media
                    $media = $registro->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Employer $employer */
                //getting the employer
                $employer=$user->getPersonPerson()->getEmployer();
                /** @var Document $mandatory */
                //getting the mandatory from the employer
                $mandatory = $employer->getMandatoryDocument();
                if($mandatory){
                    /** @var Media $media */
                    //getting the document media
                    $media = $mandatory->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $eCedula = $person->getDocumentDocument();
                if($eCedula){
                    /** @var Media $media */
                    //getting the document media
                    $media = $eCedula->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $eRut = $person->getRutDocument();
                if($eRut){
                    /** @var Media $media */
                    //getting the document media
                    $media = $eRut->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Document $cedula */
                $eRegistro= $person->getBirthRegDocument();
                if($eRegistro){
                    /** @var Media $media */
                    //getting the document media
                    $media = $eRegistro->getMediaMedia();
                    $tFile=false;
                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                        $tFile=true;

                    }
                    $file[]=$tFile;
                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                    $count++;
                }
                /** @var Employee $employee */
                //getting the employee
                $employee = $person->getEmployee();
                /** @var EmployerHasEmployee $eHE */
                //crossing the employerHasEmployees to find the actual eHE
                foreach ($employee->getEmployeeHasEmployers() as $eHE){
                    if($eHE->getEmployerEmployer()==$employer){
                        /** @var Document $cas */
                        //getting the auth document
                        $cas=$eHE->getAuthDocument();
                        if($cas){
                            /** @var Media $media */
                            //getting the document media
                            $media = $cas->getMediaMedia();
                            $tFile=false;
                            //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                            if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                                $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                            }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                                $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                                $tFile=true;

                            }
                            $file[]=$tFile;
                            //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                            $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                            $count++;
                        }
                        /** @var Contract $contract */
                        //getting the active contract
                        foreach ($eHE->getContracts() as $contract){
                            if($contract->getState()==1){
                                /** @var Document $activeContract */
                                $activeContract = $contract->getDocumentDocument();
                                if($activeContract){
                                    /** @var Media $media */
                                    //getting the document media
                                    $media = $activeContract->getMediaMedia();
                                    $tFile=false;
                                    //if the reference to the media exist in the DataBase we assign the document path to the array $docUrl
                                    if(file_exists(getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                                        $docUrl[] = getcwd().$this->container->get('sonata.media.twig.extension')->path($media, 'reference');
                                    }elseif($this->is_url_exist($this->container->get('sonata.media.twig.extension')->path($media, 'reference'))){
                                        $docUrl[] = file_get_contents($this->container->get('sonata.media.twig.extension')->path($media, 'reference'));
                                        $tFile=true;

                                    }
                                    $file[]=$tFile;
                                    //after the path has been set we asign the name of the document in the array docName that will be use later to asign names to files in the zip archive
                                    $docName[] = $count.'. '.$media->getDocumentDocument()->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.'.$media->getExtension();
                                    $count++;
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            $count--;
            /** @var Person $person */
            $person= $action->getUserUser()->getPersonPerson();
            /** @var Person $employee */
            $employee = $action->getPersonPerson();
            /** @var User $user */
            $user = $action->getUserUser();
            // ask the service for a Excel5
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            //setting some properties
            $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
                ->setLastModifiedBy("Symplifica-Bot")
                ->setTitle("General employerHasEmployee Info")
                ->setSubject("Details")
                ->setDescription("generated document with the employerHasEmployee information")
                ->setKeywords("employee employeer contract")
                ->setCategory("Information");
            //setting the active sheet and changing name
            $phpExcelObject->setActiveSheetIndex(0)->setTitle('Informacion');
            //getting the active sheet
            $sheet = $phpExcelObject->getActiveSheet();
            $outlineBorderTitleStyle= array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FFFFFFFF'),
                    'bold' => true,
                    'size' => 12,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF818181'),
                ),
                'alignment'=>array(
                    'horizontal'=>'center',
                    'vertical'=>'center',
                ),
            );
            $allBordersContentStyle = array(
                'borders'=>array(
                    'allborders'=> array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FF000000'),
                    'bold' => true,
                    'size' => 11,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FFDBDBDB'),
                ),
                'alignment'=>array(
                    'horizontal'=>'left',
                    'vertical'=>'center',
                ),

            );
            $allBordersNoContentStyle = array(
                'borders'=>array(
                    'allborders'=> array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FF000000'),
                    'size' => 10,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FFFAFAFA'),
                ),
                'alignment'=>array(
                    'horizontal'=>'left',
                    'vertical'=>'center',
                ),

            );
            $entitiesStyle= array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FFFFFFFF'),
                    'bold' => true,
                    'size' => 12,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF121869'),
                ),
                'alignment'=>array(
                    'horizontal'=>'center',
                    'vertical'=>'center',
                ),
            );
            $entitiesStyle2= array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FFFFFFFF'),
                    'bold' => true,
                    'size' => 11,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF3F46A2'),
                ),
                'alignment'=>array(
                    'horizontal'=>'center',
                    'vertical'=>'center',
                ),
            );
            $entitiesStyle3= array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FF000000'),
                    'bold' => true,
                    'size' => 11,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF2BABCB'),
                ),
                'alignment'=>array(
                    'horizontal'=>'center',
                    'vertical'=>'center',
                ),
            );
            $contractTitleStyle= array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FFFFFFFF'),
                    'bold' => true,
                    'size' => 12,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF234C19'),
                ),
                'alignment'=>array(
                    'horizontal'=>'center',
                    'vertical'=>'center',
                ),
            );
            $contractStyle= array(
                'borders'=>array(
                    'allborders'=> array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'font'=>array(
                    'name'=>'Calibri',
                    'color' => array('argb'=>'FF000000'),
                    'bold' => true,
                    'size' => 12,
                ),
                'fill'=>array(
                    'type'=>'solid',
                    'color'=>array('argb'=>'FF91C184'),
                ),
                'alignment'=>array(
                    'horizontal'=>'left',
                    'vertical'=>'center',
                ),
            );

            //Employer Basic Info
            $sheet->setCellValue('B2','INFORMACION DEL EMPLEADOR');
            $row = 2;
            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->getStyle('B2:D2')->applyFromArray($outlineBorderTitleStyle);
            $sheet->getColumnDimension('A')->setWidth(2);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(55);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(55);
            $sheet->getRowDimension(2)->setRowHeight(15);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NOMBRE DEL EMPLEADOR');
            $cell2->setValue($person->getFullName());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('TIPO DE DOCUMENTO');
            if($person->getDocumentType()=='CC'){
                $cell2->setValue('Cedula de Ciudadania');
            }elseif ($person->getDocumentType()=='CE') {
                $cell2->setValue('Cedula de Extranjeria');
            }elseif ($person->getDocumentType()=='TI'){
                $cell2->setValue('Tarjeta de Identidad');
            }
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NUMERO DE DOCUMENTO');
            $cell2->setValue($person->getDocument());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
            $cell2->setValue($person->getDocumentExpeditionDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE NACIMIENTO');
            $cell2->setValue($person->getBirthDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('DIRECCION DE RESIDENCIA EMPLEADOR');
            $cell2->setValue($person->getMainAddress());
            $sheet->mergeCells('B'.$row.':C'.$row);
            /** @var Phone $phone */
            foreach ($person->getPhones() as $phone){
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('TELEFONO/ CELULAR EMPLEADOR');
                $cell2->setValue($phone->getPhoneNumber());
                $sheet->mergeCells('B'.$row.':C'.$row);
            }
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('CIUDAD/MUNICIPIO EMPLEADOR');
            $cell2->setValue($person->getCity());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('DEPARTAMENTO EMPLEADOR');
            $cell2->setValue($person->getDepartment());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('EMAIL DEL EMPLEADO');
            $cell2->setValue($user->getEmail());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $sheet->getStyle('B3:C'.$row)->applyFromArray($allBordersContentStyle);
            $sheet->getStyle('D3:D'.$row)->applyFromArray($allBordersNoContentStyle);
            $row++;
            $row++;
            $sheet->setCellValue('B'.$row,'INFORMACION DEL REPRESENTANTE LEGAL');
            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($outlineBorderTitleStyle);
            $row++;
            $trow=$row;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NOMBRE');
            $cell2->setValue($person->getFullName());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('TIPO DOCUMENTO');
            if($person->getDocumentType()=='CC'){
                $cell2->setValue('Cedula de Ciudadania');
            }elseif ($person->getDocumentType()=='CE') {
                $cell2->setValue('Cedula de Extranjeria');
            }elseif ($person->getDocumentType()=='TI'){
                $cell2->setValue('Tarjeta de Identidad');
            }
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NUMERO DE DOCUMENTO');
            $cell2->setValue($person->getDocument());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
            $cell2->setValue($person->getDocumentExpeditionDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE NACIMIENTO');
            $cell2->setValue($person->getBirthDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $sheet->getStyle('B'.$trow.':C'.$row)->applyFromArray($allBordersContentStyle);
            $sheet->getStyle('D'.$trow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
            $row++;
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell->setValue('ENTIDADES DEL EMPLEADOR');
            $sheet->mergeCells('B'.$row.':F'.$row);
            $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle);
            $row++;
            $sheet->getCell('B'.$row)->setValue('TIPO ENTIDAD');
            $sheet->getCell('C'.$row)->setValue('ESTADO');
            $sheet->getCell('D'.$row)->setValue('NOMBRE ENTIDAD');
            $sheet->getCell('E'.$row)->setValue('VERIFICACION');
            $sheet->getCell('F'.$row)->setValue('NOMBRE ENTIDAD');
            $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle2);
            $erow=$row;
            $row++;
            /** @var EmployerHasEntity $employerHasEntity */
            foreach ($person->getEmployer()->getEntities() as $employerHasEntity){
                if($employerHasEntity->getState()==0){
                    $erow++;
                    $sheet->getCell('B'.$erow)->setValue($employerHasEntity->getEntityEntity()->getEntityTypeEntityType());
                    $sheet->getCell('C'.$erow)->setValue('VALIDAR');
                    $sheet->getCell('D'.$erow)->setValue($employerHasEntity->getEntityEntity()->getName());
                }elseif($employerHasEntity->getState()==1){
                    $erow++;
                    $sheet->getCell('B'.$erow)->setValue($employerHasEntity->getEntityEntity()->getEntityTypeEntityType());
                    $sheet->getCell('C'.$erow)->setValue('INSCRIBIR');
                    $sheet->getCell('D'.$erow)->setValue($employerHasEntity->getEntityEntity()->getName());
                }
            }
            $sheet->getStyle('B'.$row.':C'.$erow)->applyFromArray($entitiesStyle3);
            $sheet->getStyle('D'.$row.':F'.$erow)->applyFromArray($allBordersNoContentStyle);
            $row=$erow;
            $row++;
            $row++;
            if($employee->getEmployee()){
                $sheet->setCellValue('B'.$row,'INFORMACION DEL EMPLEADO');
                $sheet->mergeCells('B'.$row.':D'.$row);
                $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($outlineBorderTitleStyle);
                $row++;
                $erow=$row;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('NOMBRE DEL EMPLEADO');
                $cell2->setValue($employee->getFullName());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('TIPO DE DOCUMENTO');
                if($employee->getDocumentType()=='CC'){
                    $cell2->setValue('Cedula de Ciudadania');
                }elseif ($employee->getDocumentType()=='CE') {
                    $cell2->setValue('Cedula de Extranjeria');
                }elseif ($employee->getDocumentType()=='TI'){
                    $cell2->setValue('Tarjeta de Identidad');
                }
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('NUMERO DE DOCUMENTO');
                $cell2->setValue($employee->getDocument());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
                $cell2->setValue($employee->getDocumentExpeditionDate()->format('d/m/y'));
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('FECHA DE NACIMIENTO');
                $cell2->setValue($employee->getBirthDate()->format('d/m/y'));
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('LUGAR DE NACIMIENTO');
                $cell2->setValue($employee->getBirthCity().';'.$employee->getBirthCountry());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('GENERO');
                $cell2->setValue($employee->getGender());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('DIRECCION DE RESIDENCIA EMPLEADO');
                $cell2->setValue($employee->getMainAddress());
                $sheet->mergeCells('B'.$row.':C'.$row);
                /** @var Phone $phone */
                foreach ($employee->getPhones() as $phone){
                    $row++;
                    $cell = $sheet->getCell('B'.$row);
                    $cell2 = $sheet->getCell('D'.$row);
                    $cell->setValue('TELEFONO/ CELULAR EMPLEADO');
                    $cell2->setValue($phone->getPhoneNumber());
                    $sheet->mergeCells('B'.$row.':C'.$row);
                }
                if($employee->getEmail()){
                    $row++;
                    $cell = $sheet->getCell('B'.$row);
                    $cell2 = $sheet->getCell('D'.$row);
                    $cell->setValue('EMAIL DEL EMPLEADO');
                    $cell2->setValue($employee->getEmail());
                    $sheet->mergeCells('B'.$row.':C'.$row);
                }
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('CIUDAD/MUNICIPIO EMPLEADO');
                $cell2->setValue($employee->getCity());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('DEPARTAMENTO EMPLEADO');
                $cell2->setValue($employee->getDepartment());
                $sheet->mergeCells('B'.$row.':C'.$row);
                $sheet->getStyle('B'.$erow.':C'.$row)->applyFromArray($allBordersContentStyle);
                $sheet->getStyle('D'.$erow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
                $row++;
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell->setValue('ENTIDADES DEL EMPLEADO');
                $sheet->mergeCells('B'.$row.':F'.$row);
                $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle);
                $row++;
                $sheet->getCell('B'.$row)->setValue('TIPO ENTIDAD');
                $sheet->getCell('C'.$row)->setValue('ESTADO');
                $sheet->getCell('D'.$row)->setValue('NOMBRE ENTIDAD');
                $sheet->getCell('E'.$row)->setValue('VERIFICACION');
                $sheet->getCell('F'.$row)->setValue('NOMBRE ENTIDAD');
                $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle2);
                $erow=$row;
                $row++;
                /** @var EmployerHasEntity $employeeHasEntity */
                foreach ($employee->getEmployee()->getEntities() as $employeeHasEntity){
                    if($employeeHasEntity->getState()==0){
                        $erow++;
                        $sheet->getCell('B'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType());
                        $sheet->getCell('C'.$erow)->setValue('VALIDAR');
                        $sheet->getCell('D'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getName());
                    }elseif($employeeHasEntity->getState()==1){
                        $erow++;
                        $sheet->getCell('B'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType());
                        $sheet->getCell('C'.$erow)->setValue('INSCRIBIR');
                        $sheet->getCell('D'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getName());
                    }
                }
                $sheet->getStyle('B'.$row.':C'.$erow)->applyFromArray($entitiesStyle3);
                $sheet->getStyle('D'.$row.':F'.$erow)->applyFromArray($allBordersNoContentStyle);
                $row=$erow;
                $row++;
                $row++;
                $sheet->setCellValue('B'.$row,'INFORMACION DEL CONTRATO');
                $sheet->mergeCells('B'.$row.':D'.$row);
                $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($contractTitleStyle);
                $row++;
                $trow=$row;
                /** @var EmployerHasEmployee $employerHasEmployee */
                foreach ($person->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                    if($employerHasEmployee->getEmployeeEmployee()==$employee->getEmployee()){
                        /** @var Contract $contract */
                        foreach ($employerHasEmployee->getContracts()as $contract){
                            if($contract->getState()==1){
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('DIRECCION DEL LUGAR DE TRABAJO');
                                $cell2->setValue($contract->getWorkplaceWorkplace()->getMainAddress());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('DEPARTAMENTO DEL LUGAR DE TRABAJO');
                                $cell2->setValue($contract->getWorkplaceWorkplace()->getDepartment());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('CIUDAD DEL LUGAR DE TRABAJO');
                                $cell2->setValue($contract->getWorkplaceWorkplace()->getCity());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('JORNADA LABORAL');
                                $cell2->setValue($contract->getContractTypeContractType()->getName());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('TIEMPO DE TRABAJO');
                                $cell2->setValue($contract->getTimeCommitmentTimeCommitment()->getName());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('DIAS QUE TRABAJA AL MES');
                                $cell2->setValue($contract->getWorkableDaysMonth());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('SALARIO DEL EMPLEADO');
                                $cell2->setValue($contract->getSalary());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('CARGO DEL EMPLEADO');
                                $cell2->setValue($contract->getPositionPosition()->getName());
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('FECHA INICIO DEL CONTRATO');
                                $cell2->setValue($contract->getStartDate()->format('d/m/y'));
                                $sheet->mergeCells('B'.$row.':C'.$row);
                                if($contract->getContractTypeContractType()->getPayrollCode()==2){
                                    $row++;
                                    $cell = $sheet->getCell('B'.$row);
                                    $cell2 = $sheet->getCell('D'.$row);
                                    $cell->setValue('FECHA FIN DEL CONTRATO');
                                    $cell2->setValue($contract->getEndDate()->format('d/m/y'));
                                    $sheet->mergeCells('B'.$row.':C'.$row);
                                }
                                $sheet->getStyle('B'.$trow.':C'.$row)->applyFromArray($contractStyle);
                                $sheet->getStyle('D'.$trow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
                            }
                        }
                    }
                }
            }
            // create the writer
            $objWriter = new PHPExcel_Writer_Excel2007($phpExcelObject);
            $objWriter->save('excel_file.xlsx');
            # create new zip opbject
            $zip = new ZipArchive();
            # create a temp file & open it
            $tmp_file =$action->getUserUser()->getPersonPerson()->getNames()."_".$action->getPersonPerson()->getNames()."_Documents.zip";
            if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
                # loop through each file
                for($i=0;$i<$count;$i++){
                    if($file[$i])
                        $zip->addFromString($docName[$i], $docUrl[$i]);
                    else
                        $zip->addFile($docUrl[$i],$docName[$i]);
                }
                die();
                $zip->addFile('excel_file.xlsx','Info_'.$employee->getFullName().'_'.date('d-m-y').'.xlsx');
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
                unlink('excel_file.xlsx');
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
        /** @var User $user */
        $user = $action->getUserUser();
        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        //setting some properties
        $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
            ->setLastModifiedBy("Symplifica-Bot")
            ->setTitle("General employerHasEmployee Info")
            ->setSubject("Details")
            ->setDescription("generated document with the employerHasEmployee information")
            ->setKeywords("employee employeer contract")
            ->setCategory("Information");
        //setting the active sheet and changing name
        $phpExcelObject->setActiveSheetIndex(0)->setTitle('Informacion');
        //getting the active sheet
        $sheet = $phpExcelObject->getActiveSheet();
        $outlineBorderTitleStyle= array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FFFFFFFF'),
                'bold' => true,
                'size' => 12,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF818181'),
            ),
            'alignment'=>array(
                'horizontal'=>'center',
                'vertical'=>'center',
            ),
        );
        $allBordersContentStyle = array(
            'borders'=>array(
                'allborders'=> array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FF000000'),
                'bold' => true,
                'size' => 11,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FFDBDBDB'),
            ),
            'alignment'=>array(
                'horizontal'=>'left',
                'vertical'=>'center',
            ),

        );
        $allBordersNoContentStyle = array(
            'borders'=>array(
                'allborders'=> array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FF000000'),
                'size' => 10,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FFFAFAFA'),
            ),
            'alignment'=>array(
                'horizontal'=>'left',
                'vertical'=>'center',
            ),

        );
        $entitiesStyle= array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FFFFFFFF'),
                'bold' => true,
                'size' => 12,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF121869'),
            ),
            'alignment'=>array(
                'horizontal'=>'center',
                'vertical'=>'center',
            ),
        );
        $entitiesStyle2= array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FFFFFFFF'),
                'bold' => true,
                'size' => 11,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF3F46A2'),
            ),
            'alignment'=>array(
                'horizontal'=>'center',
                'vertical'=>'center',
            ),
        );
        $entitiesStyle3= array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FF000000'),
                'bold' => true,
                'size' => 11,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF2BABCB'),
            ),
            'alignment'=>array(
                'horizontal'=>'center',
                'vertical'=>'center',
            ),
        );
        $contractTitleStyle= array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FFFFFFFF'),
                'bold' => true,
                'size' => 12,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF234C19'),
            ),
            'alignment'=>array(
                'horizontal'=>'center',
                'vertical'=>'center',
            ),
        );
        $contractStyle= array(
            'borders'=>array(
                'allborders'=> array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font'=>array(
                'name'=>'Calibri',
                'color' => array('argb'=>'FF000000'),
                'bold' => true,
                'size' => 12,
            ),
            'fill'=>array(
                'type'=>'solid',
                'color'=>array('argb'=>'FF91C184'),
            ),
            'alignment'=>array(
                'horizontal'=>'left',
                'vertical'=>'center',
            ),
        );

        //Employer Basic Info
        $sheet->setCellValue('B2','INFORMACION DEL EMPLEADOR');
        $row = 2;
        $sheet->mergeCells('B'.$row.':D'.$row);
        $sheet->getStyle('B2:D2')->applyFromArray($outlineBorderTitleStyle);
        $sheet->getColumnDimension('A')->setWidth(2);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(55);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(55);
        $sheet->getRowDimension(2)->setRowHeight(15);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('NOMBRE DEL EMPLEADOR');
        $cell2->setValue($person->getFullName());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('TIPO DE DOCUMENTO');
        if($person->getDocumentType()=='CC'){
            $cell2->setValue('Cedula de Ciudadania');
		}elseif ($person->getDocumentType()=='CE') {
            $cell2->setValue('Cedula de Extranjeria');
		}elseif ($person->getDocumentType()=='TI'){
            $cell2->setValue('Tarjeta de Identidad');
		}
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('NUMERO DE DOCUMENTO');
        $cell2->setValue($person->getDocument());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
        $cell2->setValue($person->getDocumentExpeditionDate()->format('d/m/y'));
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('FECHA DE NACIMIENTO');
        $cell2->setValue($person->getBirthDate()->format('d/m/y'));
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('DIRECCION DE RESIDENCIA EMPLEADOR');
        $cell2->setValue($person->getMainAddress());
        $sheet->mergeCells('B'.$row.':C'.$row);
        /** @var Phone $phone */
		foreach ($person->getPhones() as $phone){
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('TELEFONO/ CELULAR EMPLEADOR');
            $cell2->setValue($phone->getPhoneNumber());
            $sheet->mergeCells('B'.$row.':C'.$row);
		}
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('CIUDAD/MUNICIPIO EMPLEADOR');
        $cell2->setValue($person->getCity());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('DEPARTAMENTO EMPLEADOR');
        $cell2->setValue($person->getDepartment());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('EMAIL DEL EMPLEADO');
        $cell2->setValue($user->getEmail());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $sheet->getStyle('B3:C'.$row)->applyFromArray($allBordersContentStyle);
        $sheet->getStyle('D3:D'.$row)->applyFromArray($allBordersNoContentStyle);
        $row++;
        $row++;
        $sheet->setCellValue('B'.$row,'INFORMACION DEL REPRESENTANTE LEGAL');
        $sheet->mergeCells('B'.$row.':D'.$row);
        $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($outlineBorderTitleStyle);
        $row++;
        $trow=$row;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('NOMBRE');
        $cell2->setValue($person->getFullName());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('TIPO DOCUMENTO');
        if($person->getDocumentType()=='CC'){
            $cell2->setValue('Cedula de Ciudadania');
        }elseif ($person->getDocumentType()=='CE') {
            $cell2->setValue('Cedula de Extranjeria');
        }elseif ($person->getDocumentType()=='TI'){
            $cell2->setValue('Tarjeta de Identidad');
        }
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('NUMERO DE DOCUMENTO');
        $cell2->setValue($person->getDocument());
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
        $cell2->setValue($person->getDocumentExpeditionDate()->format('d/m/y'));
        $sheet->mergeCells('B'.$row.':C'.$row);
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell2 = $sheet->getCell('D'.$row);
        $cell->setValue('FECHA DE NACIMIENTO');
        $cell2->setValue($person->getBirthDate()->format('d/m/y'));
        $sheet->mergeCells('B'.$row.':C'.$row);
        $sheet->getStyle('B'.$trow.':C'.$row)->applyFromArray($allBordersContentStyle);
        $sheet->getStyle('D'.$trow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
        $row++;
        $row++;
        $cell = $sheet->getCell('B'.$row);
        $cell->setValue('ENTIDADES DEL EMPLEADOR');
        $sheet->mergeCells('B'.$row.':F'.$row);
        $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle);
        $row++;
        $sheet->getCell('B'.$row)->setValue('TIPO ENTIDAD');
        $sheet->getCell('C'.$row)->setValue('ESTADO');
        $sheet->getCell('D'.$row)->setValue('NOMBRE ENTIDAD');
        $sheet->getCell('E'.$row)->setValue('VERIFICACION');
        $sheet->getCell('F'.$row)->setValue('NOMBRE ENTIDAD');
        $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle2);
        $erow=$row;
        $row++;
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($person->getEmployer()->getEntities() as $employerHasEntity){
            if($employerHasEntity->getState()==0){
                $erow++;
                $sheet->getCell('B'.$erow)->setValue($employerHasEntity->getEntityEntity()->getEntityTypeEntityType());
                $sheet->getCell('C'.$erow)->setValue('VALIDAR');
                $sheet->getCell('D'.$erow)->setValue($employerHasEntity->getEntityEntity()->getName());
            }elseif($employerHasEntity->getState()==1){
                $erow++;
                $sheet->getCell('B'.$erow)->setValue($employerHasEntity->getEntityEntity()->getEntityTypeEntityType());
                $sheet->getCell('C'.$erow)->setValue('INSCRIBIR');
                $sheet->getCell('D'.$erow)->setValue($employerHasEntity->getEntityEntity()->getName());
            }
        }
        $sheet->getStyle('B'.$row.':C'.$erow)->applyFromArray($entitiesStyle3);
        $sheet->getStyle('D'.$row.':F'.$erow)->applyFromArray($allBordersNoContentStyle);
        $row=$erow;
        $row++;
        $row++;
        if($employee->getEmployee()){
            $sheet->setCellValue('B'.$row,'INFORMACION DEL EMPLEADO');
            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($outlineBorderTitleStyle);
            $row++;
            $erow=$row;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NOMBRE DEL EMPLEADO');
            $cell2->setValue($employee->getFullName());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('TIPO DE DOCUMENTO');
            if($employee->getDocumentType()=='CC'){
                $cell2->setValue('Cedula de Ciudadania');
            }elseif ($employee->getDocumentType()=='CE') {
                $cell2->setValue('Cedula de Extranjeria');
            }elseif ($employee->getDocumentType()=='TI'){
                $cell2->setValue('Tarjeta de Identidad');
            }
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('NUMERO DE DOCUMENTO');
            $cell2->setValue($employee->getDocument());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE EXPEDICION DEL DOCUMENTO');
            $cell2->setValue($employee->getDocumentExpeditionDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('FECHA DE NACIMIENTO');
            $cell2->setValue($employee->getBirthDate()->format('d/m/y'));
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('LUGAR DE NACIMIENTO');
            $cell2->setValue($employee->getBirthCity().';'.$employee->getBirthCountry());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('GENERO');
            $cell2->setValue($employee->getGender());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('DIRECCION DE RESIDENCIA EMPLEADO');
            $cell2->setValue($employee->getMainAddress());
            $sheet->mergeCells('B'.$row.':C'.$row);
            /** @var Phone $phone */
            foreach ($employee->getPhones() as $phone){
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('TELEFONO/ CELULAR EMPLEADO');
                $cell2->setValue($phone->getPhoneNumber());
                $sheet->mergeCells('B'.$row.':C'.$row);
            }
            if($employee->getEmail()){
                $row++;
                $cell = $sheet->getCell('B'.$row);
                $cell2 = $sheet->getCell('D'.$row);
                $cell->setValue('EMAIL DEL EMPLEADO');
                $cell2->setValue($employee->getEmail());
                $sheet->mergeCells('B'.$row.':C'.$row);
            }
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('CIUDAD/MUNICIPIO EMPLEADO');
            $cell2->setValue($employee->getCity());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell2 = $sheet->getCell('D'.$row);
            $cell->setValue('DEPARTAMENTO EMPLEADO');
            $cell2->setValue($employee->getDepartment());
            $sheet->mergeCells('B'.$row.':C'.$row);
            $sheet->getStyle('B'.$erow.':C'.$row)->applyFromArray($allBordersContentStyle);
            $sheet->getStyle('D'.$erow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
            $row++;
            $row++;
            $cell = $sheet->getCell('B'.$row);
            $cell->setValue('ENTIDADES DEL EMPLEADO');
            $sheet->mergeCells('B'.$row.':F'.$row);
            $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle);
            $row++;
            $sheet->getCell('B'.$row)->setValue('TIPO ENTIDAD');
            $sheet->getCell('C'.$row)->setValue('ESTADO');
            $sheet->getCell('D'.$row)->setValue('NOMBRE ENTIDAD');
            $sheet->getCell('E'.$row)->setValue('VERIFICACION');
            $sheet->getCell('F'.$row)->setValue('NOMBRE ENTIDAD');
            $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($entitiesStyle2);
            $erow=$row;
            $row++;
            /** @var EmployerHasEntity $employeeHasEntity */
            foreach ($employee->getEmployee()->getEntities() as $employeeHasEntity){
                if($employeeHasEntity->getState()==0){
                    $erow++;
                    $sheet->getCell('B'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType());
                    $sheet->getCell('C'.$erow)->setValue('VALIDAR');
                    $sheet->getCell('D'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getName());
                }elseif($employeeHasEntity->getState()==1){
                    $erow++;
                    $sheet->getCell('B'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getEntityTypeEntityType());
                    $sheet->getCell('C'.$erow)->setValue('INSCRIBIR');
                    $sheet->getCell('D'.$erow)->setValue($employeeHasEntity->getEntityEntity()->getName());
                }
            }
            $sheet->getStyle('B'.$row.':C'.$erow)->applyFromArray($entitiesStyle3);
            $sheet->getStyle('D'.$row.':F'.$erow)->applyFromArray($allBordersNoContentStyle);
            $row=$erow;
            $row++;
            $row++;
            $sheet->setCellValue('B'.$row,'INFORMACION DEL CONTRATO');
            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray($contractTitleStyle);
            $row++;
            $trow=$row;
            /** @var EmployerHasEmployee $employerHasEmployee */
            foreach ($person->getEmployer()->getEmployerHasEmployees() as $employerHasEmployee){
                if($employerHasEmployee->getEmployeeEmployee()==$employee->getEmployee()){
                    /** @var Contract $contract */
                    foreach ($employerHasEmployee->getContracts()as $contract){
                        if($contract->getState()==1){
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('DIRECCION DEL LUGAR DE TRABAJO');
                            $cell2->setValue($contract->getWorkplaceWorkplace()->getMainAddress());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('DEPARTAMENTO DEL LUGAR DE TRABAJO');
                            $cell2->setValue($contract->getWorkplaceWorkplace()->getDepartment());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('CIUDAD DEL LUGAR DE TRABAJO');
                            $cell2->setValue($contract->getWorkplaceWorkplace()->getCity());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('JORNADA LABORAL');
                            $cell2->setValue($contract->getContractTypeContractType()->getName());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('TIEMPO DE TRABAJO');
                            $cell2->setValue($contract->getTimeCommitmentTimeCommitment()->getName());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('DIAS QUE TRABAJA AL MES');
                            $cell2->setValue($contract->getWorkableDaysMonth());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('SALARIO DEL EMPLEADO');
                            $cell2->setValue($contract->getSalary());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('CARGO DEL EMPLEADO');
                            $cell2->setValue($contract->getPositionPosition()->getName());
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            $row++;
                            $cell = $sheet->getCell('B'.$row);
                            $cell2 = $sheet->getCell('D'.$row);
                            $cell->setValue('FECHA INICIO DEL CONTRATO');
                            $cell2->setValue($contract->getStartDate()->format('d/m/y'));
                            $sheet->mergeCells('B'.$row.':C'.$row);
                            if($contract->getContractTypeContractType()->getPayrollCode()==2){
                                $row++;
                                $cell = $sheet->getCell('B'.$row);
                                $cell2 = $sheet->getCell('D'.$row);
                                $cell->setValue('FECHA FIN DEL CONTRATO');
                                $cell2->setValue($contract->getEndDate()->format('d/m/y'));
                                $sheet->mergeCells('B'.$row.':C'.$row);
                            }
                            $sheet->getStyle('B'.$trow.':C'.$row)->applyFromArray($contractStyle);
                            $sheet->getStyle('D'.$trow.':D'.$row)->applyFromArray($allBordersNoContentStyle);
                        }
                    }
                }
            }
        }


        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Info_'.$employee->getFullName().'_'.date('d-m-y').'.xlsx'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
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
        fputcsv($handle, array('TIPO DE INSCRIPCIÓN', 'NOMBRE','E-MAIL','TELEFONO','FECHA DE INSCRIPCIÓN','TIPO'),';');
        /** @var LandingRegistration $landing */
        foreach ($landings as $landing){
            fputcsv($handle, array($landing->getEntityType(), $landing->getName()." ".$landing->getLastName(),$landing->getEmail(),$landing->getPhone(),$landing->getCreatedAt()->format('d/m/y'),$landing->getType()),';');
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

    public function exportBaseUserRegisterAction(){

        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $users = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->findAll();
        $tmp_file="baseUser.csv";
        $handle = fopen($tmp_file, 'w+');
        fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        fputcsv($handle, array('INFORMACIÓN DE USUARIOS QUE NO HAN TERMINADO REGISTRO'),';');
        fputcsv($handle, array('Nº', 'NOMBRE','DOCUMENTO','EMAIL','PASO ACTUAL','FECHA REGISTRO','FECHA CLIENTE'),';');
        $count = 1;
        /** @var User $landing */
        foreach ($users as $user){
            $state ='';
            if($user->getPersonPerson()->getEmployer()==null){$state='No ha empezado el paso 1';}
            elseif(count($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees()) == 0){$state = 'No ha empezado el paso 2';}
            elseif($user->getStatus()!=2){$state='No ha empezado el paso 3';}
            else{$state='Completado';}
            $date = '';
            if(count($user->getRealProcedure())>0)$date = $user->getRealProcedure()->first()->getCreatedAt()->format("d-m-Y");
            fputcsv($handle, array($count, $user->getPersonPerson()->getFullName(),$user->getPersonPerson()->getDocumentType().' '.$user->getPersonPerson()->getDocument(),$user->getEmail(),$state,$user->getDateCreated()->format("d-m-Y"),$date),';');
            $count++;
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

    /**
     * @param string $type
     * @param boolean $all
     * @return StreamedResponse
     */
    public function generateXLSAction($type,$all=false){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        switch ($type){
            case 'emailInfo':
                if($all){
                    $emailsInfo = $em->getRepository("RocketSellerTwoPickBundle:EmailInfo")->findAll();
                }
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
                //setting some properties
                $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
                    ->setLastModifiedBy("Symplifica-Bot")
                    ->setTitle("General Email Info Group")
                    ->setSubject("Details")
                    ->setDescription("generated document with the Email Group information")
                    ->setKeywords("email group document")
                    ->setCategory("Information");
                //setting the active sheet and changing name
                $phpExcelObject->setActiveSheetIndex(0)->setTitle('Información Correos');
                $outlineBorderTitleStyle= array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FFFFFFFF'),
                        'bold' => true,
                        'size' => 12,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FF818181'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'center',
                        'vertical'=>'center',
                    ),
                );
                $allBordersContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'bold' => true,
                        'size' => 11,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFDBDBDB'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                    ),
                );
                $allBordersNoContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'size' => 10,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFFFFFFF'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                    ),

                );
                $sheet = $phpExcelObject->getActiveSheet();
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(13);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(35);
                $sheet->getRowDimension(1)->setRowHeight(17);
                $sheet->getRowDimension(2)->setRowHeight(16);
                $row=1;
                /** @var \PHPExcel_Cell $cell */
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $cell->setValue('INFORMACIÓN CORREOS');
                $row++;
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $iniCol = $cell->getColumn();
                $cell->setValue('ID');
                $cell = $sheet->getCellByColumnAndRow(1,$row);
                $cell->setValue('GRUPO');
                $cell = $sheet->getCellByColumnAndRow(2,$row);
                $cell->setValue('NOMBRE');
                $cell = $sheet->getCellByColumnAndRow(3,$row);
                $cell->setValue('TIPO_DOC');
                $cell = $sheet->getCellByColumnAndRow(4,$row);
                $cell->setValue('DOCUMENTO');
                $cell = $sheet->getCellByColumnAndRow(5,$row);
                $cell->setValue('EMAIL');
                $sheet->mergeCells($iniCol.($row-1).':'.$cell->getColumn().($row-1));
                $sheet->getStyle($iniCol.($row-1).':'.$cell->getColumn().($row-1))->applyFromArray($outlineBorderTitleStyle);
                $sheet->getStyle($iniCol.$row.':'.$cell->getColumn().$row)->applyFromArray($allBordersContentStyle);
                $row++;
                $iniRow = $row;
                if($all==true) {
                    /** @var EmailInfo $emailInfo */
                    foreach ($emailsInfo as $emailInfo) {
                        $col = 0;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($emailInfo->getIdEmailInfo());
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($emailInfo->getEmailGroup()->getName());
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $name = $emailInfo->getName();
                        $docType = $emailInfo->getDocumentType();
                        $docNumber = $emailInfo->getDocument();
                        $email = $emailInfo->getEmail();
                        $cell->setValue($name);
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($docType);
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($docNumber);
                        $col++;
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($email);
                        $row++;
                    }
                    $sheet->getStyle($iniCol.$iniRow.':'.$cell->getColumn().($row-1))->applyFromArray($allBordersNoContentStyle);
                }
                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                if($all==true){
                    $dispositionHeader = $response->headers->makeDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        'All_Emails_Info_'.date('d-m-y').'.xlsx'
                    );
                }else{
                    $dispositionHeader = $response->headers->makeDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        'Emails_info_template_'.date('d-m-y').'.xlsx'
                    );
                }
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);
                return $response;
                break;
            case 'fullTimeEmployeesCalendars':
                /** @var QueryBuilder $query */
                $query = $em->createQueryBuilder();
                $query->add('select','con');
                $query->from("RocketSellerTwoPickBundle:Contract",'con')
                    ->join("con.employerHasEmployeeEmployerHasEmployee",'ehe')
                    ->join("ehe.employerEmployer",'er')
                    ->join("ehe.employeeEmployee",'ee')
                    ->join("ee.personPerson",'eep')
                    ->join('er.personPerson','erp')
                    ->join("con.timeCommitmentTimeCommitment",'tc')
                    ->join("RocketSellerTwoPickBundle:User",'u','WITH','u.personPerson=erp.idPerson')
                    ->join("erp.phones",'ph')
                    ->where($query->expr()->gte('ehe.state',4))
                    ->andWhere($query->expr()->eq('con.state',1))
                    ->andWhere($query->expr()->eq('tc.code','?1'))
                    ->setParameter(1,'TC')
                    ->orderBy("u.id",'ASC');
                $contracts = $query->getQuery()->getResult();
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
                //setting some properties
                $phpExcelObject->getProperties()->setCreator("Symplifica-Doc-Generator")
                    ->setLastModifiedBy("Symplifica-Bot")
                    ->setTitle("Full Time Calendar Contracts Info")
                    ->setSubject("Details")
                    ->setDescription("generated document with Full time contracts calendar info")
                    ->setKeywords("Full time calendar")
                    ->setCategory("Information");
                //setting the active sheet and changing name
                $phpExcelObject->setActiveSheetIndex(0)->setTitle('Información Calendarios TC');
                $outlineBorderTitleStyle= array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FFFFFFFF'),
                        'bold' => true,
                        'size' => 12,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FF818181'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'center',
                        'vertical'=>'center',
                    ),
                );
                $allBordersContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'bold' => true,
                        'size' => 11,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFDBDBDB'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                        'wrapText'=>true,
                    ),
                );
                $allBordersNoContentStyle = array(
                    'borders'=>array(
                        'allborders'=> array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                    'font'=>array(
                        'name'=>'Calibri',
                        'color' => array('argb'=>'FF000000'),
                        'size' => 10,
                    ),
                    'fill'=>array(
                        'type'=>'solid',
                        'color'=>array('argb'=>'FFFFFFFF'),
                    ),
                    'alignment'=>array(
                        'horizontal'=>'left',
                        'vertical'=>'center',
                    ),

                );
                $sheet = $phpExcelObject->getActiveSheet();
                $sheet->getColumnDimension('A')->setWidth(4.8);
                $sheet->getColumnDimension('B')->setWidth(27);
                $sheet->getColumnDimension('C')->setWidth(11);
                $sheet->getColumnDimension('D')->setWidth(9);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(28);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(8);
                $sheet->getRowDimension(1)->setRowHeight(17);
                $sheet->getRowDimension(2)->setRowHeight(36);
                $row=1;
                /** @var \PHPExcel_Cell $cell */
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $cell->setValue('INFORMACIÓN CALENDARIOS TIEMPO COMPLETO');
                $row++;
                $cell = $sheet->getCellByColumnAndRow(0,$row);
                $iniCol = $cell->getColumn();
                $cell->setValue('Nº');
                $cell = $sheet->getCellByColumnAndRow(1,$row);
                $cell->setValue('NOMBRE_EMPLEADOR');
                $cell = $sheet->getCellByColumnAndRow(2,$row);
                $cell->setValue('TELEFONO');
                $cell = $sheet->getCellByColumnAndRow(3,$row);
                $cell->setValue('TIPO_DOC');
                $cell = $sheet->getCellByColumnAndRow(4,$row);
                $cell->setValue('DOCUMENTO');
                $cell = $sheet->getCellByColumnAndRow(5,$row);
                $cell->setValue('NOMBRE_EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(6,$row);
                $cell->setValue('TIPO_DOC_EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(7,$row);
                $cell->setValue('DOCUMENTO_EMPLEADO');
                $cell = $sheet->getCellByColumnAndRow(8,$row);
                $cell->setValue('SABADO');
                $sheet->mergeCells($iniCol.($row-1).':'.$cell->getColumn().($row-1));
                $sheet->getStyle($iniCol.($row-1).':'.$cell->getColumn().($row-1))->applyFromArray($outlineBorderTitleStyle);
                $sheet->getStyle($iniCol.$row.':'.$cell->getColumn().$row)->applyFromArray($allBordersContentStyle);
                $sheet->getStyle("A2:I2")->getAlignment()->setWrapText(true);
                $row++;
                $iniRow = $row;
                $count = 1;
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    $person= $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    $ePerson = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                    /** @var Phone $phone */
                    $phone = $person->getPhones()->first();
                    $col = 0;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($count);
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($person->getFullName());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($phone->getPhoneNumber());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($person->getDocumentType());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($person->getDocument());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($ePerson->getFullName());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($ePerson->getDocumentType());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($ePerson->getDocument());
                    $col++;
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    if($contract->getWorksSaturday()==1){$cell->setValue("S");}else{$cell->setValue("N");}
                    $row++;
                    $count++;
                }
                $sheet->getStyle($iniCol.$iniRow.':'.$cell->getColumn().($row-1))->applyFromArray($allBordersNoContentStyle);
                // create the writer
                $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
                // create the response
                $response = $this->get('phpexcel')->createStreamedResponse($writer);
                // adding headers
                $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'Contract_calendars_'.date('d-m-y').'.xlsx'
                );
                $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'maxage=1');
                $response->headers->set('Content-Disposition', $dispositionHeader);
                return $response;
                break;
            default:
                break;
        }
    }
}
