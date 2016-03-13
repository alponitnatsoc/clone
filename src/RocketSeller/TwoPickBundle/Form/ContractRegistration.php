<?php

namespace RocketSeller\TwoPickBundle\Form;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\PayType;
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
    private $workplaces;
    private $today;
    private $todayOneYear;
    function __construct($workplaces){
        $this->today= new DateTime();
        $this->todayOneYear= new DateTime((intval($this->today->format("Y"))+1)."-".$this->today->format("m")."-".$this->today->format("d"));
        $this->workplaces=$workplaces;
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
            ->add('existentNew', 'choice', array(
                'choices' => array(
                     1=> 'Término Fijo',
                     0=> 'Término Indefinido',
                ),
                'multiple' => false,
                'mapped' => false,
                'expanded' => true,
                'label'=>'¿El empleado pertenece al SISBÉN?*',
                'required' => true,
            ))
            ->add('sisben', 'choice', array(
                'choices' => array(
                     1=> 'Si',
                     0=> 'No',
                ),
                'multiple' => false,
                'expanded' => true,
                'label'=>'¿El empleado pertenece al SISBÉN?*',
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
            ->add('contractType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:ContractType',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'contractTypeContractType',
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
                'label'=>'¿Cuánto le paga a su empleado mensualmente?*',
                'required'=>false,
                //'scale'=>0
            ))
            ->add('salaryD', 'money', array(
                'currency'=>'COP',
                'label'=>'Salario Diario',
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
                'label' => 'Fecha inicio de contrato*:',
                'data' => $this->today,
                'years' => range($this->today->format("Y"),intval($this->today->format("Y"))+1),
            ))
            ->add('endDate', 'date', array(
                'label' => 'Fecha fin de contrato*:',
                'data' => $this->todayOneYear,
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
                ),
                'multiple' => false,
                'expanded' => false,
                'mapped' =>false,
                'label'=>'Días laborales de la semana*:'
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
                'label'=>'Días laborales de la semana*:'
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
            ));
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