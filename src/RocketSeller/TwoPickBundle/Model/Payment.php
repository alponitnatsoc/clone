<?php
namespace RocketSeller\TwoPickBundle\Model;

class Payment {

	//Clase STUB para la integración con tercero, acá se agregarán métodos y propiedades para definir la integración con un tercero por definir


	public function proccessPayment(PaymentInfo $info) {
		return true;
	} 

	public function validatePayment(PaymentInto $info) {
		return true;
	}

}