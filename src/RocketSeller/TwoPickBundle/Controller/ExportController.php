<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;


class ExportController extends Controller
{
  
	public function exportDocumentsByPersonAction($idPerson)
    {
    	if($this->isGranted('EXPORT_DOCUMENTS_PERSON', $this->getUser())) {

			$person = $this->getdoctrine()
			->getRepository('RocketSellerTwoPickBundle:Person')
			->find($idPerson);

			$personDocuments=$person->getDocs();
			$files = array();
			$files[0] = array();
			$files[1] = array();
			/** @var Document $document */
			$count = 0;
			foreach ($personDocuments as $document) {
				/**
				if($count>0){
					echo $person->getFullName()."<br>".$personDocuments->count();die;
				}**/
				$files[0][]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');
				$files[1][]= $document->getMediaMedia()->getName();
				$count++;
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
						$valid_files[1][] = $files[1][$i];
					}
				}
			}

			# create new zip opbject
			$zip = new ZipArchive();

			# create a temp file & open it
			$tmp_file =$person->getNames()."_Documents.zip";
			if ($zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )=== TRUE) {
				# loop through each file
				for($i=0;$i<count($valid_files[0]);$i++){
					$zip->addFile($valid_files[0][$i],$valid_files[1][$i].$i);
                    echo "el archivo es: ".$valid_files[1][$i]." y el path es: ".$valid_files[0][$i];
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
	public function generateCsvByPersonAction($idPerson){
		/** @var User $user */
		//$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')
                ->find($idPerson);

		// $tmp_file=$person->getNames()."_fields.csv";
		$tmp_file=$this->container->getParameter('kernel.cache_dir') .$person->getNames()."_fields.csv";
		$handle = fopen($tmp_file, 'w+');


		// Add the header of the CSV file
		fputcsv($handle, array('sep=;'));
		fputcsv($handle, array('Campo', 'Dato'),';');
		fputcsv($handle, array('Persona', 'Empleador'),';');
		//first the user info
		/** @var User $user */
		
		$em2 = $this->getDoctrine()->getEntityManager();
		$connection = $em2->getConnection();
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
}
