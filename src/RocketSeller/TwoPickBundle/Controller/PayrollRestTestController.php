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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
     * @Get("mock/sql/default")
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
     * @return View
     */
    public function getDefaultUrlAction(Request $request)
    {
        $codigo_interfaz =  $request->query->get('inInexCod');
        $xml = $request->query->get('clXMLSolic');
        if($codigo_interfaz == 601)
          return $this->addEmployee($xml);
        if($codigo_interfaz == 602)
          return $this->getEmployee($xml);
    }

  public function addEmployee($xml)
  {

      $xmlModelCorrect = '<InfoProceso>
                            <MensajeRetorno/>
                            <LogProceso>
                            Tipo registro 1 - Se valida el bloque - Kic_Adm_Ice.Pic_Ins_Reg_Blq -
                            <ERRORQ>606</ERRORQ>
                            - "
                            <UNICO>
                            <TIPOCON>1</TIPOCON>
                            <EMP_CODIGO>1020345201</EMP_CODIGO>
                            <CON_CODIGO>45</CON_CODIGO>
                            <VALOR/>
                            <UNIDADES>19</UNIDADES>
                            <FECHA>2015-01-01</FECHA>
                            <PROD_CODIGO>1</PROD_CODIGO>
                            <CENCOS/>
                            <DOCU/>
                            <MONEDA/>
                            <FLIQ/>
                            <USER/>
                            <TERMINAL/>
                            <FESIS/>
                            <PEXT/>
                            <FECNOV/>
                            <NOV_CONSEC>57190</NOV_CONSEC>
                            <VALLOC/>
                            <EN1_CODIGO/>
                            </UNICO>
                            " Tipo registro 1 - Se valida el bloque - Kic_Adm_Ice.Pic_Ins_Reg_Blq -
                            <ERRORQ>404</ERRORQ>
                            - "
                            <UNICO>
                            <TIPOCON>1</TIPOCON>
                            <EMP_CODIGO>102034520</EMP_CODIGO>
                            <CON_CODIGO>45</CON_CODIGO>
                            <VALOR/>
                            <UNIDADES>19</UNIDADES>
                            <FECHA>2015-01-01</FECHA>
                            <PROD_CODIGO>1</PROD_CODIGO>
                            <CENCOS/>
                            <DOCU/>
                            <MONEDA/>
                            <FLIQ/>
                            <USER/>
                            <TERMINAL/>
                            <FESIS/>
                            <PEXT/>
                            <FECNOV/>
                            <NOV_CONSEC>57190</NOV_CONSEC>
                            <VALLOC/>
                            <EN1_CODIGO/>
                            </UNICO>
                            " ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 2 0 2 TOTALES Registros leídos Registros buenos Registros rechazados 2 0 2 ----------------------------------------------------------------------------------------
                            </LogProceso>
                            </InfoProceso>';
      $xmlModelIncorrect = '<InfoProceso>
                            <MensajeRetorno/>
                            <LogProceso>
                            ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                            </LogProceso>
                            </InfoProceso>';
                  $destination = array();
      $parsed = new \SimpleXMLElement($xml);
      $components = array();
      foreach ($parsed as $element) {
        foreach($element as $key => $val) {
         // echo "___{$key}____: ***{$val}***";
         $components[$key] = (String)$val;
        }
      }
      //die(print_r($components, true));

      if($components['EMP_CODIGO'] == 123456789)
      {
          $response = new Response(
              $xmlModelCorrect,
              Response::HTTP_OK,
              array(
                  'Content-Type' => 'application/xml',
              )
          );
          return $response;
      } else
      {
        $response = new Response(
            $xmlModelIncorrect,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/xml',
            )
        );
        return $response;
      }
  }


  public function getEmployee($xml)
  {
      $xmlModelCorrect = '<Interfaz602Resp>
                          <UNICO>
                          <EMP_NOMBRE>JUAN</EMP_NOMBRE>
                          <EMP_APELLIDO1>PEPE</EMP_APELLIDO1>
                          <EMP_APELLIDO2>BOTELLAS</EMP_APELLIDO2>
                          <EMP_TIPO_IDENTIF>CC</EMP_TIPO_IDENTIF>
                          <EMP_CEDULA>102034520</EMP_CEDULA>
                          <EMP_SEXO>MAS</EMP_SEXO>
                          <EMP_FECHA_NACI>1990-08-04</EMP_FECHA_NACI>
                          <EMP_FECHA_INGRESO>2015-01-01</EMP_FECHA_INGRESO>
                          <EMP_ANTIGUEDAD_ANT/>
                          <EMP_FECHA_INI_CONTRATO>2015-01-01</EMP_FECHA_INI_CONTRATO>
                          <EMP_NRO_CONTRATO/>
                          <EMP_FECHA_FIN_CONTRATO/>
                          <EMP_JORNADA>1</EMP_JORNADA>
                          <EMP_HORAS_TRAB>8</EMP_HORAS_TRAB>
                          <EMP_FORMA_PAGO>CON</EMP_FORMA_PAGO>
                          <EMP_TIPOLIQ>M</EMP_TIPOLIQ>
                          <EMP_TIPO_SALARIO>1</EMP_TIPO_SALARIO>
                          </UNICO>
                          <InfoProceso>
                          <MensajeRetorno/>
                          <LogProceso>
                          ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                          </LogProceso>
                          </InfoProceso>
                          </Interfaz602Resp>';
    $xmlModelIncorrect = '<Interfaz602Resp>
                          <InfoProceso>
                          <MensajeRetorno>
                          Se ejecuta la sentencia antes del cargue - Kic_Adm_Ice.Pic_Proc_Int_SW_Publ - Se ejecuta la sentencia antes del cargue - Kic_Adm_Ice.Pic_Adm_Proc_Int_Tabl -
                          <ERRORQ>505</ERRORQ>
                          .
                          </MensajeRetorno>
                          <LogProceso>
                          ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados TOTALES Registros leídos Registros buenos Registros rechazados 0 0 0 ---------------------------------------------------------------------------------------- ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados TOTALES Registros leídos Registros buenos Registros rechazados 0 0 0 ----------------------------------------------------------------------------------------
                          </LogProceso>
                          </InfoProceso>
                          </Interfaz602Resp>';
                  $destination = array();
      $parsed = new \SimpleXMLElement($xml);
      $components = array();
      foreach ($parsed as $element) {
        foreach($element as $key => $val) {
         $components[$key] = (String)$val;
        }
      }

      if($components['EMPCODIGO'] == 123456789)
      {
          $response = new Response(
              $xmlModelCorrect,
              Response::HTTP_OK,
              array(
                  'Content-Type' => 'application/xml',
              )
          );
          return $response;
      } else
      {
        $response = new Response(
            $xmlModelIncorrect,
            Response::HTTP_OK,
            array(
                'Content-Type' => 'application/xml',
            )
        );
        return $response;
      }
  }

}
