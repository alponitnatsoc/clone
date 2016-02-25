<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use DateTime;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PaymentMethodRestController extends Controller
{
    /**
     * Add the credit card<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict"
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
        $view = View::create();
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
        if($insertionAnswer->getStatusCode()!=201){
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
            return $view;
        }
        $chargeValue=200;
        $idPayM=json_decode($insertionAnswer->getContent())["method-id"];
        $purchaseOrder=new PurchaseOrders();
        $purchaseOrder->setValue($chargeValue);
        $purchaseOrder->setName("Cargo prueba CC");
        $em=$this->getDoctrine()->getEntityManager();
        $em->persist($purchaseOrder);
        $em->flush();
        $purchaseOrderId=$purchaseOrder->getIdPurchaseOrders();
        $em->remove($purchaseOrder);
        $em->flush();
        $request->setMethod("POST");
        $request->request->add(array(
            "documentNumber"=>$person->getDocument(),
            "MethodId"=>$idPayM,
            "totalAmount"=>$chargeValue,
            "taxAmount"=>0,
            "taxBase"=>0,
            "commissionAmount"=>0,
            "commissionBase"=>0,
            "chargeMode"=>1,
            "chargeId"=>$purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('_format' => 'json'));
        if(!($insertionAnswer->getStatusCode()==200&&($insertionAnswer->getContent()["charge-rc"]=="00"||$insertionAnswer->getContent()["charge-rc"]=="08"))){
            $this->getDeletePayMethodAction($idPayM,$person->getDocument());
            $view->setStatusCode(400)->setData(array('error'=>array("Credit Card"=>"No se pudo agregar el medio de Pago")));
            return $view;
        }
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber"=>$person->getDocument(),
            "chargeId"=>$purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteReversePaymentMethod', array('_format' => 'json'));
        if($insertionAnswer->getStatusCode()!=200){
            $view->setStatusCode(200)->setData(array('error'=>array("Credit Card"=>"Se agrego la taerjeta de Credito, pero no se pudo reversar el cobro")));
        }else{
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
        }
        return $view;

    }
    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getDeletePayMethodAction($idPayM,$idUser)
    {
        /** @var User $user */
        $user=$this->getUser();
        if($user->getPersonPerson()->getDocument()!=$idUser){
            $view = View::create();
            $view->setStatusCode(403);
            return $view;
        }
        $request = $this->container->get('request');
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber"=>$idUser,
            "paymentMethodId"=>$idPayM,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteClientPaymentMethod', array('_format' => 'json'));
        $view = View::create();
        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());

        return $view;
    }
}
?>