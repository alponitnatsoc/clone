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
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
  var $zeros;

  function __construct($pos_inicial, $pos_final, $valor, $zeros=false) {
     $this->pos_inicial = $pos_inicial;
     $this->pos_final = $pos_final;
     $this->valor = $valor;
     $this->zeros = $zeros;
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
        if($entity->getEntityTypeEntityType()->getPayrollCode() == "AFP" && $entity->getPayrollCode() == 0) {
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

    public function add($a, $b, $c, $d=false) {
      $item = new Item($a, $b, $c, $d);
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
          if(!$line->zeros)
            $res .= ' ';
          else
            $res .= '0';
        }
      }
      return $res;
    }

    public function diasConDescuento($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 1) {
          $total += $item['NOMI_UNIDADES'];
        }
      }
      return $total;
    }
    public function diasSinDescuento($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 1 && $item['NOMI_UNIDADES'] > 0) {
          $total += $item['NOMI_UNIDADES'];
        }
      }
      return $total;
    }

    public function diasLicencia($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 3120 ) {
          $total += $item['NOMI_UNIDADES'];
        }
      }
      return $total;
    }

    public function valorLicencia($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 3120 ) {
          $total += $item['NOMI_VALOR_LOCAL'];
        }
      }
      return $total;
    }

    public function novedadGeneral($employeeInfo, $codigo) {
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 2) {
          return true;
        }
      }
      return false;
    }

    public function variacionTransitoriaSalario($employeeInfo) {
      // We will look for extra hours.
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] >= 45 && $item['CON_CODIGO'] <= 80) {
          return true;
        }
      }
      return false;
    }

    public function suspensionTemporal($employeeInfo) {
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 3125 || $item['CON_CODIGO'] == 3120) {
          return true;
        }
      }
      return false;
    }

    public function licenciaMaternidadPaternidad($employeeInfo) {
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 25 || $item['CON_CODIGO'] == 26) {
          return true;
        }
      }
      return false;
    }

    public function incapacidadAccidente($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 27 || $item['CON_CODIGO'] == 28) {
          $total += $item['NOMI_UNIDADES'];
        }
      }
      return $total;
    }


    public function getIBC($employeeInfo) {
      $salario_minio = 689455;
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 1) {
          $total += $item['NOMI_VALOR_LOCAL'];
        }
      }
      return $total;
    }

    public function getIBCSalud($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 3010) {
          $total += $item['NOMI_BASE'];
        }
      }
      return $total;
    }

    public function getIBCPension($employeeInfo) {
      $total = 0;
      foreach($employeeInfo as $item) {
        if($item['CON_CODIGO'] == 3020) {
          $total += $item['NOMI_BASE'];
        }
      }
      return $total;
    }

    public function leftZeros($value, $space) {
      $value2 = ''.$value;
      $value = '';
      for($i = 0; $i < $space - strlen($value2); $i ++){
        $value .= '0';
      }
      $value .= $value2;
      return $value;
    }


    public function employeeParame($consecutivo,$tipoDocumento,$documento,$subtipoCotizante,$deparmentCode,$municipioCode,
    $firstLastName,$secondLastName,$firstFirstName,$secondFirstName,$codigoAFP,$codigoEPS,$codigoCCF,$diasSinDescuento,$diasConDescuento,
    $salary,$ibc_arl_caja,$ibc_salud,$ibc_pension,$porcentaje_pension,$aporte_pension,$porcentaje_salud,$aporte_salud,$porcentaje_arl,$aporte_arl,$porcentaje_caja,$aporte_caja,$exonerated,
    $ingreso,$retiro,$variacionSalario,$variacionTransitoriaSalario,$suspensionTemporal,$enfermedadGeneral,$licenciaMaternidadPaternidad,$vacaciones,$incapacidadAccidente) {
      //Articulo 10 resolucion 1747 de 2008.
      // Campo 1.
      $this->add(1, 2, '02'); //02 is mandatory.
      // Campo 2.
      $this->add(3, 7, $consecutivo);
      // Campo 3.
      $this->add(8, 9, $tipoDocumento);
      // Campo 4.
      $this->add(10, 25, $documento);
      // Campo 5.
      $this->add(26, 27, '02');   // 02 si es tiempo completo 51 si es tiempo parcial importante cambiar.!!!!!!!!!!!!!
      // Campo 6.
      $this->add(28, 29, $subtipoCotizante, true); //!!!!!!!!!cambiar por 00
      // Campo 7.
      $this->add(30, 30, ''); // We don't accept foreigners.
      // Campo 8.
      $this->add(31, 31, ''); // We don't accept living abroad.
      // Campo 9.
      $this->add(32, 33, $deparmentCode);
      // Campo 10.
      $this->add(34, 36, $municipioCode);
      // Campo 11.
      $this->add(37, 56, $firstLastName);
      // Campo 12.
      $this->add(57, 86, $secondLastName);
      // Campo 13.
      $this->add(87, 106, $firstFirstName);
      // Campo 14.
      $this->add(107, 136, $secondFirstName);

      /* This needs to be fixed later, but should be updated, depending on the
      novelty, it is important. */
      // Campo 15.
      $this->add(137, 137, $ingreso);
      // Campo 16.
      $this->add(138, 138, $retiro);
      // Campo 17.
      $this->add(139, 139, '');
      // Campo 18.
      $this->add(140, 140, '');
      // Campo 19.
      $this->add(141, 141, '');
      // Campo 20.
      $this->add(142, 142, '');
      // Campo 21.
      $this->add(143, 143, $variacionSalario);
      // Campo 22.
      $this->add(144, 144, '');
      // Campo 23.
      $this->add(145, 145, $variacionTransitoriaSalario);
      // Campo 24.
      $this->add(146, 146, $suspensionTemporal);
      // Campo 25.
      $this->add(147, 147, $enfermedadGeneral);
      // Campo 26.
      $this->add(148, 148, $licenciaMaternidadPaternidad );
      // Campo 27.
      $this->add(149, 149, $vacaciones);
      // Campo 28.
      $this->add(150, 150, '');
      // Campo 29.
      $this->add(151, 151, '');
      // Campo 30.
      $this->add(152, 153, $incapacidadAccidente, true);
      /* Here finish the novelties */

      // Campo 31.
      $this->add(154, 159, $codigoAFP);
      // Campo 32.
      $this->add(160, 165, '');// Only if the employee is changing AFP.
      // Campo 33.
      $this->add(166, 171, $codigoEPS);
      // Campo 34.
      $this->add(172, 177, '');// Only if the employee is changing EPS.
      // Campo 35.
      $this->add(178, 183, $codigoCCF);
      // Campo 36.
      $this->add(184, 185, $diasSinDescuento);
      // Campo 37.
      $this->add(186, 187, $diasSinDescuento);
      // Campo 38.
      $this->add(188, 189, $diasConDescuento);
      // Campo 39.
      $this->add(190, 191, $diasConDescuento);
      // Campo 40.
      $this->add(192, 200, $salary);
      // Campo 41.
      $this->add(201, 201, '');
      // Campo 42.
      $this->add(202, 210, $ibc_pension);
      // Campo 43.
      $this->add(211, 219, $ibc_salud);
      // Campo 44.
      $this->add(220, 228, $ibc_arl_caja);
      // Campo 45.
      $this->add(229, 237, $ibc_arl_caja);
      // Campo 46.
      $this->add(238, 244, $porcentaje_pension, true);
      // Campo 47.
      $this->add(245, 253, $aporte_pension);
      // Campo 48 a 53.
      $this->add(254, 307, '', true);
      // Campo 54.
      $this->add(308, 314, $porcentaje_salud, true);
      // Campo 55.
      $this->add(315, 323, $aporte_salud);
      // Campo 56.
      $this->add(324, 332, '', true);
      // Campo 57.
      $this->add(333, 347, '');
      // Campo 58.
      $this->add(348, 356, '', true);
      // Campo 59.
      $this->add(357, 371, '');
      // Campo 60.
      $this->add(372, 380, '', true);
      // Campo 61.
      $this->add(381, 389, $porcentaje_arl, true);
      // Campo 62.
      $this->add(390, 398, '', true);
      // Campo 63.
      $this->add(399, 407, $aporte_arl);
      // Campo 64.
      $this->add(408, 414, $porcentaje_caja, true);
      // Campo 65.
      $this->add(415, 423, $aporte_caja);
      // Campo 66.
      $this->add(424, 430, '0.0', true);
      // Campo 67.
      $this->add(431, 439, '', true);
      // Campo 68.
      $this->add(440, 446, '0.0', true);
      // Campo 69.
      $this->add(447, 455, '', true);
      // Campo 70.
      $this->add(456, 462, '0.0', true);
      // Campo 71.
      $this->add(463, 471, '', true);
      // Campo 72.
      $this->add(472, 478, '0.0', true);
      // Campo 73.
      $this->add(479, 487, '', true);
      // Blank spaces.
      $this->add(488, 505, '');
      // Campo 74 Resolucion 130.
      $this->add(506, 506, $exonerated);
    }

    // Type is E or S.
    // Count is the number of employees of this type.
    public function createLineaEmpleado($pilaArr, $exonerated=false, $idEmployer){
      $consecutivo = 1;
      if($exonerated)
        $exonerated = 'S';
      else
        $exonerated = 'N';

      $lineArr = array();
      foreach($pilaArr as $key => $pila) {
        //dump($employee);die();
        $employee = $pila->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee();
        // Add left zeros to count.
        $consecutivo2 = ''.$consecutivo;
        $consecutivo = '';
        for($i = 0; $i < 5 - strlen($consecutivo2); $i ++){
          $consecutivo .= '0';
        }
        $consecutivo .= $consecutivo2;

        $tipoDocumento = $employee->getPersonPerson()->getDocumentType();
        if($tipoDocumento == 'cc')
          $tipoDocumento = 'CC';
        elseif($tipoDocumento == 'ce')
          $tipoDocumento = 'CE';
        elseif($tipoDocumento == 'ti')
          $tipoDocumento = 'TI';
        elseif($tipoDocumento == 'NIT' || $tipoDocumento == 'nit')
          $tipoDocumento = 'NI';

        $documento = $employee->getPersonPerson()->getDocument();
        $subtipoCotizante = '';
        if($this->aporta($employee))
            $subtipoCotizante = '';
        else
            $subtipoCotizante = '4';

        $deparmentCode = $employee->getPersonPerson()->getDepartment()->getDepartmentCode();
        $municipioCode = $employee->getPersonPerson()->getCity()->getCityCode();
        $municipioCode = substr($municipioCode, -3);
        $firstLastName = $employee->getPersonPerson()->getLastName1();
        $secondLastName = $employee->getPersonPerson()->getLastName2() ?: '';
        $firstFirstName = $employee->getPersonPerson()->getNames();
        $secondFirstName = isset(explode(' ', $firstFirstName)[1]) ? explode(' ', $firstFirstName)[1] : '';
        $firstFirstName = explode(' ', $firstFirstName)[0];
        $codigoAFP = $this->codigoEntidad($employee->getIdEmployee(), 3); //3 is afp.
        $codigoEPS = $this->codigoEntidad($employee->getIdEmployee(), 1); //1 is eps.
        $codigoCCF = $this->codigoEntidadEmployer($idEmployer, 4); // 4 is ccf.

        $eheRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
        $ehe = $eheRepo->findOneBy(array('employeeEmployee' => $employee->getIdEmployee()));
        $idEmployerHasEmployee = $ehe->getIdEmployerHasEmployee();
        // Call SQL for the next information.
        // General payroll, getting the employee information.
        $employeeInfo = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getGeneralPayroll', array(
            "employeeId" => $idEmployerHasEmployee,
                ), array('_format' => 'json'));



        if ($employeeInfo->getStatusCode() != 200) {
          throw new \Exception('Error getting the information from SQL. Id employee: ' . $idEmployerHasEmployee);
        }
        $employeeInfo = json_decode($employeeInfo->getContent(), true);

        // General external entities, getting the employer information.
        $employerInfo = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getExternalEntitiesLiquidation', array(
            'employeeId' => $idEmployerHasEmployee,
                ), array('_format' => 'json')
        );
        $employerInfo = json_decode($employerInfo->getContent(), true);


        // Fixed concepts, to get the salary.
        $salary = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getFixedConcepts', array(
            'employeeId' => $idEmployerHasEmployee,
                ), array('_format' => 'json')
        );
        if ($salary->getStatusCode() != 200) {
          throw new \Exception('Error getting the information from SQL.');
        }
        $salary = json_decode($salary->getContent(), true)['COF_VALOR'];
        $salary = $salary - $this->valorLicencia($employeeInfo);
        $salary = $this->leftZeros($salary, 9);

        // Get final liquidation parameters.
        $finalLiquidation = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getFinalLiquidationParameters', array(
            'employeeId' => $idEmployerHasEmployee,
                ), array('_format' => 'json')
        );
        $finalLiquidation = json_decode($finalLiquidation->getContent(), true);
        $diasSinDescuento = $this->diasSinDescuento($employeeInfo) - $this->diasLicencia($employeeInfo);
        $diasConDescuento = $this->diasConDescuento($employeeInfo) - $this->diasLicencia($employeeInfo);

        $diasSinDescuento = $this->leftZeros($diasSinDescuento, 2);
        $diasConDescuento = $this->leftZeros($diasConDescuento, 2);
        $ibc_arl_caja = $this->getIBC($employeeInfo) -  $this->valorLicencia($employeeInfo);
        $ibc_arl_caja = $this->leftZeros($ibc_arl_caja, 9);

        $ibc_salud = $this->getIBCSalud($employeeInfo) -  $this->valorLicencia($employeeInfo);
        $ibc_pension= $this->getIBCPension($employeeInfo) - $this->valorLicencia($employeeInfo);
        $ibc_salud = $this->leftZeros($ibc_salud, 9);
        $ibc_pension = $this->leftZeros($ibc_pension, 9);

        $aporte_pension = 0;
        $porcentaje_pension = '0.16';
        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'AFP' && $item['APR_APORTE_EMP'] != 0)
          {
            $aporte_pension += $item['APR_APORTE_EMP'];
            $aporte_pension += $item['APR_APORTE_CIA'];
          }
        }
        $aporte_pension = $this->leftZeros($aporte_pension, 9);

        $porcentaje_salud = '0.125';
        if($exonerated == 'S')
          $porcentaje_salud = '0.04';
        $aporte_salud = 0;
        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'EPS' && $item['APR_APORTE_EMP'] != 0)
          {
            $aporte_salud += $item['APR_APORTE_EMP'];
            $aporte_salud += $item['APR_APORTE_CIA'];
          }
        }
        $aporte_salud = $this->leftZeros($aporte_salud, 9);

        $porcentaje_arl = 0;
        $contracts = $ehe->getContracts();
        $start_date = '';
        foreach($contracts as $contract) {
          if($contract->getState() != 1)
            continue;
          if($contract->getPositionPosition()->getPayrollCoverageCode() == 1)
            $porcentaje_arl = 0.00522;
          elseif($contract->getPositionPosition()->getPayrollCoverageCode() == 2)
            $porcentaje_arl = 0.01044;
          elseif($contract->getPositionPosition()->getPayrollCoverageCode() == 3)
            $porcentaje_arl = 0.02436;
          elseif($contract->getPositionPosition()->getPayrollCoverageCode() == 4)
            $porcentaje_arl = 0.04350;
          elseif($contract->getPositionPosition()->getPayrollCoverageCode() == 5)
            $porcentaje_arl = 0.06960;

          $start_date = $contract->getStartDate();
        }

        $aporte_arl = 0;
        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'ARP' && $item['APR_APORTE_CIA'] != 0)
          {
            $aporte_arl += $item['APR_APORTE_EMP'];
            $aporte_arl += $item['APR_APORTE_CIA'];
          }
        }

        $aporte_arl = $this->leftZeros($aporte_arl, 9);
        $porcentaje_caja = '0.04';
        $aporte_caja = 0;

        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'PARAFISCAL' && $item['COB_CODIGO'] == 1  && $item['APR_APORTE_CIA'] != 0)
          {

            $aporte_caja += $item['APR_APORTE_EMP'];
            $aporte_caja += $item['APR_APORTE_CIA'];
          }
        }
        $aporte_caja = $this->leftZeros($aporte_caja, 9);
        // Novelties.
        // Ingreso.
        $start_date_month = $start_date->format('m');
        $start_date_year = $start_date->format('y');
        $ingreso = '';
        if($start_date_month == date('m') && $start_date_year == date("y")) {
          $ingreso = 'X';
        }
        $retiro = '';
        if(count($finalLiquidation) > 0)
          $retiro = 'X';

        $variacionSalario = '';
        if($this->novedadGeneral($employeeInfo, 2))
          $variacionSalario = 'X';

        $variacionTransitoriaSalario = '';
        if($this->variacionTransitoriaSalario($employeeInfo))
          $variacionTransitoriaSalario = 'X';

        $suspensionTemporal = '';
        if($this->suspensionTemporal($employeeInfo))
          $suspensionTemporal = 'X';

        $enfermedadGeneral = '';
        if($this->novedadGeneral($employeeInfo, 15))
          $enfermedadGeneral = 'X';

        $licenciaMaternidadPaternidad = '';
        if($this->licenciaMaternidadPaternidad($employeeInfo))
          $licenciaMaternidadPaternidad = 'X';

        $vacaciones = '';
        if($this->novedadGeneral($employeeInfo, 145))
          $vacaciones = 'X';

        $incapacidadAccidente = $this->incapacidadAccidente($employeeInfo);

        $this->employeeParame($consecutivo,$tipoDocumento,$documento,$subtipoCotizante,$deparmentCode,$municipioCode,
        $firstLastName,$secondLastName,$firstFirstName,$secondFirstName,$codigoAFP,$codigoEPS,$codigoCCF,$diasSinDescuento,$diasConDescuento,
        $salary,$ibc_arl_caja,$ibc_salud,$ibc_pension,$porcentaje_pension,$aporte_pension,$porcentaje_salud,$aporte_salud,$porcentaje_arl,$aporte_arl,$porcentaje_caja,$aporte_caja,$exonerated,
        $ingreso,$retiro,$variacionSalario,$variacionTransitoriaSalario,'',$enfermedadGeneral,$licenciaMaternidadPaternidad,$vacaciones,$incapacidadAccidente);
        $line = $this->executeLine();
        $this->elementos = array();
        if($suspensionTemporal != 'X'){
          $lineArr[] = $line;
          continue;
        }

        // Here starts the second line, only if there is a non payable absentism.

        $line .= "\n";
        $diasSinDescuento = $this->diasLicencia($employeeInfo);
        $diasSinDescuento = $this->leftZeros($diasSinDescuento, 2);
        $diasConDescuento = $this->diasLicencia($employeeInfo);
        $diasConDescuento = $this->leftZeros($diasConDescuento, 2);
        $salary = $this->valorLicencia($employeeInfo);
        $salary = $this->leftZeros($salary, 9);
        $ibc = $salary;
        $porcentaje_pension = '0.12';
        $aporte_pension = 0;
        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'AFP' && $item['APR_APORTE_EMP'] == 0)
          {
            $aporte_pension += $item['APR_APORTE_EMP'];
            $aporte_pension += $item['APR_APORTE_CIA'];
            break;
          }
        }
        $aporte_pension = $this->leftZeros($aporte_pension, 9);

        $porcentaje_salud = '0.085';
        if($exonerated == 'S')
          $porcentaje_salud = '0.0';
        $aporte_salud = 0;
        foreach($employerInfo as $item) {
          if($item['TENT_CODIGO'] == 'EPS' && $item['APR_APORTE_EMP'] == 0)
          {
            $aporte_salud += $item['APR_APORTE_EMP'];
            $aporte_salud += $item['APR_APORTE_CIA'];
            break;
          }
        }
        $aporte_salud = $this->leftZeros($aporte_salud, 9);

        $porcentaje_arl = '0.0';
        $aporte_arl = 0;
        $aporte_arl = $this->leftZeros($aporte_arl, 9);

        $aporte_caja = 0;
        $aporte_caja = $this->leftZeros($aporte_caja, 9);
        $porcentaje_caja = '0.0';


        $this->employeeParame($consecutivo,$tipoDocumento,$documento,$subtipoCotizante,$deparmentCode,$municipioCode,
        $firstLastName,$secondLastName,$firstFirstName,$secondFirstName,$codigoAFP,$codigoEPS,$codigoCCF,$diasSinDescuento,$diasConDescuento,
        $salary,$ibc,$ibc,$ibc,$porcentaje_pension,$aporte_pension,$porcentaje_salud,$aporte_salud,$porcentaje_arl,$aporte_arl,$porcentaje_caja,$aporte_caja,$exonerated,
        $ingreso,$retiro,$variacionSalario,$variacionTransitoriaSalario,$suspensionTemporal,$enfermedadGeneral,$licenciaMaternidadPaternidad,$vacaciones,$incapacidadAccidente);

        $line = $line . $this->executeLine();
        $this->elementos = array();

        $lineArr[] = $line;
      }

      $returnString = "";
      foreach ($lineArr as $key => $singleLine) {
        if($key  == count($lineArr) - 1){
          $returnString = $returnString . $singleLine;
          break;
        }
        $returnString = $returnString . $singleLine;
        $returnString = $returnString . "\n";
      }

      return $returnString;
    }


    // Type is E or S.
    // Count is the number of employees of this type.
    public function createEncabezado($employer, $type, $count){
      $elementos = &$this->elementos;
      $elementos = array();

      /** @var Employer $emp */
      $emp = $employer;

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
        $tipoAportante = '2';  /// Camiar debe ser 2 siempre importante cambiar, esto es inutil se debe cambiar.

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
     * @param Int $podId id of the purchase order description.
     * @return String
     */
    public function getMonthlyPlainTextAction($podId) {
      // TOGO 0

      $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
      $pod = $podRepo->findOneBy(array("idPurchaseOrdersDescription" => $podId));

      $pilaArr = $pod->getPayrollsPila();
      $type = $pilaArr[0]->getContractContract()->getPlanillaTypePlanillaType()->getCode();

      $numberEmployees = 0;

      $tempEmployer = $pilaArr[0]->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer();
      $tempArrayEhe = $tempEmployer->getEmployerHasEmployees();
      /** @var EmployerHasEmployee $ehe */
      foreach($tempArrayEhe as $ehe) {
        if($ehe->getState() >= 4){
          $contractsToValidate = $ehe->getContracts();
          foreach ($contractsToValidate as $contractToCheck) {
            if($contractToCheck->getState() == 1){
              $numberEmployees ++;
              break;
            }
          }
        }
      }

      $exonerated = $numberEmployees > 1 ? true: false;

      $employer = $pilaArr[0]->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer();

      //S es tiempo completo (Convencional)
      //E es tiempo parcial sin Sisben (Domestico)

      $line = $this->createEncabezado($employer, $type, $numberEmployees);
      $line .= "\n";
      $this->elementos = array();
      $line .= $this->createLineaEmpleado($pilaArr, $exonerated, $employer->getIdEmployer());
      $filename = 'PILA_' . $type . $employer->getPersonPerson()->getLastName1() .$employer->getIdEmployer() . '.txt';
      header("Content-type: text/plain; charset=utf-8");
      header("Content-Disposition: attachment; filename=$filename");

      die($line);
    }
}

?>
