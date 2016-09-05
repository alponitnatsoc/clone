<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use Symfony\Component\Validator\Constraints\Date;

class UtilsController
{
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
				$answer[1] = "La fecha no puede ser menor a " . $localDate->format('Y-m-d');
				return $answer;
			}
		}
		elseif ($dateConstrain == "eP" ){
			$localDate = get_start_end_from_actual_period("end",$payroll); //This function returns the date for the start or end of the period as requested
			if($localDate < $valDate) {
				$answer[0] = false;
				$answer[1] = "La fecha no puede ser mayor a " . $localDate->format('Y-m-d');
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
						$answer[1] = "La fecha no puede ser menor a " . $todayDate->format('Y-m-d');
						return $answer;
					}
				} elseif (strpos($singleConstr, "Max") !== false) {
					if ($todayDate < $valDate) {
						$answer[0] = false;
						$answer[1] = "La fecha no puede ser mayor a " . $todayDate->format('Y-m-d');
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
		
		$localTimeCommitment = $payroll->getContractContract()->getTimeCommitmentTimeCommitment()->getCode();
		
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
}
