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

    public function mediaAction(Request $request) {
            if ($request->getMethod() == 'POST') {            
            $files = $this->get('request')->files;
            $status = 'success';
            $uploadedURL='';
            $message='';
            $em = $this->getDoctrine()->getManager();
            if(!sizeof($files->get('files'))==0){				
				$gallery = new Gallery();
				$gallery->setName('Documentos prue');
				$gallery->setContext('person');
				$gallery->setDefaultFormat('person_preview');
				$gallery->setEnabled(0);
				$em->persist($gallery);
				$em->flush();
            }
            foreach ($files->get('files') as $file) {
            	if (($file instanceof UploadedFile) && ($file->getError() == '0')) {
	                if (($file->getSize() < 20000000000)) {												
						$galleryHasMedia = new GalleryHasMedia();
						$galleryHasMedia->setGallery($gallery);
						$em->persist($galleryHasMedia);	
						$mediaManager = $this->container->get('sonata.media.manager.media');					
						$media = new Media();
						$media->setBinaryContent($file);
						$media->setContext('default'); 
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
            $em->flush();
            return $this->render('RocketSellerTwoPickBundle:Employee:documents.html.twig');
        } else {
            return $this->render('RocketSellerTwoPickBundle:Employee:documents.html.twig');
        }

    }    
}
