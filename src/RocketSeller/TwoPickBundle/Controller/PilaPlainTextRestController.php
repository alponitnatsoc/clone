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

class Item {
  var $pos_inicial;
  var $pos_final;
  var $valor;

  function __construct($pos_inicial, $pos_final, $valor) {
     $this->pos_inicial = $pos_inicial;
     $this->pos_final = $pos_final;
     $this->valor = $valor;
  }
}

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
      // We first write the header.
      $res = 'CLASIFICACION_APORTANTE,DSTIPO_DOCUMENTO,DSNUMERO_DOCUMENTO,NMDIGITO_VERIFICACION,DSRAZON_SOCIAL,DSDIRECCION,DSTELEFONO,DSFAX,DSCLASE_APORTANTE,NMNATURALEZA_JURIDICA,,DSFORMA_PRESENTACION,NMTIPO_ACCION,NMTIPO_APORTANTE,SNAPORTA_MEN,POTARIFA_MEN,SNAPORTA_ESAP,POTARIFA_ESAP,FEINICIO_ACTIVIDAD,FEFINAL_ACTIVIDAD,TD_ REPRESENTANTE LEGAL,NM_NUMERO,PRIMER NOMBRE REPRESENTANTE LEGAL,PRIMER APELLIDO REPRESENTANTE LEGAL,CDMUNICIPIO,CDACTIVIDAD_ECONOMICA,DSCORREO_ELECTRONICO,DSCELULAR,NMTIPO_PAGADOR,SNEXONERADO_PARAFISCALES';
      $res .= "\n";
      $res .= 'SERVICIO DOMESTICO';
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
      $res .= $person->getPhones()[0]->getPhoneNumber();
      $res .= ',';
      $res .= $person->getPhones()[0]->getPhoneNumber(); //Fax.
      $res .= ',';
      $res .= 'Independiente';
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

      // Create a new file with the csv.
      $filename = 'pila_empleador_' . $person->getDocument() . '.csv';
      header("Content-type: text/plain; charset=utf-8");
      header("Content-Disposition: attachment; filename=$filename");

      // Print all the content to the file created.
      die($res);
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
      // We first set the header.
      $res = 'NIT APORTANTE,Tipo Documento,Número documento,Primer nombre,Segundo nombre,Primer apellido,Segundo apellido,Salario básico,Salario integral,Tipo cotizante,Subtipo cotizante,Departamento,Municipio,Código EPS,Código AFP,Centro trabajo,Clase tarifa ARP,Tarifa ARP,Codigo CCF,Tarifa CCF,Tarifa SENA,Tarifa ICBF,Colombiano exterior,UPC Adicional,Tipo Documento Responsable UPC,Numero Documento Responsable UPC,Cotizante Exonerado Parafiscales (S/N),Codigo ARL';
      $res .= "\n";
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

      // Create a new file with the csv.
      $filename = 'pila_empleado_' . $person->getDocument() . '.csv';
      header("Content-type: text/plain; charset=utf-8");
      header("Content-Disposition: attachment; filename=$filename");

      // Print all the content to the file created.
      die($res);
    }

    public $elementos = array();

    public function add($a, $b, $c) {
      $item = new Item($a, $b, $c);
      $elementos = &$this->elementos;
      $elementos[] = $item;
    }

    public function executeLine() {
      // TODO: sort all the items based on start of line, in case of error.
      $elementos = &$this->elementos;
      $res = '';
      foreach($elementos as $line) {
        $length = ($line->pos_final - $line->pos_inicial) + 1;
        // If the length of the line makes no sense.
        if(strlen($line->valor) > $length) {
          throw new \Exception('The length of the value of field starting in position: '
                              . $line->pos_inicial . ' is greater than the space.');
        }

        $res .= $line->valor;

        for($i = 0; $i < ($length - strlen($line->valor)); $i++) {
          // We add a space in each empty position.
          $res .= ' ';
        }
      }
      return $res;
    }

    // Type is E or S.
    // Count is the number of employees of this type.
    public function createEncabezado($idEmployer, $type, $count){
      $elementos = &$this->elementos;
      $elementos = array();
      $employer = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
      /** @var Employer $emp */
      $emp = $employer->findOneBy(array('idEmployer' => $idEmployer));

      $razonSocial = $emp->getPersonPerson()->getNames();
      $razonSocial .= ' ' . $emp->getPersonPerson()->getLastName1();
      if($emp->getPersonPerson()->getLastName2())
        $razonSocial .= ' ' . $emp->getPersonPerson()->getLastName2();

      $tipoDocumento = $emp->getPersonPerson()->getDocumentType();
      if($tipoDocumento == 'cc')
        $tipoDocumento = 'CC';
      elseif($tipoDocumento == 'ce')
        $tipoDocumento = 'CE';
      elseif($tipoDocumento == 'ti')
        $tipoDocumento = 'TI';
      elseif($tipoDocumento == 'NIT' || $tipoDocumento == 'nit')
        $tipoDocumento = 'NI';

      $documento = $emp->getPersonPerson()->getDocument();
      $digitoVer = $this->digitoVerificacion($documento);

      $entidades = $emp->getEntities();
      $entidadARL = '';
      foreach($entidades as $i) {
        if($i->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == 'ARP') {
          $entidadARL = $i->getEntityEntity()->getPilaCode();
        }
      }
      $currentPeriod = date("Y-m");
      $pastPeriod = date("Y-m", strtotime("-1 months"));
      $tipoAportante = '';
      if($type == 'E')
        $tipoAportante = '1';
      else
        $tipoAportante = '2';

      // Add left zeros to count.
      $count2 = ''.$count;
      $count = '';
      for($i = 0; $i < 5 - strlen($count2); $i ++){
        $count .= '0';
      }
      $count .= $count2;



      //Articulo 7 resolucion 1747 de 2008.
      // Campo 1.
      $this->add(1, 2, '01'); //01 is mandatory.
      // Campo 2a.
      $this->add(3, 3, 0); //01 is electronica.
      // Campo 2b.
      $this->add(4, 7, '0001'); //this is supose to be a sequence, but only 1 employer is allowed.
      // Campo 3.
      $this->add(8, 207, $razonSocial);
      // Campo 4.
      $this->add(208, 209, $tipoDocumento);
      // Campo 5.
      $this->add(210, 225, $documento);
      // Campo 6.
      $this->add(226, 226, $digitoVer);
      // Campo 7.
      $this->add(227, 227, $type);
      // Campo 8.
      $this->add(228, 237, ''); // Empty because of the type of planilla.
      // Campo 9.
      $this->add(238, 247, ''); // Empty because of the type of planilla.
      // Campo 10.
      $this->add(248, 248, 'U'); // Means unico.
      // Campo 11.
      $this->add(249, 258, ''); // The example has this empty.
      // Campo 12.
      $this->add(259, 298, ''); // The example has this empty.
      // Campo 13.
      $this->add(299, 304, $entidadARL);
      // Campo 14.
      $this->add(305, 311, $pastPeriod);
      // Campo 15.
      $this->add(312, 318, $currentPeriod);
      // Campo 16.
      $this->add(319, 328, '0000000000'); // Goes all in 0.
      // Campo 17.
      $this->add(329, 338, ''); // Empty for now.
      // Campo 18.
      $this->add(339, 343, $count);
      // Campo 19.
      $this->add(344, 355, '000000000000'); // Goes all in 0.
      // Campo 20.
      $this->add(356, 356, $tipoAportante);
      // Campo 21.
      $this->add(357, 358, '00');

      return $this->executeLine();
    }

    /**
     * Get the monthly plain text of an employer.
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the monthly plain text of an employer",
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
    public function getMonthlyPlainTextAction($idEmployer) {
      $employer = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
      /** @var Employer $emp */
      $emp = $employer->findOneBy(array('idEmployer' => $idEmployer));

      $employees = $emp->getEmployerHasEmployees();
      $tiempo_completo = array();
      $tiempo_parcial = array();

      foreach($employees as $employee) {
        $contracts = $employee->getContracts();
        foreach($contracts as $contract) {
          if($contract->getState() != 1)
            continue;
          if($contract->getTimeCommitmentTimeCommitment()->getCode() == 'TC')
            $tiempo_completo[] = $employee->getEmployeeEmployee();
          else
            $tiempo_parcial[] = $employee->getEmployeeEmployee();
        }
      }

      if(count($tiempo_completo) > 0) {
        die($this->createEncabezado($idEmployer, 'S', count($tiempo_completo)));
      }
      if(count($tiempo_parcial) > 0) {
        // Commented for test porpouses.
        die($this->createEncabezado($idEmployer, 'E', count($tiempo_parcial)));
      }
    }
}

?>
