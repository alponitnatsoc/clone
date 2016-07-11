<?php

namespace RocketSeller\TwoPickBundle\Controller;

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
}
