<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NoveltyRestController extends FOSRestController
{
  /**
   * Return the novelty types<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Returns the list of novelty types",
   *   statusCodes = {
   *     200 = "Returned when successful"
   *   }
   * )
   *
   * @return View
   */
   public function getNoveltyTypesAction() {
     $novletyRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NoveltyType");
     $novleties = $novletyRepo->findAll();
     $view = View::create();

     return $view->setStatusCode(200)->setData(array('novelties'=>$novleties));
   }

    /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Returned when the notification id doesn't exists "
     *   }
     * )
     *
     * @param $idNovelty
     * @return View
     * @internal param $idNotification
     */
    public function getAddNoveltySqlAction($idNovelty)
    {

        $em=$this->getDoctrine();
        $noveltyRepo=$em->getRepository("RocketSellerTwoPickBundle:Novelty");
        /** @var Novelty $novelty */
        $novelty=$noveltyRepo->find($idNovelty);
        $view = View::create();
        $request = $this->container->get('request');
        $noveltyType=$novelty->getNoveltyTypeNoveltyType();
        $idEmployerHasEmployee=$novelty->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee();
        if($noveltyType->getAbsenteeism()==null){
            if($noveltyType->getPayrollCode()==150||$noveltyType->getPayrollCode()==145){
                $methodToCall="postAddVacationParameters";
                $request->request->add(array(
                    "employee_id"=>$idEmployerHasEmployee,
                    "money_days"=>$novelty->getAmount(),
                    "number_days"=>$novelty->getUnits(),
                    "exit_date"=>$novelty->getDateStart()->format("d-m-Y"),
                ));
            }else{
                $dateToday= new DateTime();
                $methodToCall="postAddNoveltyEmployee";
                if($noveltyType->getName()=="Anticipo"){
                  $novelty->setUnits("1");
                }
                $request->request->add(array(
                    "employee_id"=>$idEmployerHasEmployee,
                    "novelty_concept_id"=>$noveltyType->getPayrollCode(),
                    "novelty_value"=>$novelty->getAmount(),
                    "unity_numbers"=>$novelty->getUnits(),
                    "novelty_start_date"=>$novelty->getDateStart()?$novelty->getDateStart()->format("d-m-Y"):$dateToday->format("d-m-Y"),
                    "novelty_end_date"=>$novelty->getDateEnd()?$novelty->getDateEnd()->format("d-m-Y"):null,
                ));
            }
            $request->setMethod("POST");


            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:'.$methodToCall, array('_format' => 'json'));
            if($insertionAnswer->getStatusCode()!=200){
                return $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array("error"=>"No se pudo agregar la novedad"));
            }
            return $view->setStatusCode(201);

        }else{
            $methodToCall="postAddAbsenteeismEmployee";
            $request->setMethod("POST");
            $request->request->add(array(
                "employee_id"=>$idEmployerHasEmployee,
                "absenteeism_type_id"=>$noveltyType->getAbsenteeism(),
                "absenteeism_state"=>"ACT",
                "absenteeism_units"=>$novelty->getUnits(),
                "absenteeism_start_date"=>$novelty->getDateStart()->format("d-m-Y"),
                "absenteeism_end_date"=>$novelty->getDateEnd()->format("d-m-Y"),
            ));


            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:'.$methodToCall, array('_format' => 'json'));
            if($insertionAnswer->getStatusCode()!=200){
                return $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array("error"=>"No se pudo agregar la novedad"));
            }

            return $view->setStatusCode(201);
        }
    }

    /**
     * Get the validation errors
     *
     *
     * @param string $dateStart format YYYY-MM-DD
     * @param int $days
     * @return View data date YYYY-MM-DD
     *
     */
    public function getWorkableDaysToDateAction($dateStart, $days)
    {
        $view = View::create();
        $wkd=array();
        $wkd[5]=true;
        $wkd[4]=true;
        $wkd[3]=true;
        $wkd[2]=true;
        $wkd[1]=true;
        $dateRStart= new DateTime($dateStart);
        $numberDays=intval($days);
        $days=[];
        $dateToCheck=new DateTime();
        $i=$j=0;
        while($j<$numberDays){
            $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);
            if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                $days[]=$dateToCheck->format("Y-m-d");
                $j++;
            }
            $i++;

        }
        $view->setStatusCode(200)->setData(array("date"=>$dateToCheck->format("Y-m-d")));
        return $view;
    }
    /**
     * Get the validation errors
     *
     *
     * @param $dateStart
     * @param $dateEnd
     * @param int $contractId
     * @param int $payrollId
     * @Route(defaults={"contractId"="-1","payrollId"="-1"})
     *
     * @return View
     */
    public function getValidVacationDaysContractAction($dateStart, $dateEnd,$contractId,$payrollId)
    {
        //datetime format YYYY-mm-dd
        $em=$this->getDoctrine()->getManager();
        if($contractId==-1){
            $payrollRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
            /** @var Payroll $realPayroll */
            $realPayroll=$payrollRepo->find($payrollId);
            $realContract=$realPayroll->getContractContract();
        }else{
            $contractRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract");
            /** @var Contract $realContract */
            $realContract=$contractRepo->find($contractId);
        }

        $wkd=array();
        if($realContract->getTimeCommitmentTimeCommitment()->getCode()=="TC"){
            $wkd[6]=true;
            $wkd[5]=true;
            $wkd[4]=true;
            $wkd[3]=true;
            $wkd[2]=true;
            $wkd[1]=true;
        }else{
            $weekWorkableDays=$realContract->getWeekWorkableDays();
            /** @var WeekWorkableDays $wwd */
            foreach ($weekWorkableDays as $wwd) {
                $wkd[$wwd->getDayNumber()]=true;
            }
        }
        $dateRStart= new DateTime($dateStart);
        $dateREnd= new DateTime($dateEnd);
        $dateREnd->modify('+1 day');
        $interval=$dateRStart->diff($dateREnd);
        $answer=0;
        $numberDays=$interval->format("%a");
        $days=[];
        for($i=0;$i<$numberDays;$i++){
            $dateToCheck=new DateTime();
            $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);

            if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                $answer++;
                $days[]=$dateToCheck->format("Y-m-d");
            }

        }
        $view = View::create();
        $view->setStatusCode(200)->setData(array("days"=>$answer,"dateToCheck"=>$days,"wkd"=>$wkd));

        return $view;
    }

    /**
     * Get the validation errors
     *
     *
     * @param $dateStart
     * @param $dateEnd
     *
     * @return View
     */
    public function getWorkableDaysBetweenDatesAction($dateStart, $dateEnd)
    {
        //datetime format YYYY-mm-dd
        $em=$this->getDoctrine()->getManager();

        $wkd=array();
        $wkd[5]=true;
        $wkd[4]=true;
        $wkd[3]=true;
        $wkd[2]=true;
        $wkd[1]=true;
        $dateRStart= new DateTime($dateStart);
        $dateREnd= new DateTime($dateEnd);
        $mult=1;
        if($dateRStart>$dateREnd){
            $dateRStart = $dateREnd;
            $dateREnd = new DateTime($dateStart);
            $mult = -1;
        }

        $dateRStart->modify('+1 day');
        $dateREnd->modify('+1 day');
        $interval=$dateRStart->diff($dateREnd);
        $answer=0;
        $numberDays=$interval->format("%a");
        $days=[];
        for($i=0;$i<$numberDays;$i++){
            $dateToCheck=new DateTime();
            $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);

            if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                $answer++;
                $days[]=$dateToCheck->format("Y-m-d");
            }

        }
        $view = View::create();
        $view->setStatusCode(200)->setData(array("days"=>$answer*$mult,"dateToCheck"=>$days,"wkd"=>$wkd));

        return $view;
    }

    /**
     * @param DateTime $dateToCheck
     * @return bool
     */
    private function workable ($dateToCheck){
        $holyDays=array();
        $date=new DateTime("2016-"."01"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."01"."-"."11");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."21");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."24");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."03"."-"."25");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."09");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."05"."-"."30");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."06"."-"."06");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."07"."-"."04");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."07"."-"."20");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."08"."-"."07");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."08"."-"."15");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."10"."-"."17");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."11"."-"."07");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."11"."-"."14");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."12"."-"."08");
        $holyDays[]=$date;
        $date=new DateTime("2016-"."12"."-"."25");
        $holyDays[]=$date;
        /** @var DateTime $hd */
        foreach ($holyDays as $hd) {
            if($dateToCheck->format("Y-m-d")==$hd->format("Y-m-d"))
                return false;
        }

        return true;
    }

    /**
     * Post correct noveltytypesHasDocumentType
     * @ApiDoc(
     *   resource = true,
     *   description = "Correct the database documents asigned to eah novelty type",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *   }
     * )
     *
     * @return View
     */
    public function postCorrectNoveltyDocumentsAction()
    {
        $response ="";
        $response = $response . " CORRIGIENDO DOCUMENTOS ASOCIADOS A LAS NOVEDADES...<br><br>";
        $em = $this->getDoctrine()->getManager();
        /** @var NoveltyTypeHasDocumentType $nTHDT */

        //LICENCIA DE MATERNIDAD
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(1);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>25)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'RCNV')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(2);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>25)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'LDM')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(3);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>25)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'HCDE')));
        $em->persist($nTHDT);

        //LICENCIA DE PATERNIDAD
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(4);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>26)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'RCNV')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(5);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>26)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'LDM')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(6);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>26)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'LDP')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(7);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>26)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'HCDE')));
        $em->persist($nTHDT);

        //LICENCIA NO REMUNERADA
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(8);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>3120)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'LNRE')));
        $em->persist($nTHDT);

        //LICENCIA REMUNERADA
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(9);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>23)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'LREM')));
        $em->persist($nTHDT);

        //SUSPENSION
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(10);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>3125)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'SUSP')));
        $em->persist($nTHDT);

        //INCAPACIDAD GENERAL
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(11);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>15)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'HCDE')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(12);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>15)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'INC')));
        $em->persist($nTHDT);

        //ACCIDENTE DE TRABAJO
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(13);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>27)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'RADT')));
        $em->persist($nTHDT);

        //INCAPACIDAD LABORAL
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(14);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>28)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'HCDE')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(15);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>28)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'INCP')));
        $em->persist($nTHDT);
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(16);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>28)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'RADT')));
        $em->persist($nTHDT);

        //AUMENTO DE SUELDO --

        //BONIFICACION HIDDEN --

        //RECARGO NOCTURNO --

        //RECARGO NOCTURNO FESTIVO --

        //HORA EXTRA DIURNA --

        //HORA EXTRA NOCTURNA --

        //HORA EXTRA FESTIVA DIURNA --

        //FESTIVO DIURNO --

        //HORA EXTRA FESTIVA NOCTURNA --

        //SUBSIDIO DE TRANSPORTE --

        //VACACIONES
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(17);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>145)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'SDV')));
        $em->persist($nTHDT);

        //VACACIONES EN DINERO
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(18);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>150)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'SDVD')));
        $em->persist($nTHDT);

        //BONIFICACIÓN --

        //PRESTAMO
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(19);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("name"=>'Prestamo')));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'ADES')));
        $em->persist($nTHDT);

        //APORTES SALUD --

        //APORTES PENSION --

        //PRIMA LEGAL --

        //CESANTIAS DEFINITIVAS --

        //INTERESES SOBRE SESANTIAS --

        //INDEMNIZACION --

        //SUELDO --

        //GASTO DE INCAPACIDAD --

        //AJUSTE DE INCAPACIDAD --

        //RETENCION EN LA FUENTE --

        //LLEGADA TARDE
        $nT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array('name'=>'Llegada tarde'));
        $nT->setPayrollCode(-1);
        $em->persist($nT);
        $em->flush();
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(20);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>-1)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'DSN')));
        $em->persist($nTHDT);

        //ABANDONO PUESTO DE TRABAJO
        $nT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array('name'=>'Abandono puesto de trabajo'));
        $nT->setPayrollCode(-2);
        $em->persist($nT);
        $em->flush();
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(21);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>-2)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'DSN')));
        $em->persist($nTHDT);

        //VERSION LIBRE DE HECHOS
        $nT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array('name'=>'Versión libre de hechos'));
        $nT->setPayrollCode(-3);
        $em->persist($nT);
        $em->flush();
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(22);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>-3)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'DSN')));
        $em->persist($nTHDT);

        //DOTACION
        $nT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array('name'=>'Dotación'));
        $nT->setPayrollCode(-4);
        $em->persist($nT);
        $em->flush();
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(23);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("payroll_code"=>-4)));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'DOT')));
        $em->persist($nTHDT);

        //TERMINAR CONTRATO --

        //ANTICIPO
        $nTHDT = $em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(24);
        $nTHDT->setNoveltyTypeNoveltyType($em->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array("name"=>'Anticipo')));
        $nTHDT->setDocumentTypeDocumentType($em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array("docCode"=>'ADES')));
        $em->persist($nTHDT);
        $em->remove($em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(25));
        $em->remove($em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(26));
        $em->remove($em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(27));
        $em->remove($em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(28));
        $em->remove($em->getRepository("RocketSellerTwoPickBundle:NoveltyTypeHasDocumentType")->find(29));

        $response = $response . " FINALIZADO.<br><br>";
        $em->flush();
        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;

    }

}
?>
