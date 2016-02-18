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
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;
use GuzzleHttp\Client;

use EightPoints\Bundle\GuzzleBundle;

  /**
  * Contains the web service to be exposed to novopayment, it will be called
  * by them when the dispersion process if finished.
  *
  */
class NovoPaymentRestController extends FOSRestController
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
               throw new HttpException(400, "The parameter " . $key . " is empty");

           if (array_key_exists($key, $regex) &&
                   array_key_exists($key, $parameters) &&
                   !preg_match('/^' . $regex[$key] . '$/', $parameters[$key]))
               throw new HttpException(400, "The format of the parameter " .
               $key . " is invalid, it doesn't match" .
               $regex[$key]);

           if (!$mandatory[$key] && (!array_key_exists($key, $parameters)))
               $parameters[$key] = '';
       }
   }
  /**
   * @PUT("dispersion/{id}")
   * Dispersion of beneficiary payment by client.(5.6)<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Dispersion of beneficiary payment by client.",
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
   * (name="id", nullable=false, requirements="([0-9])+", strict=true, description="the id of the transfer.")
   *
   * (name="status", nullable=false, requirements="(1|2)", strict=true, description="the status of the operation. Succesfull: 1, unsuccesfull: 2.")
   * (name="message", nullable=true, requirements="(.)*", strict=true, description="Optional message indicating the failure.")
   *
   * @return View
   */
  public function putApprovalDispersionAction(Request $request, $id)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();

    // Set all the parameters info.
    $regex['status'] = '(1|2)'; $mandatory['status'] = true;
    $regex['message'] = '(.)*'; $mandatory['message'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);
    // Validate that the id exists.
    $dispersion = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Pay");
    /** @var $ehEs EmployerHasEmployee */
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

    // Succesfull operation.
    $view = View::create();
    $view->setStatusCode(200);
    $view->setData([]);
    return $view;

  }
}
