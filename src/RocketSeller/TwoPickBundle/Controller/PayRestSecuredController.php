<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;

class PayRestSecuredController extends FOSRestController
{

  /**
   * get pods currenly in process <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "get pods currenly in process",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   *
   * @return View
   */
  public function getListPodsAction()
  {
    /** @var User $user */
    $user = $this->getUser();
    $purchaseOrders = $user->getPurchaseOrders();
    $answer= new ArrayCollection();
    /** @var PurchaseOrders $purchaseOrder */
    foreach ($purchaseOrders as $purchaseOrder) {
        $pods = $purchaseOrder->getPurchaseOrderDescriptions();
        /** @var PurchaseOrdersDescription $pod */
        foreach ($pods as $pod) {
            if($pod->getPurchaseOrdersStatus() == null) {
                continue;
            }
            if( $pod->getPurchaseOrdersStatus()->getIdNovoPay()=="00" ||
                $pod->getPurchaseOrdersStatus()->getIdNovoPay()=="-2" ) {
                $answer->add($pod);
            }
        }
    }

    $view = View::create();
    $view->setStatusCode(200);

    $context = new SerializationContext();
    $context->setSerializeNull(true);
    $serializer = $this->get('jms_serializer');
    $encodedAnswer = $serializer->serialize(array(
        'pods' => $answer), 'json', $context);
    return $view->setData($encodedAnswer);
  }

}
