<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use \Application\Sonata\MediaBundle\Entity\Media;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Form\DocumentRegistration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentRestSecuredController extends FOSRestController
{

  /**
   * download document <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "get download document",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param String $ref type of document.
   * @param String $id id payroll.
   * @param String $type html | pdf.
   *
   * @return View
   */
  public function getDownloadDocumentAction($ref,$id, $type = "html")
  {
      $params = array(
        'ref' => $ref,
        'id' => $id,
        'type' => $type,
        'attach'=> null
      );

      $result = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
      $view = View::create();
      $view->setStatusCode(200);

      return $result;
   }
 }
