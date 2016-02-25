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
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="credit_card", nullable=false,  requirements="\d+", strict=true, description="CC Number")
     * @RequestParam(name="expiry_date_year", nullable=false,  requirements="[0-9]{4}", strict=true, description="YEAR in YYYY format.")
     * @RequestParam(name="expiry_date_month", nullable=false,  requirements="[0-9]{2}", strict=true, description="Month in MM format.")
     * @RequestParam(name="cvv", nullable=false,  requirements="\d+", strict=true, description="CVV CC.")
     * @RequestParam(name="cvv", nullable=false,  requirements="\d+", strict=true, description="CVV CC.")
     * @RequestParam(name="name_on_card", nullable=false, strict=true, description="The name on card")
     */
    public function postAddCreditCardAction(ParamFetcher $paramFetcher)
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
        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
        return $view;

    }
}
?>