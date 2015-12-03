<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\Bundle\DemoBundle\Model\MediaPreview;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Application\Sonata\MediaBundle\Entity\Media;
use Application\Sonata\MediaBundle\Entity\Gallery;
use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;

class DocumentsController extends Controller
{
	public function showDocumentsAction($id){
		$employee = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Employee')
		->find($id);
		$galleryHasMedia = $this->getGalleryHasMedia($employee->getPersonPerson()->getGallery());

		return $this->render(
			'RocketSellerTwoPickBundle:Employee:documents.html.twig',
			array(
				'employee' => $employee,
				'galleryHasMedia' => $galleryHasMedia
				));
	}
	public function mediaAction($id,Request $request) {
		if ($request->getMethod() == 'POST') {            
			$files = $this->get('request')->files;                        
			$employee = $this->loadClassById($id,"Employee");
			$documentType = $this->loadClassById($this->get('request')->request->get('documents'),"DocumentType");                       
			$status = 'success';
			$uploadedURL='';
			$message='';
			$em = $this->getDoctrine()->getManager();
			if(!sizeof($files->get('files'))==0){
				$gallery = $employee->getPersonPerson()->getGallery();
				if(!$gallery){				
					$gallery = new Gallery();
					$gallery->setName('Documentos');
					$gallery->setContext('person');
					$gallery->setDefaultFormat('person_preview');
					$gallery->setEnabled(0);
					$gallery->setPerson($employee->getPersonPerson());
					$em->persist($gallery);

				}
				$galleryHasMedia = new GalleryHasMedia();
				$galleryHasMedia->setGallery($gallery);
				$em->persist($galleryHasMedia);	
				$mediaManager = $this->container->get('sonata.media.manager.media');
				$em->flush();
			}
			
			foreach ($files->get('files') as $file) {
				if (($file instanceof UploadedFile) && ($file->getError() == '0')) {
					if (($file->getSize() < 200000000)) {																	
						$media = new Media();
						$media->setBinaryContent($file);
						$media->setDocumentType($documentType);
						$media->setContext('person');						 
						$ImagemimeTypes = array('image/jpeg', 'image/png');
						$FilemimeTypes = array('application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							'application/msword', 'application/pdf', 'application/x-pdf');
						if (in_array($file->getMimeType(), $FilemimeTypes)) {
							$media->setProviderName('sonata.media.provider.file');
						}
						if (in_array($file->getMimeType(), $ImagemimeTypes)) {
							$media->setProviderName('sonata.media.provider.image');
						}
						$mediaManager->save($media);
						$galleryHasMedia->setMedia($media);
						$em->persist($galleryHasMedia);	
					} else {
						$status = 'failed';
						$message = 'Size exceeds limit';
						echo $message . $status;

					}
				} else {
					$status = 'failed';
					$message = 'invalid file type';
					echo $message. $status;

				}
			}
			$employee->getPersonPerson()->setGallery($gallery);
			$em->flush();
			return $this->render('RocketSellerTwoPickBundle:Employee:documents.html.twig',array('employee'=>$employee,
				'galleryHasMedia' => $galleryHasMedia,

				));
		} else {
			$employerHasEmployees = $this->getEmployees();
			$documentTypes = $this->getDocumentTypes();
			return $this->render('RocketSellerTwoPickBundle:Employee:addDocuments.html.twig',array('employerHasEmployees'=>$employerHasEmployees, 'documentTypes'=>$documentTypes, 'employee_id' => $id));
		}

	}
	public function getDocumentTypes(){
		$documents = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:DocumentType')
		->findAll();
		return $documents;
	}
	public function getGalleryHasMedia($gallery){
		$galleryHasMedia = $this->getdoctrine()
		->getRepository('ApplicationSonataMediaBundle:GalleryHasMedia')
		->findByGallery($gallery);
		return $galleryHasMedia;
	}
	public function getEmployees(){
		$persons = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findBy(
			array('employerEmployer' => $this->getUser()->getPersonPerson()->getEmployer())    		
			);
		return $persons;
	}
	public function loadClassById($parameter, $entity)
	{
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->find($parameter);
		return $loadedClass;
	}   
	public function loadClassByArray($array, $entity)
	{
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->findOneBy($array);
		return $loadedClass;
	}
}
