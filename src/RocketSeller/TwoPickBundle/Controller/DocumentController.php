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

class DocumentController extends Controller
{
	public function addDocumentAction(Request $request){
		$em = $this->getDoctrine()->getManager();

		/** @var User $user */
		$user=$this->getUser();
		/** @var Person $person */
		$person=$user->getPersonPerson();
		$document=new Document();
		$document->setPersonPerson($person);

		$form = $this->createForm(new DocumentRegistration(),$document);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$medias=$document->getMediaMedium();
			/** @var Media $media */
			foreach ($medias as $media) {
				print_r($media);
				die();
				$media->setBinaryContent($media);

				$media->setProviderName("sonata.media.provider.image");
				$media->setName($document->getName());
				$media->setProviderStatus(Media::STATUS_OK);
				$media->setProviderReference($media->getBinaryContent());

				$em->persist($media);
			}
			$em = $this->getDoctrine()->getManager();
			$em->persist($document);
			$em->flush();

			return $this->redirectToRoute('show_dashboard');
		}
		return $this->render(
			'RocketSellerTwoPickBundle:Document:addDocumentForm.html.twig',
				array('form' => $form->createView()));
	}
}
