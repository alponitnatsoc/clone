<?php

namespace RocketSeller\TwoPickBundle\pdf;

class cafeSalud extends  Pdf
{

	function selectTypeField($type){
		switch ($type) {
		    case 0:
		       	$this->selectField(42,34.5);
		        break;
		    case 1:
		        $this->selectField(59,34.5);
		        break;
		    case 2:
		        $this->selectField(76,34.5);
		        break; 
		    case 3:
		        $this->selectField(93,34.5);
		        break;        	
		}
	}
	function writePersonInfo($personPerson)
	{
		$this->writeInfo(55,102,$personPerson->getDocument());
		$this->writeInfo(87,102,$personPerson->getNames()." ".$personPerson->getLastName1() . " ".$personPerson->getLastName2());
		$this->writeInfo(179,102,$personPerson->getBirthDate()->format("d")."/");
		$this->writeInfo(185,102,$personPerson->getBirthDate()->format("m")."/");
		$this->writeInfo(191,102,$personPerson->getBirthDate()->format("y"));
	}	

}