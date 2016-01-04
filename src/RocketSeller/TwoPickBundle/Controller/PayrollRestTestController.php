<?php
namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use DateTime;

use EightPoints\Bundle\GuzzleBundle;

/**
 * This class is only used to test the REST request services to the payment
 * interface.
 */
class PayrollRestTestController extends FOSRestController
{
  /**
   * It sets the headers for the payments request.
   * @return Array with the header options.
   */
  private function setHeaders() {
    $header = array();
    $header['x-channel'] = 'WEB';
    $header['x-country'] = 'CO' ;
    $header['language'] = 'es' ;
    $header['content-type'] = 'application/json';
    $header['accept'] = 'application/json';
    return $header;
  }


  /**
   * @Post("mock/sql/{interfaz}")
   * Mocks the insert client request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request.
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the insert client request. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @return View
   */
  public function getMocsksCustomerAction($interfaz)
  {

    $view = View::create();

      $view->setStatusCode(201);
      $view->setData("<Interfaz7Solic>
                <UNICO>
                  <TIPOCON>1</TIPOCON>
                  <EMP_CODIGO>' . 1020345201 . '</EMP_CODIGO>
                  <CON_CODIGO>' . 45 . '</CON_CODIGO>
                  <VALOR>' . '' . '</VALOR>
                  <UNIDADES>' . 19 . '</UNIDADES>
                  <FECHA>' . '2015-01-01' . '</FECHA>
                  <PROD_CODIGO>' . 1 . '</PROD_CODIGO>
                  <NOV_CONSEC>' . 57190 . '</NOV_CONSEC>
                </UNICO>
              </Interfaz7Solic>");
      $view->setFormat('xml');

    return $view;
  }
}
