<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use DateTime;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PaymentMethodRestController extends Controller
{
    /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     404 = "Returned when the Novelty Type id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @param int $idNotification
     * @return View
     * @RequestParam(name="credit_card", nullable=false,  requirements="\d+", strict=true, description="the novelty type id.")
     * @RequestParam(name="expiry_date_year", nullable=false,  requirements="\d+", strict=true, description="the novelty type id.")
     * @RequestParam(name="expiry_date_month", nullable=false,  requirements="\d+", strict=true, description="the novelty type id.")
     * @RequestParam(name="cvv", nullable=false,  requirements="\d+", strict=true, description="the novelty type id.")
     * @RequestParam(name="name_on_card", nullable=false,  requirements="\d+", strict=true, description="the novelty type id.")
     */
    public function postAddCreditCardAction(ParamFetcher $paramFetcher, $idNotification=-1)
    {
        /** @var User $user */
        $user=$this->getUser();
        $person=$user->getPersonPerson();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType"=>$person->getDocumentType(),
            "documentNumber"=>$person->getDocument(),
            "accountNumber"=>$paramFetcher->get("credit_card"),
            "expirationYear"=>$paramFetcher->get("expiry_date_year"),
            "expirationMonth"=>$paramFetcher->get("expiry_date_month"),
            "codeCheck"=>$paramFetcher->get("cvv"),
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClientPaymentMethod', array('_format' => 'json'));
        echo "Status Code Employee PayMethod: ".$person->getNames()." -> ".$insertionAnswer->getStatusCode()." content".$insertionAnswer->getContent() ;
        if($idNotification!=-1&&$insertionAnswer->getStatusCode()==201){
            $notificationRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification");
            /** @var Notification $realNotif */
            $realNotif=$notificationRepo->find($idNotification);
            if($realNotif!=null){
                $realNotif->setStatus(-1);
                $realNotif->setSawDate(new DateTime());
                $realNotif->setRelatedLink(null);
                $em=$this->getDoctrine()->getManager();
                $em->persist($realNotif);
                $em->flush();
            }
        }
        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
        return $view;

    }
}
?>