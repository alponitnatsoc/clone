<?php

namespace RocketSeller\TwoPickBundle\Form;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\TimeCommitment;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class ContractRegistration extends AbstractType
{
    private $timeCommitments;
    private $workplaces;
    private $today;
    private $todayOneYear;
    private $legalFlag;
    private $departments;

    /**
     * @param $workplaces
     * @param $timeCommitments
     * @param $legalFlag
     * @param array $departments
     */
    function __construct($workplaces,$timeCommitments,$legalFlag,$departments=array()){
        $this->legalFlag=$legalFlag;
        $this->timeCommitments=$timeCommitments;
        $this->today= new DateTime();
        $this->todayOneYear= new DateTime((intval($this->today->format("Y"))+1)."-".$this->today->format("m")."-".$this->today->format("d"));
        $this->workplaces=$workplaces;
        $this->departments = $departments;
    }
    private function getChoicesContractType()
    {
        $dataArray= array();
        // some logic here using $this->em
        // it should return key-value array, example:
        /** @var ContractType $TM */
        foreach ($this->timeCommitments as $TM) {
            $dataArray[$TM->getPayrollCode()]=$TM->getName();
        }
        return $dataArray;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('new', 'button', array(
                'label' => 'Nuevo',
            ))
            ->add('existent', 'button', array(
                'label' => 'Ya trabaja conmigo',
            ))
            ->add('yesExistent', 'button', array(
                'label' => 'Si',
            ))
            ->add('noExistent', 'button', array(
                'label' => 'No',
            ))
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
            ->add('worksSaturday', 'choice', array(
                'choices' => array(
                    1=> 'Si',
                    -1=> 'No',
                ),
                'multiple' => false,
                'expanded' => true,
                'label'=>'¿Trabaja los sabados?',
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
            ->add('benefits', 'collection', array(
                'type' => new BenefitPick(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
                ))
            ->add('benefitsConditions', 'textarea', array(
                'property_path' => 'benefitsConditions',
                'label'=>'Condiciones de los beneficios',
                'required' => false
            ))
            ->add('documentDocument', new DocumentPick())
            /*->add('workTimeStart', 'time', array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'Hora inicio*:'
            ))
            ->add('workTimeEnd', 'time', array(
                'input'  => 'datetime',
                'widget' => 'choice',
                'label'=>'Hora fin:'
            ))*/
            ->add('startDate', 'date', array(
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                ),
                'label' => '¿Cúal es la fecha de inicio del contrato?:',
                'years' => range(1990,intval($this->today->format("Y"))+1),
            ))
            ->add('endDate', 'date', array(
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                ),
                'label' => '¿Cúal es la fecha en que finaliza el contrato?*:',
                //'data' => $this->todayOneYear,
                'years' => range($this->today->format("Y"),intval($this->today->format("Y"))+3),
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
            ->add('workplaces', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Workplace',
                'choices' => $this->workplaces,
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'workplaceWorkplace',
                'label'=>'¿Cuál será su lugar de trabajo?',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('payMethod', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:PayType',
                'property' => 'name',
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>' '
            ))
            ->add('frequencyFrequency', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Frequency',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'label'=>'Frecuencia de Págo',
                'required'=> true,
                'placeholder'=>'Seleccione una opción'
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
            ))
            ->add('workplace', new WorkPlaceRegistration($this->departments), array(
                'mapped' => false));
        if($this->legalFlag=='1'){
            $builder
                ->add('holidayDebt', 'integer', array(
                    'label'=>'¿Como está respecto a vacaciones?*',
                    'required'=>true,
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Contract'
        ));
    }

    public function getName()
    {
        return 'add_contract';
    }
}
