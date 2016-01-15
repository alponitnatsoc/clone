<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Person;
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

class DocumentController extends Controller
{
	public function showDocumentsAction($id){		
		$person = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Person')
		->find($id);		
		$documents = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Document')
		->findByPersonPerson($person);
		return $this->render(
			'RocketSellerTwoPickBundle:Employee:documents.html.twig',
			array(
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
	    $path = $this->get('kernel')->getRootDir(). "/../web/public/";
	    $content = file_get_contents($path.$filename);

	    $response = new Response();

	    //set headers
	    $response->headers->set('Content-Type', 'mime/type');
	    $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename);

	    $response->setContent($content);
	    return $response;
	}
	public function addDocumentAction($id,Request $request){
		$em = $this->getDoctrine()->getManager();
		$person = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Person')
		->find($id);
		$document = new Document();
		$document->setPersonPerson($person);
		$document->setName('nombre');
		$form = $this->createForm(new DocumentRegistration(),$document);

		$form->handleRequest($request);

		if ($form->isValid()) {
			$medias=$document->getMediaMedia();
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
			'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig',
				array('form' => $form->createView()));
	}

	public function addDocAction($id,$idDocumentType,Request $request){
		$em = $this->getDoctrine()->getManager();
		$person = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Person')
		->find($id);
		$documentType = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:DocumentType')
		->find($idDocumentType);
		$document=new Document();
		$document->setPersonPerson($person);
		$document->setStatus(1);
		$document->setName('nombre');
		$document->setDocumentTypeDocumentType($documentType);

		$form = $this->createForm(new DocumentRegistration(),$document);

		$form->handleRequest($request);

		if ($form->isValid()) {
			$medias=$document->getMediaMedia();
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

			return $this->redirect('/pages?redirector=/matrix/choose');
			//$this->redirect($request->server->get('HTTP_REFERER'));
		}
		return $this->render(
			'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig',
				array('form' => $form->createView()));
	}
	public function editDocumentAction($id,$idDocument,Request $request)
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
		$document=new Document();
		$document->setPersonPerson($person);
		$document->setStatus(1);
		$document->setDocumentTypeDocumentType($documentType);
		$form = $this->createForm(new DocumentRegistration(),$document);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$medias=$document->getMediaMedia();
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
			'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig',
				array('form' => $form->createView()));
		return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
	}
	public function downloadDocAction($idDocument)
	{
		$em = $this->getDoctrine()->getManager();
		$document = $this->getDoctrine()
		->getRepository('RocketSellerTwoPickBundle:Document')
		->find($idDocument);
		return $this->redirect('/media/download/'.$document->getMediaMedia()->getId());
	}
}
