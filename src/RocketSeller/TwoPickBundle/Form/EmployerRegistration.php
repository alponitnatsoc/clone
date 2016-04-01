<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class EmployerRegistration extends AbstractType
{
    private $severancesEntities;
    private $arlEntities;
    function __construct($severancesEntities,$arlEntities){
        $this->severancesEntities=$severancesEntities;
        $this->arlEntities=$arlEntities;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('youAre', 'choice', array(                
                'choices' => array(
                    'persona'   => 'Una persona',
                    'empresa' => 'Una empresa (muy pronto)'
                ),
                'multiple' => false,
                'expanded' => true,
                'choice_attr' => function($key, $val, $index) {
                    $disabled = $key=="empresa";
                    return $disabled ? ['disabled' => 'disabled'] : [];
                },
                'label' => 'Soy*',
                'property_path' => 'employerType'))
            ->add('person', new BasicPersonRegistration(), array(
                'property_path' => 'personPerson'))
            ->add('sameWorkHouse', 'choice', array(
                'choices' => array(
                    true   => 'Si',
                    false  => 'No'
                ),                
                'multiple' => false,
                'expanded' => true,
                'label' => '¿Tu dirección de contacto es la misma donde trabajarán tus empleados? *',
                'data' => true,
                'property_path' => 'sameWorkHouse',
                'required' => true,
                'attr' => array(
                    'class' => "mainLabel"
                )
            ))
            ->add('workplaces', 'collection', array(
                'type' => new WorkPlaceRegistration(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
                'placeholder'=>"",
                'mapped' => false,
                'label'=>'Caja de Compensación Familiar*',
                'required' => true
            ))

            ->add('arl', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->arlEntities,
                'choice_label' =>'name',
                'placeholder'=>"",
                'mapped' => false,
                'label'=>'Administradora de Riesgos Labolares*',
                'required' => true
            ))
            ->add('severancesAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomS')
            ))
            ->add('arlAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomA')
            ))
            ->add('save', 'submit', array(
                'label' => 'Guardar y continuar',
                'attr'   =>  array(
                'class'   => 'btn btn-orange')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Employer'
        ));
    }

    public function getName()
    {
        return 'register_employer';
    }
}