<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Controller\NoveltyController;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NoveltyRestSecuredController extends FOSRestController
{
   /**
    * update worked days<br/>
    *
    * @ApiDoc(
    *   resource = true,
    *   description = "update worked days",
    *   statusCodes = {
    *     200 = "Returned when successful"
    *   }
    * )
    *
    * @param paramFetcher $paramFetcher ParamFetcher
    *
    * @RequestParam(name="empId", nullable=false, strict=true, description="employee id")
    * @RequestParam(name="idNovelty", nullable=false, strict=true, description="novelty id")
    * @RequestParam(name="daysAmount", nullable=false, strict=true, description="daysAmount")
    * @RequestParam(name="idPerson", nullable=false, strict=true, description="id person of logged user")
    *
    * @return View
    */
    public function postUpdateWorkedDaysAction(ParamFetcher $paramFetcher) {

      $empId = $paramFetcher->get('empId');
      $idNovelty = $paramFetcher->get('idNovelty');
      $daysAmount = $paramFetcher->get('daysAmount');
      $idPerson = $paramFetcher->get('idPerson');
      $params = array(
          'empId' => $empId,
          'idNovelty' => $idNovelty,
          'daysAmount' => $daysAmount,
          'idPerson' => $idPerson
      );
      $result = $this->forward('RocketSellerTwoPickBundle:Novelty:updateWorkedDays', $params);
      // updateWorkedDaysAction($empId, $idNovelty, $daysAmount);

      $view = View::create();

      return $view->setStatusCode(200)->setData(array('result'=>$result));
    }

   /**
    * registers a new novelty<br/>
    *
    * @ApiDoc(
    *   resource = true,
    *   description = "registers a new novelty",
    *   statusCodes = {
    *     200 = "Returned when successful"
    *   }
    * )
    *
    * @param paramFetcher $paramFetcher ParamFetcher
    *
    * @RequestParam(array=true, name="noveltyFields", nullable=false, strict=true, description="hash name of document pages")
    * @RequestParam(name="idNoveltyType", nullable=false, strict=true, description="novelty type")
    * @RequestParam(name="idContract", nullable=false, strict=true, description="id contract")
    *
    * @return View
    */
    public function postRegisterNoveltyAction(ParamFetcher $paramFetcher) {

      $noveltyFields = $paramFetcher->get('noveltyFields');
      $idNoveltyType = $paramFetcher->get('idNoveltyType');
      $idContract = $paramFetcher->get('idContract');
      // $idPod = $paramFetcher->get('idPod');
      // $idPayroll = $paramFetcher->get('idPayroll');
      $em = $this->getDoctrine()->getManager();
      $contractRepo = $em->getRepository("RocketSellerTwoPickBundle:Contract");

      $contract = $contractRepo->find($idContract);
      $idPayroll = $contract->getActivePayroll()->getIdPayroll();
      //esperar a daniel

      $params = array(
          'idPayroll' => $idPayroll,
          'idNoveltyType' => $idNoveltyType,
          'noveltyFields' => $noveltyFields,
      );

      // $novletyService = $this->get('app.novelty_service');
      $result = "hola";
      // $result = $novletyService->validateAndPersistNovelty($novelty, $payroll, $noveltyType);
      // $this->validateAndPersistNovelty($novelty, $payroll, $noveltyType);

      $result = $this->forward('RocketSellerTwoPickBundle:Novelty:validateAndPersistNovelty', $params);
      // $podRepo = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
      // $pod =  $podRepo->find($idPod);

      $view = View::create();
      return $view->setStatusCode(200)->setData(array('result' => $result));
    }
  }
