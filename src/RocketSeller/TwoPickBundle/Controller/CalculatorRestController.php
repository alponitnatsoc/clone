<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\CalculatorConstraints;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
class CalculatorRestController extends FOSRestController
{
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="type", nullable=true, requirements="((days)|(complete))", strict=true, description="tipo de pago mensual o por dias.")
     * @RequestParam(name="salaryM", nullable=true, strict=true, description="documentType.")
     * @RequestParam(name="salaryD", nullable=true, strict=true, description="document.")
     * @RequestParam(name="numberOfDays", nullable=true, requirements="\d+", strict=true, description="names.")
     * @RequestParam(name="transport", nullable=false, requirements="([1|0])", strict=true, description="last Name 1.")
     * @RequestParam(name="aid", nullable=false, requirements="([1|0])", strict=true, description="last Name 2.")
     * @RequestParam(name="aidD", nullable=true, strict=true, description="year.")
     * @RequestParam(name="sisben", nullable=true, requirements="([1|0])", strict=true, description="year.")
     * @return View
     */
    public function postCalculatorSubmitAction(ParamFetcher $paramFetcher)
    {
        $calcRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:CalculatorConstraints');
        /** @var ArrayCollection $calculatorConstrains */
        $calculatorConstrains=$calcRepo->findBy(array('employeeContractTypeEmployeeContractType'=>'1'));
        $constraints=array();
        /** @var CalculatorConstraints $cal */
        foreach($calculatorConstrains as $cal){
            $constraints[$cal->getName()]=$cal->getValue();
        }
        $view = View::create();
        $type=$paramFetcher->get("type");
        $salaryM=$paramFetcher->get("salaryM");
        $salaryD=$paramFetcher->get("salaryD");
        $numberOfDays=$paramFetcher->get("numberOfDays");
        $transport=$paramFetcher->get("transport");
        $aid=$paramFetcher->get("aid");
        $aidD=$paramFetcher->get("aidD");
        $sisben=$paramFetcher->get("sisben");
        //Extract Constraints
        $transportAid=$transportAid=$constraints['auxilio transporte'];
        $smmlv=$constraints['smmlv'];
        $EPSEmployer=$constraints['eps empleador'];
        $EPSEmployee=$constraints['eps empleado'];
        $PensEmployer=$constraints['pension empleador'];
        $PensEmployee=$constraints['pension empleado'];
        $arl=$constraints['arl'];
        $caja=$constraints['caja'];
        $sena=$constraints['sena'];
        $icbf=$constraints['icbf'];
        $vacations=$constraints['vacaciones'];
        $taxCes=$constraints['intereses cesantias'];
        $ces=$constraints['cesantias'];
        $dotation=$constraints['dotacion'];
        $transportAidDaily=$transportAid/30;
        $vacations30D=$vacations/30;
        $dotationDaily=$dotation/30;

        $totalExpenses=0;
        $totalIncome=0;
        $EPSEmployerCal=0;
        $EPSEmployeeCal=0;
        $PensEmployeeCal=0;
        $PensEmployerCal=0;
        $transportCal=0;
        $cesCal=0;
        $taxCesCal=0;
        $dotationCal=0;
        $vacationsCal=0;
        $arlCal=0;
        $cajaCal=0;
        $senaCal=0;
        $icbfCal=0;
        if($aid==0){
            $aidD=0;
        }
        if($type=="days"){
            if($transport==1){
                $salaryD-=$transportAidDaily;
            }
            //if it overpass the SMMLV calculates as a full time job  or
            //if does not belongs to SISBEN
            if((($salaryD+$transportAidDaily+$aidD)*$numberOfDays)>$smmlv||$sisben==0){
                if((($salaryD+$transportAidDaily+$aidD)*$numberOfDays)>$smmlv){
                    $base=($salaryD+$aidD)*$numberOfDays;
                }else{$base=$smmlv;}

                $totalExpenses=(($salaryD+$aidD+$transportAidDaily+$dotationDaily)*$numberOfDays)+(($EPSEmployer+$PensEmployer+$arl+$caja+$sena+$icbf)*$base)+($vacations30D*$numberOfDays*$salaryD)+(($taxCes+$ces)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid));
                $EPSEmployerCal=$EPSEmployer*$base;
                $EPSEmployeeCal=$EPSEmployee*$base;
                $PensEmployerCal=$PensEmployer*$base;
                $PensEmployeeCal=$PensEmployee*$base;
                $arlCal=$arl*$base;
                $cesCal=(($ces)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid));
                $taxCesCal=(($taxCes)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid));
                $cajaCal=$caja*$base;
                $vacationsCal=$vacations30D*$numberOfDays*$salaryD;
                $transportCal=$transportAidDaily*$numberOfDays;
                $dotationCal=$dotationDaily*$numberOfDays;
                $senaCal=$sena*$base;
                $icbfCal=$icbf*$base;
                $totalIncome=($salaryD*$numberOfDays)-$EPSEmployerCal-$PensEmployerCal;
            }else{
                $EPSEmployee=0;
                $EPSEmployer=0;
                $base=$smmlv;
                //calculate the caja and pens in base of worked days
                if($numberOfDays<=7){
                    $PensEmployerCal=$PensEmployer*$base/4;
                    $PensEmployeeCal=$PensEmployee*$base/4;
                    $cajaCal=$caja*$base;
                }else if($numberOfDays<=14){
                    $PensEmployerCal=$PensEmployer*$base/2;
                    $PensEmployeeCal=$PensEmployee*$base/2;
                    $cajaCal=$caja*$base/2;
                }else if($numberOfDays<=21){
                    $PensEmployerCal=$PensEmployer*$base*3/4;
                    $PensEmployeeCal=$PensEmployee*$base*3/4;
                    $cajaCal=$caja*$base*3/4;
                }else {
                    $PensEmployerCal=$PensEmployer*$base;
                    $PensEmployeeCal=$PensEmployee*$base;
                    $cajaCal=$caja*$base;
                }
                //then calculate arl ces and the rest
                $totalExpenses=(($salaryD+$aidD+$transportAidDaily+$dotationDaily)*$numberOfDays)+(($EPSEmployer+$arl+$sena+$icbf)*$base)+($vacations30D*$numberOfDays*$salaryD)+(($taxCes+$ces)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid))+$PensEmployeeCal+$cajaCal+$PensEmployerCal;
                $EPSEmployerCal=$EPSEmployer*$base;
                $EPSEmployeeCal=$EPSEmployee*$base;
                $arlCal=$arl*$base;
                $cesCal=(($ces)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid));
                $taxCesCal=(($taxCes)*((($salaryD+$aidD)*$numberOfDays*30/28)+$transportAid));
                $vacationsCal=$vacations30D*$numberOfDays*$salaryD;
                $transportCal=$transportAidDaily*$numberOfDays;
                $dotationCal=$dotationDaily*$numberOfDays;
                $senaCal=$sena*$base;
                $icbfCal=$icbf*$base;
                $totalIncome=(($salaryD+$transportAidDaily)*$numberOfDays)-$PensEmployeeCal;
            }

        }else{
            if($transport==1){
                $salaryM-=$transportAid;
            }else if($salaryM+$aidD>$smmlv*2){
                $transportAid=0;
            }

            $totalExpenses=$salaryM+$aidD+$transportAid+$dotation+(($EPSEmployer+$PensEmployer+$arl+$caja+$vacations30D+$sena+$icbf)*($salaryM+$aidD))+(($taxCes+$ces)*($salaryM+$aidD+$transportAid));
            $EPSEmployerCal=$EPSEmployer*($salaryM+$aidD);
            $EPSEmployeeCal=$EPSEmployee*($salaryM+$aidD);
            $PensEmployerCal=$PensEmployer*($salaryM+$aidD);
            $PensEmployeeCal=$PensEmployee*($salaryM+$aidD);
            $arlCal=$arl*($salaryM+$aidD);
            $cesCal=$ces*($salaryM+$aidD+$transportAid);
            $taxCesCal=$taxCes*($salaryM+$aidD+$transportAid);
            $cajaCal=$caja*($salaryM+$aidD);
            $vacationsCal=$vacations30D*($salaryM+$aidD);
            $transportCal=$transportAid;
            $dotationCal=$dotation;
            $senaCal=$sena*($salaryM+$aidD);
            $icbfCal=$icbf*($salaryM+$aidD);
            $totalIncome=($salaryM+$transportCal-$EPSEmployerCal-$PensEmployerCal);

        }
        $resposne=array();
        $resposne['totalExpenses']=$totalExpenses;
        $resposne['EPSEmployerCal']=$EPSEmployerCal;
        $resposne['EPSEmployeeCal']=$EPSEmployeeCal;
        $resposne['PensEmployerCal']=$PensEmployerCal;
        $resposne['PensEmployeeCal']=$PensEmployeeCal;
        $resposne['arlCal']=$arlCal;
        $resposne['cesCal']=$cesCal;
        $resposne['taxCesCal']=$taxCesCal;
        $resposne['cajaCal']=$cajaCal;
        $resposne['vacationsCal']=$vacationsCal;
        $resposne['transportCal']=$transportCal;
        $resposne['dotationCal']=$dotationCal;
        $resposne['senaCal']=$senaCal;
        $resposne['icbfCal']=$icbfCal;
        $resposne['totalIncome']=$totalIncome;
        $view->setData($resposne )->setStatusCode(200);
        return $view;


    }
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *   }
     * )
     *
     * @return View
     */
    public function getCalculatorConstraintsAction(){
        $calcRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:CalculatorConstraints');
        /** @var ArrayCollection $calculatorConstrains */
        $calculatorConstrains=$calcRepo->findBy(array('employeeContractTypeEmployeeContractType'=>'1'));
        $constraints=array();
        /** @var CalculatorConstraints $cal */
        foreach($calculatorConstrains as $cal){
            $constraints[$cal->getName()]=$cal->getValue();
        }
        $view = View::create();
        $view->setData(array("response"=>$constraints))->setStatusCode(200);
       return $view;
    }
}
 ?>