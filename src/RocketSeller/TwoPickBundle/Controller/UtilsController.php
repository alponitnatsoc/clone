<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Validator\Constraints\Date;

class UtilsController extends ContainerAware
{
    public function __construct( $container=null)
    {
        if($container)
            $this->setContainer($container);
    }
    public function getAllStrings($str){
        $strs = array();
        $strs[]=$str;
        if(count(explode('a',$str))>1){
            if(count(explode('a',$str))>2){
                $count = count(explode('a',$str));
                $sbstr = explode('a',$str);
                $fstr2 = $sbstr[0];
                for($i = 1; $i<$count;$i++){
                    for($j = 1; $j<$count;$j++){
                        if($j==$i){
                            $fstr2.='á'.$sbstr[$j];
                        }else{
                            $fstr2.='a'.$sbstr[$j];
                        }
                    }
                    $strs[]=$fstr2;
                    $fstr2 = $sbstr[0];
                }
            }else{
                $sbstr = explode('a',$str);
                $strs[]=$sbstr[0].'á'.$sbstr[1];
            }
        }
        if(count(explode('e',$str))>1){
            if(count(explode('e',$str))>2){
                $count = count(explode('e',$str));
                $sbstr = explode('e',$str);
                $fstr2 = $sbstr[0];
                for($i = 1; $i<$count;$i++){
                    for($j = 1; $j<$count;$j++){
                        if($j==$i){
                            $fstr2.='é'.$sbstr[$j];
                        }else{
                            $fstr2.='e'.$sbstr[$j];
                        }
                    }
                    $strs[]=$fstr2;
                    $fstr2 = $sbstr[0];
                }
            }else{
                $sbstr = explode('e',$str);
                $strs[]=$sbstr[0].'é'.$sbstr[1];
            }
        }
        if(count(explode('i',$str))>1){
            if(count(explode('i',$str))>2){
                $count = count(explode('i',$str));
                $sbstr = explode('i',$str);
                $fstr2 = $sbstr[0];
                for($i = 1; $i<$count;$i++){
                    for($j = 1; $j<$count;$j++){
                        if($j==$i){
                            $fstr2.='í'.$sbstr[$j];
                        }else{
                            $fstr2.='i'.$sbstr[$j];
                        }
                    }
                    $strs[]=$fstr2;
                    $fstr2 = $sbstr[0];
                }
            }else{
                $sbstr = explode('i',$str);
                $strs[]=$sbstr[0].'í'.$sbstr[1];
            }
        }
        if(count(explode('o',$str))>1){
            if(count(explode('o',$str))>2){
                $count = count(explode('o',$str));
                $sbstr = explode('o',$str);
                $fstr2 = $sbstr[0];
                for($i = 1; $i<$count;$i++){
                    for($j = 1; $j<$count;$j++){
                        if($j==$i){
                            $fstr2.='ó'.$sbstr[$j];
                        }else{
                            $fstr2.='o'.$sbstr[$j];
                        }
                    }
                    $strs[]=$fstr2;
                    $fstr2 = $sbstr[0];
                }
            }else{
                $sbstr = explode('o',$str);
                $strs[]=$sbstr[0].'ó'.$sbstr[1];
            }
        }
        if(count(explode('u',$str))>1){
            if(count(explode('u',$str))>2){
                $count = count(explode('u',$str));
                $sbstr = explode('u',$str);
                $fstr2 = $sbstr[0];
                for($i = 1; $i<$count;$i++){
                    for($j = 1; $j<$count;$j++){
                        if($j==$i){
                            $fstr2.='ú'.$sbstr[$j];
                        }else{
                            $fstr2.='u'.$sbstr[$j];
                        }
                    }
                    $strs[]=$fstr2;
                    $fstr2 = $sbstr[0];
                }
            }else{
                $sbstr = explode('u',$str);
                $strs[]=$sbstr[0].'ú'.$sbstr[1];
            }
        }
        return $strs;
    }
    public function generateRandomString($STRL = 20) {
        $CHR = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($STR = ''; strlen($STR) < $STRL;)
            $STR .= $CHR[rand(0, strlen($CHR) - 1)];
        return $STR;
    }
    public function generateRandomEmail($KNOWN=false){
        $STRL = rand(8, 15);
        $STRL2= rand(4, 10);
        $CHR = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($STR = ''; strlen($STR) < $STRL;)
            $STR .= $CHR[rand(0, strlen($CHR) - 1)];
        if($KNOWN){
            $EMEND =["@gmail.com","@hotmail.com","@yahoo.co","@yahoo.es"];
            $STR.=$EMEND[rand(0, count($EMEND) - 1)];
        }else{
            $END=[".co",".com",".edu",".gov",".org",".es"];
            for ($EMEND = ''; strlen($EMEND) < $STRL2;)
                $EMEND.=$CHR[rand(0, strlen($CHR) - 1)];
            $STR.="@".$EMEND.$END[rand(0, count($END) - 1)];
        }
        return $STR;
    }
    public function getDocumentPath(Document $document)
    {

        $service = $this->container->get('sonata.media.twig.extension');
        $response = '';
        if($document->getMediaMedia()){
            $response.= '//' . $_SERVER['HTTP_HOST'] . $service->path($document->getMediaMedia(), 'reference');
        }

        return $response;
    }
    public function getLocalDocumentPath(Document $document)
    {
        $service = $this->container->get('sonata.media.twig.extension');
        $response = '';
        if($document->getMediaMedia()){
            $response.= $service->path($document->getMediaMedia(), 'reference');
        }

        return $response;
    }

