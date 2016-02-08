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
    private function setHeaders()
    {
        $header = array();
        $header['x-channel'] = 'WEB';
        $header['x-country'] = 'CO';
        $header['language'] = 'es';
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
        $codigo_interfaz = $request->query->get('inInexCod');
        $xml = $request->query->get('clXMLSolic');
        if ($codigo_interfaz == 601)
            return $this->addEmployee($xml);
        if ($codigo_interfaz == 602)
            return $this->getEmployee($xml);
        if ($codigo_interfaz == 616)
            return $this->getGeneralPayroll($xml);
        if ($codigo_interfaz == 606)
            return $this->getConceptosFijos($xml);
        if ($codigo_interfaz == 620)
            return $this->AddFinalLiquidation($xml);
        if ($codigo_interfaz == 611)
            return $this->ProcessFinalLiquidation($xml);
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
            foreach ($element as $key => $val) {
                // echo "___{$key}____: ***{$val}***";
                $components[$key] = (String) $val;
            }
        }
        //die(print_r($components, true));

        if ($components['EMP_CODIGO'] == 123456789) {
            $response = new Response(
                    $xmlModelCorrect, Response::HTTP_OK, array(
                'Content-Type' => 'application/xml',
                    )
            );
            return $response;
        } else {
            $response = new Response(
                    $xmlModelIncorrect, Response::HTTP_OK, array(
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
            foreach ($element as $key => $val) {
                $components[$key] = (String) $val;
            }
        }

        if ($components['EMPCODIGO'] == 123456789) {
            $response = new Response(
                    $xmlModelCorrect, Response::HTTP_OK, array(
                'Content-Type' => 'application/xml',
                    )
            );
            return $response;
        } else {
            $response = new Response(
                    $xmlModelIncorrect, Response::HTTP_OK, array(
                'Content-Type' => 'application/xml',
                    )
            );
            return $response;
        }
    }

    public function getConceptosFijos($xml)
    {
        $xmlModelCorrect = '<Interfaz606Resp>
                          <UNICO>
                          <EMP_CODIGO>333333333</EMP_CODIGO>
                          <CON_CODIGO>1 </CON_CODIGO>
                          <COF_VALOR>689454</COF_VALOR>
                          </UNICO>
                          <InfoProceso>
                          <MensajeRetorno/>
                          <LogProceso>
                          ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                          </LogProceso>
                          </InfoProceso>
                          </Interfaz606Resp>';
        $xmlModelCorrect2 = '<Interfaz606Resp>
                          <UNICO>
                          <EMP_CODIGO>123456789</EMP_CODIGO>
                          <CON_CODIGO>1 </CON_CODIGO>
                          <COF_VALOR>2500000</COF_VALOR>
                          </UNICO>
                          <InfoProceso>
                          <MensajeRetorno/>
                          <LogProceso>
                          ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                          </LogProceso>
                          </InfoProceso>
                          </Interfaz606Resp>';

        $destination = array();
        $parsed = new \SimpleXMLElement($xml);
        $components = array();
        foreach ($parsed as $element) {
            foreach ($element as $key => $val) {
                $components[$key] = (String) $val;
            }
        }

        if ($components['EMPCODIGO'] != 123456789) {
            $response = new Response(
                    $xmlModelCorrect, Response::HTTP_OK, array(
                'Content-Type' => 'application/xml',
                    )
            );
            return $response;
        } else {
            $response = new Response(
                    $xmlModelCorrect2, Response::HTTP_OK, array(
                'Content-Type' => 'application/xml',
                    )
            );
            return $response;
        }
    }

    public function getSalary($document)
    {
        $personRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Person");
        /** @var Person $person */
        $person = $personRepo->findOneByDocument($document);
        if($person == null)
          return 689455;
        $ehE = $person->getEmployee()->getEmployeeHasEmployers();

        /** @var EmployerHasEmployee $ehEs */
        foreach ($ehE as $ehEs) {
            if ($ehEs->getState() == 'Active') {
                $contracts = $ehEs->getContracts();
                /** @var Contract    $contract */
                foreach ($contracts as $contract) {
                    if ($contract->getState() == 'Active') {
                        return $contract->getSalary();
                    }
                }
            }
        }
        return 689455;
    }

    public function getGeneralPayroll($xml)
    {
        $destination = array();
        $parsed = new \SimpleXMLElement($xml);
        $components = array();
        foreach ($parsed as $element) {
            foreach ($element as $key => $val) {
                $components[$key] = (String) $val;
            }
        }

        $xmlModelCorrect2 = '<Interfaz616Resp>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>05</NOMI_MES>
            <NOMI_ANO>2015</NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>15-11-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>' . $this->getSalary($components['EMPCODIGO']) . '</NOMI_VALOR_LOCAL>
            <CON_CODIGO>1</CON_CODIGO>
            <NOMI_VALOR>' . $this->getSalary($components['EMPCODIGO']) . '</NOMI_VALOR>
            <NOMI_BASE>0 </NOMI_BASE>
            <NOMI_UNIDADES>1</NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>545</NOV_CONSEC>
          </UNICO>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>05</NOMI_MES>
            <NOMI_ANO>2015</NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR_LOCAL>
            <CON_CODIGO>3010</CON_CODIGO>
            <NOMI_VALOR>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR>
            <NOMI_BASE>0 </NOMI_BASE>
            <NOMI_UNIDADES>1 </NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>546</NOV_CONSEC>
          </UNICO>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>05</NOMI_MES>
            <NOMI_ANO>2015</NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR_LOCAL>
            <CON_CODIGO>3020</CON_CODIGO>
            <NOMI_VALOR>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR>
            <NOMI_BASE>0 </NOMI_BASE>
            <NOMI_UNIDADES>1 </NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>547</NOV_CONSEC>
          </UNICO>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>05</NOMI_MES>
            <NOMI_ANO>2015</NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>150000 </NOMI_VALOR_LOCAL>
            <CON_CODIGO>3120</CON_CODIGO>
            <NOMI_VALOR>150000</NOMI_VALOR>
            <NOMI_BASE>0 </NOMI_BASE>
            <NOMI_UNIDADES>1 </NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>548</NOV_CONSEC>
          </UNICO>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . ' </EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>06</NOMI_MES>
            <NOMI_ANO>2015 </NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>09-12-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>20000 </NOMI_VALOR_LOCAL>
            <CON_CODIGO>4810</CON_CODIGO>
            <NOMI_VALOR>20000</NOMI_VALOR>
            <NOMI_BASE>0</NOMI_BASE>
            <NOMI_UNIDADES>1</NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>549</NOV_CONSEC>
          </UNICO>
          <UNICO>
            <EMP_CODIGO>' . $components['EMPCODIGO'] . ' </EMP_CODIGO>
            <NOMI_PERIODO>4</NOMI_PERIODO>
            <NOMI_MES>06</NOMI_MES>
            <NOMI_ANO>2015 </NOMI_ANO>
            <PROC_CODIGO>1</PROC_CODIGO>
            <NOMI_FECHA_PAGO>09-12-2015</NOMI_FECHA_PAGO>
            <NOMI_VALOR_LOCAL>400000 </NOMI_VALOR_LOCAL>
            <CON_CODIGO>145</CON_CODIGO>
            <NOMI_VALOR>400000</NOMI_VALOR>
            <NOMI_BASE>0</NOMI_BASE>
            <NOMI_UNIDADES>1</NOMI_UNIDADES>
            <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
            <NOV_CONSEC>550</NOV_CONSEC>
          </UNICO>
          <InfoProceso>
          <MensajeRetorno/>
          <LogProceso>
          ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
          </LogProceso>
          </InfoProceso>
          </Interfaz616Resp>';


          $xmlModelLiquidation = '<Interfaz616Resp>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>15-11-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>' . $this->getSalary($components['EMPCODIGO']) . '</NOMI_VALOR_LOCAL>
              <CON_CODIGO>1</CON_CODIGO>
              <NOMI_VALOR>' . $this->getSalary($components['EMPCODIGO']) . '</NOMI_VALOR>
              <NOMI_BASE>0 </NOMI_BASE>
              <NOMI_UNIDADES>1</NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
              <NOV_CONSEC>545</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR_LOCAL>
              <CON_CODIGO>3010</CON_CODIGO>
              <NOMI_VALOR>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR>
              <NOMI_BASE>0 </NOMI_BASE>
              <NOMI_UNIDADES>1 </NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
              <NOV_CONSEC>546</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR_LOCAL>
              <CON_CODIGO>3020</CON_CODIGO>
              <NOMI_VALOR>' . ( ($this->getSalary($components['EMPCODIGO']) * 4) / 100 ) . '</NOMI_VALOR>
              <NOMI_BASE>0 </NOMI_BASE>
              <NOMI_UNIDADES>1 </NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
              <NOV_CONSEC>547</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>180000 </NOMI_VALOR_LOCAL>
              <CON_CODIGO>3120</CON_CODIGO>
              <NOMI_VALOR>180000</NOMI_VALOR>
              <NOMI_BASE>0 </NOMI_BASE>
              <NOMI_UNIDADES>1 </NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>180000</NOMI_VALOR_LOCAL>
              <CON_CODIGO>3120</CON_CODIGO>
              <NOMI_VALOR>180000</NOMI_VALOR>
              <NOMI_BASE>0 </NOMI_BASE>
              <NOMI_UNIDADES>1 </NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015 </NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>1000000</NOMI_VALOR_LOCAL>
              <CON_CODIGO>130</CON_CODIGO>
              <NOMI_VALOR>1000000</NOMI_VALOR>
              <NOMI_BASE>0</NOMI_BASE>
              <NOMI_UNIDADES>1</NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015</NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>2000000</NOMI_VALOR_LOCAL>
              <CON_CODIGO>185</CON_CODIGO>
              <NOMI_VALOR>2000000</NOMI_VALOR>
              <NOMI_BASE>0</NOMI_BASE>
              <NOMI_UNIDADES>1</NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015</NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>100000</NOMI_VALOR_LOCAL>
              <CON_CODIGO>190</CON_CODIGO>
              <NOMI_VALOR>100000</NOMI_VALOR>
              <NOMI_BASE>0</NOMI_BASE>
              <NOMI_UNIDADES>1</NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015</NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <UNICO>
              <EMP_CODIGO>' . $components['EMPCODIGO'] . '</EMP_CODIGO>
              <NOMI_PERIODO>4</NOMI_PERIODO>
              <NOMI_MES>05</NOMI_MES>
              <NOMI_ANO>2015</NOMI_ANO>
              <PROC_CODIGO>1</PROC_CODIGO>
              <NOMI_FECHA_PAGO>11-12-2015</NOMI_FECHA_PAGO>
              <NOMI_VALOR_LOCAL>100000</NOMI_VALOR_LOCAL>
              <CON_CODIGO>190</CON_CODIGO>
              <NOMI_VALOR>100000</NOMI_VALOR>
              <NOMI_BASE>0</NOMI_BASE>
              <NOMI_UNIDADES>1</NOMI_UNIDADES>
              <NOMI_FECHA_NOV>12-12-2015</NOMI_FECHA_NOV>
              <NOV_CONSEC>548</NOV_CONSEC>
            </UNICO>
            <InfoProceso>
            <MensajeRetorno/>
            <LogProceso>
            ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
            </LogProceso>
            </InfoProceso>
            </Interfaz616Resp>';


        if (substr($components['EMPCODIGO'], -1) == "9") {
          $response = new Response(
                  $xmlModelLiquidation, Response::HTTP_OK, array(
              'Content-Type' => 'application/xml',
                  )
          );
          return $response;
        } else {
          $response = new Response(
                  $xmlModelCorrect2, Response::HTTP_OK, array(
              'Content-Type' => 'application/xml',
                  )
          );
          return $response;
        }
    }

    public function addFinalLiquidation($xml)
    {
        $xmlModelCorrect = '<InfoProceso>
                            <MensajeRetorno/>
                            <LogProceso>
                            ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                            </LogProceso>
                            </InfoProceso>';
        $destination = array();
        $parsed = new \SimpleXMLElement($xml);
        $components = array();
        foreach ($parsed as $element) {
            foreach ($element as $key => $val) {
                // echo "___{$key}____: ***{$val}***";
                $components[$key] = (String) $val;
            }
        }
        //die(print_r($components, true));
        $response = new Response(
                $xmlModelCorrect, Response::HTTP_OK, array(
            'Content-Type' => 'application/xml',
                )
        );return $response;

    }

    public function ProcessFinalLiquidation($xml)
    {
        $xmlModelCorrect = '<InfoProceso>
                            <MensajeRetorno/>
                            <LogProceso>
                            ---------------------------------------------------------------------------------------- ESTADÍSTICAS ---------------------------------------------------------------------------------------- Tipo de registro Registros leídos Registros buenos Registros rechazados 1 1 1 0 TOTALES Registros leídos Registros buenos Registros rechazados 1 1 0 ----------------------------------------------------------------------------------------
                            </LogProceso>
                            </InfoProceso>';
        $destination = array();
        $parsed = new \SimpleXMLElement($xml);
        $components = array();
        foreach ($parsed as $element) {
            foreach ($element as $key => $val) {
                // echo "___{$key}____: ***{$val}***";
                $components[$key] = (String) $val;
            }
        }
        //die(print_r($components, true));
        $response = new Response(
                $xmlModelCorrect, Response::HTTP_OK, array(
            'Content-Type' => 'application/xml',
                )
        );return $response;

    }

}
