<?php

namespace RocketSeller\TwoPickBundle\pdf;

class Pdf extends  \FPDF_FPDI
{
	function setFile($path)
	{
		$this->setSourceFile($path);
		$this->SetFont('Helvetica');
		$this->SetTextColor(0, 0, 0);
	}
	function selectField($x,$y)
	{
		$this->SetXY($x,$y);
		$this->Write(0, 'x');
	} 
	function setActualDate()
	{
		$this->SetXY(173,34.5);
		$this->Write(0, date("d"));

		$this->SetXY(180, 34.5);
		$this->Write(0, date("m"));

		$this->SetXY(187,34.5);
		$this->Write(0, date("y"));
	}
	function writeInfo($x,$y,$info)
	{
		$this->SetXY($x,$y);
		$this->Write(0,$info);

	}

}