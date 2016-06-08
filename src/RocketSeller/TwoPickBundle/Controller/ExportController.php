<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;


class ExportController extends Controller
{

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

			$personDocuments=$person->getDocs();
			$files = array();
			$files[0] = array();
			/** @var Document $document */
			foreach ($personDocuments as $document) {
				/**
				if($count>0){
					echo $person->getFullName()."<br>".$personDocuments->count();die;
				}**/
				$files[0][]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
			}
			$valid_files = array();
			$valid_files[0] = array();
			$valid_files[1] = array();
			//if files were passed in..
			if(is_array($files[0])) {
						//cycle through each file
				for($i=0;$i<count($files[0]);$i++){
					if(file_exists(getcwd().$files[0][$i])) {
						$valid_files[0][] = getcwd() . $files[0][$i];
					}
				}
				foreach($personDocuments as $document){
					$valid_files[1][] = $document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.pdf';
				}
			}

			# create new zip opbject
			$zip = new ZipArchive();

			# create a temp file & open it
			$tmp_file =$person->getNames()."_Documents.zip";
			if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
				# loop through each file
				for($i=0;$i<count($valid_files[0]);$i++){
					$zip->addFile($valid_files[0][$i],$i.". ".$valid_files[1][$i]);
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
				header('Content-Length: '.filesize($tmp_file));
				ob_clean();
				flush();

				readfile($tmp_file);
				ignore_user_abort(true);
				unlink($tmp_file);
			}
			return $this->redirectToRoute('ajax', array(), 301);
			//return $this->redirect("/backoffice/procedures", 301);
		}else{
			throw $this->createAccessDeniedException("No tiene suficientes permisos");
		}
    }

    /**
     * Funcion para exporar todos los documentos relacionados a la acción
     * @param $idAction
     */
    public function exportAllDocumentsAction($idAction)
    {
        if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {
            $action = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Action')
                ->find($idAction);

            //Employee Documents
            /** @var Person $person */
            $person = $action->getPersonPerson();
            $personDocuments=$person->getDocs();
            $files = array();
            $files[0] = array();
            /** @var Document $document */
            foreach ($personDocuments as $document) {
                $files[0][]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
            }
            $valid_files = array();
            $valid_files[0] = array();
            $valid_files[1] = array();
            //if files were passed in..
            if(is_array($files[0])) {
                for($i=0;$i<count($files[0]);$i++){
                    //If file exist create path
                    if(file_exists(getcwd().$files[0][$i])) {
                        $valid_files[0][] = getcwd() . $files[0][$i];
                    }
                }
                foreach($personDocuments as $document){
                    //asign document name for the zip file
                    $valid_files[1][] = $document->getDocumentTypeDocumentType()->getName().' '.$person->getFullName().'.pdf';
                }
            }
            if($person->getIdPerson()!= $action->getUserUser()->getPersonPerson()->getIdPerson()){
                //Employer Documents
                /** @var Person $user */
                $user = $action->getUserUser()->getPersonPerson();
                $userDocuments = $user->getDocs();
                $files1 = array();
                $files1[0] = array();
                /** @var Document $document */
                foreach ($userDocuments as $document) {
                    $files1[0][]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
                }
                $valid_files1 = array();
                $valid_files1[0] = array();
                $valid_files1[1] = array();
                //if files were passed in..
                if(is_array($files1[0])) {
                    for($i=0;$i<count($files1[0]);$i++){
                        //If file exist create path
                        if(file_exists(getcwd().$files1[0][$i])) {
                            $valid_files1[0][] = getcwd() . $files1[0][$i];
                        }
                    }
                    foreach($userDocuments as $document){
                        //asign document name for the zip file
                        $valid_files1[1][] = $document->getDocumentTypeDocumentType()->getName().' '.$user->getFullName().'.pdf';
                    }
                }
            }

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
            }elseif ($person->getDocByType()=='TI'){
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
            }elseif ($person->getDocByType()=='TI'){
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
                }elseif ($employee->getDocByType()=='TI'){
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
                fputcsv($handle, array('ENTIDADES',''),',');
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
                for($i=0;$i<count($valid_files[0]);$i++){
                    $zip->addFile($valid_files[0][$i],$i.". ".$valid_files[1][$i]);
                }
                # close zip
                if($user){
                    for($i=0;$i<count($valid_files1[0]);$i++){
                        $zip->addFile($valid_files1[0][$i],$i.". ".$valid_files1[1][$i]);
                    }
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
                header('Content-Length: '.filesize($tmp_file));
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
    
    public function exportDocumentsAction()
    {
		/** @var User $user */
		$user = $this->getUser();
		$userDocuments=$user->getPersonPerson()->getDocs();
		$files = array();
		$files[0] = array();
		$files[1] = array();
		/** @var Document $document */
		foreach ($userDocuments as $document) {
			$files[0][]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
			$files[1][]=$document->getMediaMedia()->getName();
		}
		$valid_files = array();
		$valid_files[0] = array();
		$valid_files[1] = array();

		//if files were passed in..
		if(is_array($files[0])) {
					//cycle through each file
			for($i=0;$i<count($files[0]);$i++){
				if(file_exists(getcwd().$files[0][$i])) {
					$valid_files[0][] = getcwd().$files[0][$i];
					$valid_files[1][] = $files[1][$i];

				}
			}
		}

		# create new zip opbject
		$zip = new ZipArchive();

		# create a temp file & open it
		$tmp_file =$user->getPersonPerson()->getNames()."_Documents.zip";
		if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
			# loop through each file
			for($i=0;$i<count($valid_files[0]);$i++){
				$zip->addFile($valid_files[0][$i],$valid_files[1][$i]);
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
			header('Content-Length: '.filesize($tmp_file));
			ob_clean();
			flush();

			readfile($tmp_file);
			ignore_user_abort(true);
			unlink($tmp_file);
		}
		return $this->redirectToRoute('ajax', array(), 301);
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
		header('Content-Length: '.filesize($tmp_file));
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
		}elseif ($person->getDocByType()=='TI'){
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
        }elseif ($person->getDocByType()=='TI'){
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
            }elseif ($employee->getDocByType()=='TI'){
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
                            if($contract->getEndDate()) {
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
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.filesize($tmp_file));

        ob_clean();
        ob_end_flush();
		flush();
		readfile($tmp_file);
		ignore_user_abort(true);
		unlink($tmp_file);
	}
}
