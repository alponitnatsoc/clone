<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use RocketSeller\TwoPickBundle\pdf\cafeSalud;
use RocketSeller\TwoPickBundle\pdf\ConcatPdf;
use Symfony\Component\HttpFoundation\Request;
use ZipArchive;


class ExportController extends Controller
{
	public function testAction(){

		/** @var User $user */
		$user = $this->getUser();
		$userDocuments=$user->getPersonPerson()->getDocs();
		$files = array();
		/** @var Document $document */
		foreach ($userDocuments as $document) {
			$files[]= $this->container->get('sonata.media.twig.extension')->path($document->getMediaMedia(), 'reference');;
		}
		$valid_files = array();



		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists(getcwd().str_replace('/', '\\', $file))) {
					$valid_files[] = getcwd().str_replace('/', '\\', $file);
				}
			}
		}
		# create new zip opbject
		$zip = new ZipArchive();

		# create a temp file & open it
		$tmp_file ="my-archive.zip";
		$zip->open($tmp_file,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );

		# loop through each file
		foreach($valid_files as $file) {
			echo $file;
			$zip->addFile($file,"test.jpg");
		}
		# close zip
		$zip->close();
		# send the file to the browser as a download
		header('Content-disposition: attachment; filename=my-archive.zip');
		header('Content-type: application/zip');

		readfile($tmp_file);
		return $this->redirectToRoute('ajax', array(), 301);

	}
    public function exportDocumentsAction()
    {
		/** @var User $user */
		$user = $this->getUser();
		$userDocuments=$user->getPersonPerson()->getDocs();
		$files = array();
		$serverPath="/web/uploads/media/";
		/** @var Document $document */
		foreach ($userDocuments as $document) {
			$files[]=$serverPath.$document->getMediaMedia()->getName();
		}
		# create new zip opbject
		$zip = new ZipArchive();

		# create a temp file & open it
		$tmp_file = tempnam('.','');
		$zip->open($tmp_file, ZipArchive::CREATE);

		# loop through each file
		foreach($files as $file){

			# download file
			$download_file = file_get_contents($file);

			#add it to the zip
			$zip->addFromString(basename($file),$download_file);

		}

		# close zip
		$zip->close();
		# send the file to the browser as a download
		header('Content-disposition: attachment; filename=Resumes.zip');
		header('Content-type: application/zip');
		readfile($tmp_file);
		return $this->redirectToRoute('ajax', array(), 301);
        
    }
}
