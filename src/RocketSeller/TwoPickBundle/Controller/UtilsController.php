<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

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
}
