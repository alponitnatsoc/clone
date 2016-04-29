<?php
namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;
use GuzzleHttp\Client;

use EightPoints\Bundle\GuzzleBundle;

  /**
  * Contains the web service to be exposed to novopayment, it will be called
  * by them when the dispersion process if finished.
  *
  */
class HighTechRestController extends FOSRestController
{
  /**
   * Verifies that the parameters to the web services, are in place and that
   * have the ccorrect format.
   * @param Array $parameters, Contains the parameters by the client.
   * @param Array $regex, contains the key as parameter and a regex.
   * @param Array $mandatory, contains a bool indicating if it is mandatory.
   */
   public function validateParamters($parameters, $regex, $mandatory)
   {

       foreach ($mandatory as $key => $value) {
           if (array_key_exists($key, $mandatory) &&
                   $mandatory[$key] &&
                   (!array_key_exists($key, $parameters)))
               throw new HttpException(422, "The parameter " . $key . " is empty");

           if (array_key_exists($key, $regex) &&
                   array_key_exists($key, $parameters) &&
                   !preg_match('/^' . $regex[$key] . '$/', $parameters[$key]))
               throw new HttpException(422, "The format of the parameter " .
               $key . " is invalid, it doesn't match" .
               $regex[$key]);

           if (!$mandatory[$key] && (!array_key_exists($key, $parameters)))
               $parameters[$key] = '';
       }
   }

  /**
   * @POST("notificacion/recaudo")
   * Get a notification of the collection of the money, to update in our system.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Get a notification of the collection of the money,
   *                  to update in our system.",
   *   statusCodes = {
   *     200 = "OK",
   *     201 = "Accepted",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="numeroRadicado", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
   * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
   *                                                   0 OK, 90 Fondos Insuficientes, 91 Cuenta Embargada, 92 No Autorizada, 93 Cuenta No Existe."
   *
   * @return View
   */
  public function postCollectionNotificationAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();

    // Set all the parameters info.
    $regex['numeroRadicado'] = '([0-9])+'; $mandatory['numeroRadicado'] = true;
    $regex['estado'] = '([0-9])+'; $mandatory['estado'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);
    // Validate that the id exists.
    $dispersion = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Pay");
    /** @var $ehEs EmployerHasEmployee */

    /*
    $dis = $dispersion->findOneBy(array('idDispercionNovo' => $id));
    if ($dis == null) {
      throw new HttpException(404, "The id: " . $id . " was not found.");
    }
    $status = $parameters['status'];
    $message = isset($parameters['message']) ? $parameters['message'] : null;

    $dis->setStatus($status);
    if($message)
      $dis->setMessage($message);
    $em = $this->getDoctrine()->getManager();
    $em->persist($dis);
    $em->flush();
    */
    // Succesfull operation.
    $view = View::create();
    $view->setStatusCode(200);
    $view->setData([]);
    return $view;

  }

  /**
   * @POST("notificacion/pago")
   * Get a notification of the payment, to update in our system.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Get a notification of the payment, to update in our system.",
   *   statusCodes = {
   *     200 = "OK",
   *     201 = "Accepted",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="numeroRadicado", nullable=false, requirements="([0-9])+", strict=true, description="the id of operation returned by HT in web service #8.")
   * (name="estado", nullable=false, requirements="([0-9])+", strict=true, description="Status of the operation, where:
   *                                                   0 OK, 90 Fondos Insuficientes, 91 Cuenta Embargada, 92 No Autorizada, 93 Cuenta No Existe."
   *
   * @return View
   */
  public function postPaymentNotificationAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();

    // Set all the parameters info.
    $regex['numeroRadicado'] = '([0-9])+'; $mandatory['numeroRadicado'] = true;
    $regex['estado'] = '([0-9])+'; $mandatory['estado'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);
    $id = $parameters['numeroRadicado'];
    // Validate that the id exists.
    $dispersion = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
    /** @var $ehEs EmployerHasEmployee */

    $dis = $dispersion->findOneBy(array('radicatedNumber' => $id));
    if ($dis == null) {
      throw new HttpException(404, "The id: " . $id . " was not found.");
    }

    $retorno = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRestController:getDispersePurchaseOrder', ['idPurchaseOrder' => $dis->getIdPurchaseOrder()]);

    return $retorno;
  }

}
