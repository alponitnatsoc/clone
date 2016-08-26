<?php

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\TimeCommitment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PublicCalculator extends AbstractType
{
	private $timeCommitments;
	/**
	 * @param $timeCommitments
	 */
	function __construct($timeCommitments){
		$this->timeCommitments=$timeCommitments;
	}
	private function getChoicesContractType()
	{
		$dataArray= array();
		/** @var ContractType $TM */
		foreach ($this->timeCommitments as $TM) {
			$dataArray[$TM->getPayrollCode()]=$TM->getName();
		}
		return $dataArray;
	}
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('sisben', 'choice', array(
				'choices' => array(
					1=> 'Si',
					-1=> 'No',
					//0=> 'No Se',
				),
				'multiple' => false,
				'expanded' => true,
				'label'=>'¿El empleado pertenece al SISBÉN? (regímen subsidiado)',
				'required' => true,
			))
			->add('transportAid', 'choice', array(
				'choices' => array(
					1=> 'Si',
					0=> 'No',
				),
				'multiple' => false,
				'expanded' => true,
				'label'=>'¿Residirá en el lugar de trabajo?',
				'required' => true,
			))
			->add('contractType', 'choice', array(
				'choices'=>$this->getChoicesContractType(),
				'multiple' => false,
				'expanded' => false,
				'mapped'=> false,
				'placeholder' => ' ',
				'label'=>'Tipo de contrato*',
				'required' => true
			))
			->add('timeCommitment', 'entity', array(
				'class' => 'RocketSellerTwoPickBundle:TimeCommitment',
				'property' => 'name',
				'multiple' => false,
				'expanded' => true,
				'property_path' => 'timeCommitmentTimeCommitment',
				'label'=>'¿Cuál será la modalidad de trabajo?',
				'required' => true
			))
			->add('position', 'entity', array(
				'class' => 'RocketSellerTwoPickBundle:Position',
				'property' => 'name',
				'multiple' => false,
				'expanded' => false,
				'property_path' => 'positionPosition',
				'label'=>'Defina el cargo que asignará a su empleado:',
				'placeholder' => 'Seleccionar una opción',
				'required' => true
			))
			->add('salary', 'money', array(
				'currency'=>'COP',
				'label'=>'¿Cuánto será el sueldo mensual que recibirá tu empleado?*',
				'required'=>false,
				'scale'=>0
			))
			->add('salaryD', 'money', array(
				'currency'=>'COP',
				'label'=>'¿Cuanto será el sueldo diario que recibirá?*',
				'required'=>false,
				'mapped'=>false,
				'scale'=>0
			))
			->add('weekWorkableDays', 'choice', array(
				'choices' => array(
					'1'=> '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
				),
				'placeholder'=>' ',
				'multiple' => false,
				'expanded' => false,
				'mapped' =>false,
				'label'=>'¿Qué días de la semana trabajará?'
			))
			->add('weekDays', 'choice', array(
				'choices' => array(
					'lunes'=> 'Lunes',
					'martes' => 'Martes',
					'miercoles' => 'Miercoles',
					'jueves' => 'Jueves',
					'viernes' => 'Viernes',
					'sabado' => 'Sabado',
					'domingo' => 'Domingo',
				),
				'multiple' => true,
				'expanded' => true,
				'mapped' =>false,
				'label'=>'¿Qué días de la semana trabajará?',
				'attr'   =>  array(
					'class'   => 'testing')
			))
			->add('paysPens', 'choice', array(
				'choices' => array(
					1=> 'Si',
					-1=> 'No',
				),
				'multiple' => false,
				'expanded' => true,
				'label'=>'¿Aporta a pensión?',
				'required' => true,
				'mapped' => false
			));
	}
	
	public function getName()
	{
		return 'public_calculator';
	}
}
