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
                'label' => 'Soy',
                'property_path' => 'employerType'))
            ->add('person', new BasicPersonRegistration(), array(
                'property_path' => 'personPerson'))

            ->add('documentExpeditionDate', 'date', array(
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                ),
                'years' => range(2015,1900),
                'label' => 'Fecha de expedición de documento de identidad',
                'required' => true,
                'mapped' => false,
            ))
            ->add('sameWorkHouse', 'choice', array(
                'choices' => array(
                    true   => 'Si',
                    false  => 'No'
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => '¿Tu dirección de contacto es la misma donde trabajarán tus empleados?',
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
            ->add('severances', 'collection', array(
                'type' => new CajaPick($this->severancesEntities),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'property_path' => 'entities',
                'label'=>'Caja de compensación Familiar',
            ))
            ->add('arl', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->arlEntities,
                'choice_label' =>'name',
                'placeholder'=>"",
                'mapped' => false,
                'label'=>'Administradora de Riesgos Labolares',
                'required' => true
            ))
            ->add('arlAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomA',"placeholder" => "Escribe el nombre de tu entidad")
            ))
            ->add('arlExists', 'choice', array(
                'choices' => array(
                    0 => 'Si',
                    1 => 'No, afílieme'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿Está afiliado?',
                'required' => true
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
