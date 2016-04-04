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
 * Contains the methods to generate the plain text to create an employee or an
 * employer in the pila system.
 * If a post method is going to be call from within the application here is an
 * example:
 *   $request =  new Request();
 *   $request->request->set("employee_id", "123456");
 *   $request->request->set("concept_id", "1");
 *   $this->postFunctionAction($request);
 *
 */
class PilaPlainTextRestController extends FOSRestController
{


    public function digitoVerificacion($cedula){
        while(strlen($cedula) < 15) {
          $cedula = '0' . $cedula;
        }
        $specialPrimes = [71,67,59,53,47,43,41,37,29,23,19,17,13,7,3];
        $conta = 0;
        for($i = 0; $i < 15; $i++) {
          $conta += $specialPrimes[$i] * $cedula[$i];
        }
        $residuo = $conta % 11;
        if($residuo == 0 || $residuo == 1)
          return $residuo;
        return 11 - $residuo;
    }

    /**
     * Get the balance of a client.(6.1)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the balance of a client.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $beneficiaryId The id of the beneficiary of the client.
     *
     * @return View
     */
    public function getPlainTextCsvAction($documentNumber)
    {

      $personRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person");
      $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
      /** @var $NoveltyType NoveltyType  */
      $person = $personRepo->findOneBy(array('document' => $documentNumber));

      //die(print_r($person) . '_');

      $res = '';

      $res .= 'SERVICIO DOMESTICO'; // Its an array I dont know why.
      $res .= ',';
      $res .= $person->getDocumentType();
      $res .= ',';
      $res .= $person->getDocument();
      $res .= ',';
      $res .= $this->digitoVerificacion($person->getDocument());
      $res .= ',';
      $res .= $person->getNames() . ' ' . $person->getLastName1() . ' ' . $person->getLastName2(); // Razón social.
      $res .= ',';
      $res .= $person->getMainAddress();
      $res .= ',';
      $res .= '7953525'; // Its an array I dont know why.
      $res .= ',';
      $res .= '7953525'; // Fax.
      $res .= ',';
      $res .= 'pequeño';
      $res .= ',';
      $res .= 'privada';
      $res .= ',';
      $res .= ''; // Optional.
      $res .= ',';
      $res .= 'U'; // Presentation form.
      $res .= ',';
      $res .= ''; // Action type, optional.
      $res .= ',';
      $res .= 'I'; // Employer type.
      $res .= ',,,,,,,'; // 6 optional fields.
      $res .= $person->getDocumentType(); // Legal representative.
      $res .= ',';
      $res .= $person->getDocument(); // Legal representative.
      $res .= ',';
      $res .= explode(' ', $person->getNames())[0]; // Legal representative.
      $res .= ',';
      $res .= $person->getLastName1(); // Legal representative.
      $res .= ',';
      $res .= $person->getCity()->getName();
      $res .= ',';
      $res .= ''; // Economic activity, optional.
      $res .= ',';
      $res .= $userRepo->findOneBy(array('personPerson'=>$person->getIdPerson()))->getEmail();
      $res .= ',';
      $res .= $person->getPhones()[0]->getPhoneNumber();
      $res .= ',';
      $res .= ''; // Payment type.
      $res .= ',';
      $res .= 'S'; // Doesn't pay parafiscales.
      die($res);
    }

}

?>
