<?php 

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\PayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class ContractRegistration extends AbstractType
{
    private $workplaces;
    function __construct($workplaces){
        $this->workplaces=$workplaces;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('employeeType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:EmployeeContractType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'employeeContractTypeEmployeeContractType',
                'label'=>'Tipo de empleado'
            ))
            ->add('contractType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:ContractType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'contractTypeContractType',
                'label'=>'Tipo de contrato'
            ))
            ->add('timeCommitment', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:TimeCommitment',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'timeCommitmentTimeCommitment',
                'label'=>'Dedicación de tiempo'

            ))
            ->add('position', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Position',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'positionPosition',
                'label'=>'Cargo'
            ))
            ->add('salary', 'money', array(
                'constraints' => array(
                    new NotBlank(),),
                'currency' => 'COP',
                'property_path' => 'salary',
                'label'=>'Sueldo diario'
            ))

            ->add('benefits', 'collection', array(
                'type' => new BenefitPick(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                ))

            ->add('benefitsConditions', 'textarea', array(
                'property_path' => 'benefitsConditions',
                'label'=>'Condiciones de los beneficios'
            ))

            ->add('document', new DocumentPick())

            ->add('workTimeStart', 'time', array(
                'input'  => 'timestamp',
                'widget' => 'choice',
                'label'=>'Horario*:'
            ))

            ->add('workTimeEnd', 'time', array(
                'input'  => 'timestamp',
                'widget' => 'choice',
                'label'=>'A:'
            ))

            ->add('startDate', 'date', array(
                'years' => range(2010,2020),
                'label' => 'Fecha inicio de contrato*:',
                ))

            ->add('endDate', 'date', array(
                'years' => range(2016,2020),
                'label' => 'Fecha fin de contrato*:',
                ))

            ->add('transportAid', 'choice', array(
                'choices' => array(
                    ''=> 'Seleccionar',
                    'auxilio' => 'Recibe auxilio de transporte',
                    'empleador' => 'Transporte brindado por el empleador',
                    'reside' => 'Reside en el lugar de trabajo',
                ),
                'multiple' => false,
                'expanded' => false,
                'label'=>'Desplazamiento al lugar de trabajo'
            ))

            ->add('workableDaysMonth', 'choice', array(
                'choices' => range(1,30),
                'multiple' => false,
                'expanded' => false,
                'label'=>'Días laborales al mes'
            ))

            ->add('weekWorkableDays', 'choice', array(
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
                'label'=>'Días laborales de la semana*:'
            ))

            ->add('workplaces', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Workplace',
                'placeholder' => '',
                'choices' => $this->workplaces,
                'property' => 'mainAddress',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'workplaceWorkplace',
                'label'=>'Lugar de trabajo'
            ))

            ->add('payMethod', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:PayType',
                'property' => function ($payMethod) {
                    /** @var  PayType $payMethod */
                    //return $payMethod->getName()." DESCRIPTION ".$payMethod->getDescripcion()." IMAGE ".$payMethod->getImage();
                    return "<div class='paymentImage'><img src='". $payMethod->getImage() ."'' name='paymentImageDescription' /></div><h3 class='paymentTitle'>" . $payMethod->getName()."</h3><div class='paymentDescription'>".$payMethod->getDescripcion()."</div> ";
                },
                'multiple' => false,
                'expanded' => true,
                'property_path' => 'payTypePayType',
                'label'=>''
            ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Contract',
        ));
    }
    
    public function getName()
    {
        return 'add_contract';
    }
} 
?>