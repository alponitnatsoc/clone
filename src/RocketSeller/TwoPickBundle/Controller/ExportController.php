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
}