    public function mb_capitalize($stringToCapitalize)
    {
        mb_internal_encoding('UTF-8');
        if(!mb_check_encoding($stringToCapitalize, 'UTF-8') OR !($stringToCapitalize === mb_convert_encoding(mb_convert_encoding($stringToCapitalize, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
          $stringToCapitalize = mb_convert_encoding($stringToCapitalize, 'UTF-8');
        }

        $lclArr = [];
        $strArray = explode(" ", trim(mb_convert_case($stringToCapitalize, MB_CASE_LOWER, "UTF-8")));
        foreach ($strArray as $key => $singleWord){
          $localMay = mb_convert_case($singleWord, MB_CASE_UPPER, "UTF-8");
          $lclArr[$key] =  mb_substr($localMay,0,1,"UTF-8").mb_substr($strArray[$key],1,NULL,"UTF-8");
        }

        $stringToReturn = implode(" ", $lclArr);

        return $stringToReturn;
    }
    public function month_number_to_name($numberToConvert)
    {
        $stringToReturn="";
        if(is_numeric($numberToConvert)){
            $numberToConvert=$numberToConvert % 13;
            if($numberToConvert==0)
                $numberToConvert+=1;
            switch ($numberToConvert){
                case 1 :
                    $stringToReturn="Enero";
                    break;
                case 2 :
                    $stringToReturn="Febrero";
                    break;
                case 3 :
                    $stringToReturn="Marzo";
                    break;
                case 4 :
                    $stringToReturn="Abril";
                    break;
                case 5 :
                    $stringToReturn="Mayo";
                    break;
                case 6 :
                    $stringToReturn="Junio";
                    break;
                case 7 :
                    $stringToReturn="Julio";
                    break;
                case 8 :
                    $stringToReturn="Agosto";
                    break;
                case 9 :
                    $stringToReturn="Septiembre";
                    break;
                case 10 :
                    $stringToReturn="Octubre";
                    break;
                case 11 :
                    $stringToReturn="Noviembre";
                    break;
                case 12 :
                    $stringToReturn="Diciembre";
                    break;
            }
        }
        return $stringToReturn;
    }
    public function period_number_to_name($numberToConvert)
    {
        $stringToReturn="";
        if(is_numeric($numberToConvert)){
            switch ($numberToConvert){
                case 2 :
                    $stringToReturn="1ª Quincena de";
                    break;
                case 4 :
                    $stringToReturn="Cierre de mes";
                    break;
            }
        }
        return $stringToReturn;
    }

		/**
		 * Return the date to be set, after submitting the novelty<br/>
		 *
		 * @ApiDoc(
		 *   resource = true,
		 *   description = "Return the date to be set, after submitting the novelty"
		 * )
		 *
		 * @return DateTime
		 */
    public function novelty_date_constrain_to_date_after($dateConstrain, Novelty $novelty){

	    $answer = new DateTime();
	    /** @var DateTime $today */
	    $today = new DateTime();
	    $period = $novelty->getPayrollPayroll()->getPeriod();
	    $timeCommitment = $novelty->getPayrollPayroll()->getContractContract()->getTimeCommitmentTimeCommitment();

	    //Start period
	    if($dateConstrain == "sP"){
		    if($period == 2 || ($period == 4 && $timeCommitment == "TC") ){
		    	$answer->setDate($today->format('Y'),$today->format('m'), 1);
		    }
		    elseif ($period == 4 && $timeCommitment == "XD"){
			    $answer->setDate($today->format('Y'),$today->format('m'), 16);
		    }

		    return $answer;
	    }

			//End Period
	    if($dateConstrain == "eP"){
		    if($period == 2){
			    $answer->setDate($today->format('Y'),$today->format('m'), 15);
		    }
		    elseif ($period == 4){
			    $answer->setDate($today->format('Y'),$today->format('m'), $today->format('t'));
		    }

		    return $answer;
	    }

	    //Current Date
	    if($dateConstrain == "today"){
				//$answer already has the current date
		    return $answer;
	    }

	    $elementsArr = explode("+", $dateConstrain);

	    foreach ($elementsArr as $singleEle){
	    	if($singleEle == "date_start"){
	    		$answer = new DateTime($novelty->getDateStart()->format('Y-m-d'));
		    }
		    elseif($singleEle == "date_end"){
		    	$answer = new DateTime($novelty->getDateEnd()->format('Y-m-d'));
		    }
		    elseif($singleEle == "units"){
			    $day = $novelty->getUnits() - 1; //Since the same day counts
			    $answer->modify("+{$day} days");
		    }
	    }

	    return $answer;

    }

	/**
	 * Validates the date<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Validates the date, if wrong returns a string with the error"
	 * )
	 *
	 * @return array
	 */
	public function novelty_date_constrain_to_date_validation($dateConstrain, $valDate, $payroll )
	{
		$answer = array();
		array_push($answer, true); //Index 0 - Determines if is false or true the validation
		array_push($answer, "");// Index 1 - If Index 0 is false, sets a custom error msg to display to the user
		if($dateConstrain == "sP" ){
			$localDate = get_start_end_from_actual_period("start",$payroll); //This function returns the date for the start or end of the period as requested
			if($localDate > $valDate){
				$answer[0] = false;
				$answer[1] = "La fecha no puede ser menor a " . $localDate->format('d-m-Y');
				return $answer;
			}
		}
		elseif ($dateConstrain == "eP" ){
			$localDate = get_start_end_from_actual_period("end",$payroll); //This function returns the date for the start or end of the period as requested
			if($localDate < $valDate) {
				$answer[0] = false;
				$answer[1] = "La fecha no puede ser mayor a " . $localDate->format('d-m-Y');
				return $answer;
			}
		}
		else{
			$constrArr = explode(" ", $dateConstrain);

			foreach ($constrArr as $singleConstr) { //Checks all the validations for the field

				/** @var DateTime $todayDate */
				$todayDate = new DateTime();
				$sign = $singleConstr[4]; //Determines if the value should be higher than the constrain (-) or less (+)
				$dataType = substr($singleConstr, 6, 2); //Gets the type of validation (check multiple ifs)
				$value = substr($singleConstr, 9); // Number that modifies the dataType

				if ($dataType == "nM") { //Numero de meses
					$todayDate->modify("{$sign}{$value} months");
				} elseif ($dataType == "da") { //Fecha exacta
					$todayDate =new DateTime($value);
				} elseif ($dataType == "nP") { //Numero de periodos
					$todayDate = $this->get_date_from_period($sign,$value, $payroll); //This function returns the date of the result period
				} elseif ($dataType == "nD") { //Numero de dias
					$todayDate->modify("{$sign}{$value} days");
				} elseif ($dataType == "nY") { //Numero de anios
					$todayDate->modify("{$sign}{$value} years");
				} elseif ($dataType == "cU") { //Siguiente corte
					$todayDate = $this->get_date_from_period("+",0, $payroll); //This function returns the date of the next cut
				}

				if (strpos($singleConstr, "Min") !== false) {
					if ($todayDate > $valDate) {
						$answer[0] = false;
						$answer[1] = "La fecha no puede ser menor a " . $todayDate->format('d-m-Y');
						return $answer;
					}
				} elseif (strpos($singleConstr, "Max") !== false) {
					if ($todayDate < $valDate) {
						$answer[0] = false;
						$answer[1] = "La fecha no puede ser mayor a " . $todayDate->format('d-m-Y');
						return $answer;
					}
				}
			}
		}
		return $answer;
	}

	/**
	 * Get the date to set regarding the period specified on the constrain<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Get the date to set regarding the period specified on the constrain"
	 * )
	 *
	 * @return DateTime
	 */
	private function get_date_from_period($sign, $value, Payroll $payroll){

		//$localTimeCommitment = $payroll->getContractContract()->getTimeCommitmentTimeCommitment()->getCode();

		$actualPeriod = intval($payroll->getPeriod());
		$actualMonth = intval($payroll->getMonth());
		$actualDay = $actualPeriod == 4?20:5;
		$actualYear = intval($payroll->getYear());

		$frequency = $payroll->getContractContract()->getFrequencyFrequency()->getPayrollCode();

		$dateString = $actualYear . "-" . $actualMonth . "-" . $actualDay;
		$actualPeriodDate = new DateTime($dateString);

		$targetToReach = $value;
		while($targetToReach != 0){
			if($frequency == "Q"){
				if($actualPeriodDate->format('d') == 20){
					$actualPeriodDate->modify("-15 days");
					if($sign == "+"){
						$actualPeriodDate->modify("+1 month");
					}
				}
				else{
					$actualPeriodDate->modify("+15 days");
					if($sign == "-"){
						$actualPeriodDate->modify("-1 month");
					}
				}
			}
			elseif ($frequency == "M"){
					$actualPeriodDate->modify("{$sign}1 month");
			}
			$targetToReach--;
		}

		//In this point actualPeriodDate has the target month and year for the desired period, only need to set the correct day
		$localY = $actualPeriodDate->format('Y');
		$localM = $actualPeriodDate->format('m');

		if($sign == "-" && $actualPeriodDate->format('d') == 20 && $frequency == "Q"){
			$actualPeriodDate->setDate($localY,$localM,16);
		}
		elseif ($sign == "-" && (($frequency == "Q" && $actualPeriodDate->format('d') == 5) || ($frequency == "M") )) {
			$actualPeriodDate->setDate($localY,$localM,1);
		}
		elseif ($sign == "+" && (($frequency == "Q" && $actualPeriodDate->format('d') == 20) || ($frequency == "M") )) {
			$actualPeriodDate->setDate($localY,$localM,25);
		}
		elseif ($sign == "+" && $frequency == "Q" && $actualPeriodDate->format('d') == 5){
			$actualPeriodDate->setDate($localY,$localM,12);
		}

		return $actualPeriodDate;
	}

	/**
	 * Get the date to set regarding the active period<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Get the date to set regarding the active period"
	 * )
	 *
	 * @return DateTime
	 */
	private function get_start_end_from_actual_period($start_or_end ,Payroll $payroll){

		$localTimeCommitment = $payroll->getContractContract()->getTimeCommitmentTimeCommitment()->getCode();

		$actualPeriod = intval($payroll->getPeriod());
		$actualMonth = intval($payroll->getMonth());
		$actualYear = intval($payroll->getYear());

		$returnDate = new DateTime($actualYear,$actualMonth,1);

		if($start_or_end == "start"){
			if($actualPeriod == 4 && $localTimeCommitment == "XD"){
				$returnDate->setDate($actualYear,$actualMonth,16);
			}
			elseif (($localTimeCommitment == "XD" && $actualPeriod == 2) || ($localTimeCommitment == "TC") ) {
				$returnDate->setDate($actualYear,$actualMonth,1);
			}
		}
		elseif ($start_or_end == "end"){
			if($actualPeriod == 4 && $localTimeCommitment == "XD"){
				$returnDate->setDate($actualYear,$actualMonth,25);
			}
			elseif (($localTimeCommitment == "XD" && $actualPeriod == 2) || ($localTimeCommitment == "TC") ) {
				$returnDate->setDate($actualYear,$actualMonth,12);
			}
		}

		return $returnDate;
	}

	/**
	 * Validates the amount<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Validates the amount, if wrong returns a string with the error"
	 * )
	 *
	 * @return array
	 */
	public function novelty_amount_constrain_validation($amountConstrain, $valAmount,Contract $contract)
	{

		$answer = array();
		array_push($answer, true); //Index 0 - Determines if is false or true the validation
		array_push($answer, "");// Index 1 - If Index 0 is false, sets a custom error msg to display to the user

		$constrArr = explode(" ", $amountConstrain);

		foreach ($constrArr as $singleConstr) { //Checks all the validations for the field

			$compareTo = 0;
			$dataType = substr($singleConstr, 4, 2); //Gets the type of validation (check multiple ifs)
			$value = substr($singleConstr, 7); // Number that modifies the dataType

			if(strpos($value,"_") !== false ){ //if this line exists, means that has to compare a % with certain value
				$elementsArr = explode("_", substr($singleConstr,4));
				if($elementsArr[0] == "pe"){ //Percentage
					if($elementsArr[2] == "Sal"){
						$splitSalaryIn = 0;
						if($contract->getFrequencyFrequency()->getPayrollCode() == "M"){
							$splitSalaryIn = 1;
						}
						elseif ($contract->getFrequencyFrequency()->getPayrollCode() == "Q"){
							$splitSalaryIn = 2;
						}
						$compareTo = ($contract->getSalary()/$splitSalaryIn) *(intval($elementsArr[1])/100); //Gets the % value based on a static value (most used case, % of the base salary)
					}
				}
			}
			else{ //Otherwise the string is ready
				if($dataType == "mo"){ //Money (flat value)
					$compareTo = intval($value);
				}
			}

			if (strpos($singleConstr, "Min") !== false) {
				if ($compareTo > $valAmount) {
					$answer[0] = false;
					$answer[1] = "El valor no puede ser menor a $" . round($compareTo);
					return $answer;
				}
			} elseif (strpos($singleConstr, "Max") !== false) {
				if ($compareTo < $valAmount) {
					$answer[0] = false;
					$answer[1] = "El valor no puede ser mayor a $" . round($compareTo);
					return $answer;
				}
			}
		}
		return $answer;
	}

	/**
	 * Validates the units<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Validates the units, if wrong returns a string with the error"
	 * )
	 *
	 * @return array
	 */
	public function novelty_units_constrain_validation($unitsConstrain, $valUnits)
	{

		$answer = array();
		array_push($answer, true); //Index 0 - Determines if is false or true the validation
		array_push($answer, "");// Index 1 - If Index 0 is false, sets a custom error msg to display to the user

		$constrArr = explode(" ", $unitsConstrain);

		foreach ($constrArr as $singleConstr) { //Checks all the validations for the field

			//Not used for now
			//$dataType = substr($singleConstr, 4, 2); //Gets the type of validation (check multiple ifs)
			$value = intval(substr($singleConstr, 7)); // Number that modifies the dataType

			if (strpos($singleConstr, "Min") !== false) {
				if ($value > $valUnits) {
					$answer[0] = false;
					$answer[1] = "El valor ingresado no puede ser menor a " . $value;
					return $answer;
				}
			} elseif (strpos($singleConstr, "Max") !== false) {
				if ($value < $valUnits) {
					$answer[0] = false;
					$answer[1] = "El valor ingresado no puede ser mayor a " . $value;
					return $answer;
				}
			}
		}
		return $answer;
	}

	public function mb_normalize($stringToNormalize)
	{
		mb_internal_encoding('UTF-8');
		if(!mb_check_encoding($stringToNormalize, 'UTF-8') OR !($stringToNormalize === mb_convert_encoding(mb_convert_encoding($stringToNormalize, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
			$stringToNormalize = mb_convert_encoding($stringToNormalize, 'UTF-8');
		}

		$lclArr = [];
		$strArray = explode(" ", trim(mb_convert_case($stringToNormalize, MB_CASE_LOWER, "UTF-8")));
		foreach ($strArray as $key => $singleWord){
			$lclArr[$key] =  $singleWord;
		}

		$stringToReturn = implode(" ", $lclArr);

		return $stringToReturn;
	}

	/**
     * get date from dateStart to number of workable (business) specified (can be negative)
     *
     *
     * @param string $dateStart format YYYY-MM-DD
     * @param int $days
     * @return data date YYYY-MM-DD
     *
     */
    public function getWorkableDaysToDateAction($dateStart, $days)
    {
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
        $i=1;
        $j=0;
        if($numberDays > 0) {
            while($j<$numberDays){
                $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))+$i);
                if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                    $days[]=$dateToCheck->format("Y-m-d");
                    $j++;
                }
                $i++;

            }
        } else if($numberDays < 0){
             while($j>$numberDays){
                $dateToCheck->setDate($dateRStart->format("Y"),$dateRStart->format("m"),intval($dateRStart->format("d"))-$i);
                if($this->workable($dateToCheck)&&isset($wkd[$dateToCheck->format("w")])){
                    $days[]=$dateToCheck->format("Y-m-d");
                    $j--;
                }
                $i++;

            }
        }
        return $dateToCheck->format("Y-m-d");
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


        $date=new DateTime("2017-"."01"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."01"."-"."09");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."03"."-"."20");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."04"."-"."09");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."04"."-"."13");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."04"."-"."14");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."04"."-"."16");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."05"."-"."01");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."05"."-"."29");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."06"."-"."19");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."06"."-"."26");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."07"."-"."03");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."07"."-"."20");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."08"."-"."07");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."08"."-"."21");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."10"."-"."16");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."11"."-"."06");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."11"."-"."13");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."12"."-"."08");
        $holyDays[]=$date;
        $date=new DateTime("2017-"."12"."-"."25");
        $holyDays[]=$date;
        /** @var DateTime $hd */
        foreach ($holyDays as $hd) {
            if($dateToCheck->format("Y-m-d")==$hd->format("Y-m-d"))
                return false;
        }

        return true;
    }

    public function normalizeAccentedChars($strToNormalize) {
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $strNormalized = strtr( $strToNormalize, $unwanted_array );

        return $strNormalized;
    }
}
