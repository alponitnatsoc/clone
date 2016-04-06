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

  /**
   * This method returns the verification digit of a NIT or a CC, this number
   * is math process to confirm the main number.
   *
   * @param Int $documentNumber The document to process the verification digit.
   * @return Int The verification digit.
   */
    public function digitoVerificacion($cedula){
        // For the equation to work, we need 15 digits on the string, we append
        // ceros at the begining of the document.
        while(strlen($cedula) < 15) {
          $cedula = '0' . $cedula;
        }
        // I'm not sure why it doesn't use all the primes, it skips some
        // numbers, here is the list of primes used.
        $specialPrimes = [71,67,59,53,47,43,41,37,29,23,19,17,13,7,3];
        $conta = 0;
        for($i = 0; $i < 15; $i++) {
          $conta += $specialPrimes[$i] * $cedula[$i];
        }
        $residuo = $conta % 11;
        // The formula says that after module 11, if the result is 1 or 0, that
        // is the result, if not, the result is 11 minus the result.
        if($residuo == 0 || $residuo == 1)
          return $residuo;
        return 11 - $residuo;
    }

    /**
     * Get the csv file to register the employer in the pila.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the csv file to register the employer in the pila.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     *
     * @return String
     */
    public function getPlainTextCsvAction($documentNumber)
    {

      $personRepo = $this->getDoctrine()->getRepository(
                                            "RocketSellerTwoPickBundle:Person");
      $userRepo = $this->getDoctrine()->getRepository(
                                            "RocketSellerTwoPickBundle:User");
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
      $res .= $person->getNames() . ' ' . $person->getLastName1() . ' ' .
              $person->getLastName2(); // Razón social.
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

      return $res;
    }

    /**
     * This method returns the salary of an employee given
     * the idEmployerHasEmployee.
     *
     * @param Int $idEmployerHasEmployee The id in our DB.
     * @return Int Salary of the employee.
     */
    public function getSalary($idEmployerHasEmployee)
    {
        $personRepo = $this->getDoctrine()->getRepository(
                              "RocketSellerTwoPickBundle:EmployerHasEmployee");
        /** @var $ehEs EmployerHasEmployee */
        $ehEs = $personRepo->findOneBy(array(
                            'idEmployerHasEmployee' => $idEmployerHasEmployee));
        if ($ehEs == null)
            return 0;
        if ($ehEs->getState() == 1) {
            $contracts = $ehEs->getContracts();
            /** @var $contract Contract */
            foreach ($contracts as $contract) {
                if ($contract->getState() == 1) {
                    return $contract->getSalary();
                }
            }
        }

        return 0;
    }

    /**
     * This method returns wether this empoyee pays social securtity.
     *
     * @param Int $employee The id of the employee in our DB.
     * @return Boolean.
     */
    public function aporta($employee) {
      $eheRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployeeHasEntity");
      /** @var $NoveltyType NoveltyType  */
      $ehe = $eheRepository->findBy(array('employeeEmployee' => $employee));
      foreach($ehe as $i) {
        $entity = $i->getEntityEntity();
        // If is AFP and doesn't pay.
        if($entity->getIdEntity() == 3 && $entity->getPayrollCode() == 0) {
          return false;
        }
      }
      return true;
    }

    /**
     * Returns the entity code for an employee.
     *
     * @param Int $employee The id of the employee in our DB.
     * @param Int $entity_code Entity to be queried(eps, arp, etc).
     * @return String The entity code.
     */
    public function codigoEntidad($employee, $entity_code) {
        $eheRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployeeHasEntity");
        /** @var $NoveltyType NoveltyType  */
        $ehe = $eheRepository->findBy(array('employeeEmployee' => $employee));
        foreach($ehe as $i) {
          $entity = $i->getEntityEntity();
          // If is AFP and doesn't pay.
          if($entity->getEntityTypeEntityType()->getIdEntityType() == $entity_code) {
            return $entity->getPilaCode();
          }
        }
        return false;
    }

    /**
     * Returns the entity code for an employer.
     *
     * @param Int $employer The id of the employer in our DB.
     * @param Int $entity_code Entity to be queried(eps, arp, etc).
     * @return String The entity code.
     */
    public function codigoEntidadEmployer($employer, $entity_code) {
        $eheRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEntity");
        /** @var $NoveltyType NoveltyType  */
        $ehe = $eheRepository->findBy(array('employerEmployer' => $employer));
        foreach($ehe as $i) {
          $entity = $i->getEntityEntity();
          // If is AFP and doesn't pay.
          if($entity->getEntityTypeEntityType()->getIdEntityType() == $entity_code) {
            return $entity->getPilaCode();
          }
        }
        return false;
    }

    /**
     * Returns the coverage code in the ARL of an empoyee.
     *
     * @param Int $document The idEmployerHasEmployee of the employee in our DB.
     * @return String The coverage code.
     */
    public function getArlCode($document)
    {
        $personRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
        /** @var $ehEs EmployerHasEmployee */
        $ehEs = $personRepo->findOneBy(array('idEmployerHasEmployee' => $document));
        if ($ehEs == null)
            return '';
        if ($ehEs->getState() == 1) {
            $contracts = $ehEs->getContracts();
            /** @var $contract Contract */
            foreach ($contracts as $contract) {
                if ($contract->getState() == 1) {
                    return $contract->getPositionPosition()->getPayrollCoverageCode();
                }
            }
        }

        return '';
    }

    /**
     * Get the csv file to register the employee_id in the pila.<br/>     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the csv file to register the employee_id in the
     *                  pila",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @return String
     */
    public function getPlainTextCsvEmployeeAction($idEmployee)
    {
      $employeeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employee");
      $employee = $employeeRepo->findOneBy(array('idEmployee' => $idEmployee));

      $person = $employee->getPersonPerson();

      $eheRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
      $ehe = $eheRepo->findOneBy(array('employeeEmployee' => $idEmployee));
      $idEmployerHasEmployee = $ehe->getIdEmployerHasEmployee();
      $idEmployer = $ehe->getEmployerEmployer()->getIdEmployer();
      $names = explode(' ', $person->getNames());
      $nit = $ehe->getEmployerEmployer()->getPersonPerson()->getDocument();

      $res = '';
      $res .= $nit;//Nit empleador.
      $res .= ',';
      $res .= $person->getDocumentType();
      $res .= ',';
      $res .= $person->getDocument();
      $res .= ',';
      $res .= $names[0];
      $res .= ',';
      $res .= count($names) > 1 ? $names[0] : ''; // In case of no middle name.
      $res .= ',';
      $res .= $person->getLastName1();
      $res .= ',';
      $res .= $person->getLastName2();
      $res .= ',';
      $res .= $this->getSalary($idEmployerHasEmployee);
      $res .= ',';
      $res .= $this->getSalary($idEmployerHasEmployee);
      $res .= ',';
      $res .= '02'; // Tipo cotizante.
      $res .= ',';
      $res .= $this->aporta($idEmployee) ? '00' : '04';
      $res .= ',';
      $res .= $person->getDepartment()->getName();
      $res .= ',';
      $res .= $person->getCity()->getName();
      $res .= ',';
      $res .= $this->codigoEntidad($idEmployee, 1) | ''; // 1 is eps.
      $res .= ',';
      $res .= $this->codigoEntidad($idEmployee, 3) | ''; // 3 is afp.
      $res .= ',';
      $res .= ''; // Work center, not needed.
      $res .= ',';
      $res .= ''; // AFP fee class.
      $res .= ',';
      $res .= $this->getArlCode($idEmployerHasEmployee);
      $res .= ',';
      $res .= $this->codigoEntidadEmployer($idEmployer, 4) | ''; // 4 is ccf.
      $res .= ',';
      $res .= '4%'; // Tarifa CCF.
      $res .= ',,,,,,,'; // SENA, ICBF, live abroad and aditional UPC*3.
      $res .= 'S'; // Exonerated parafiscales.
      $res .= ',';
      $res .= $this->codigoEntidadEmployer($idEmployer, 2) | ''; // 2 is arl.

      die($res);
      return $res;
    }
}

?>
