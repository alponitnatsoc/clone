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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('youAre', 'choice', array(
                'choices' => array(
                    'persona'   => 'Persona',
                    'empresa' => 'Empresa (muy pronto)'
                ),
                'multiple' => false,
                'expanded' => true,
                'choice_attr' => function($key, $val, $index) {
                    $disabled = $key=="empresa";
                    return $disabled ? ['disabled' => 'disabled'] : [];
                },
                'label' => 'Usted es*',
                'property_path' => 'employerType'))
            ->add('person', new BasicPersonRegistration(), array(
                'property_path' => 'personPerson'))
            ->add('numberOfWorkplaces', 'choice', array(
                'choices' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6'
                ),
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,
                "label" => "¿En cuántos lugares trabajará(n) su(s) empleado(s)?",
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('sameWorkHouse', 'choice', array(
                'choices' => array(
                    true   => 'Si',
                    false  => 'No'
                ),
                'multiple' => false,
                'expanded' => true,
                'label' => '¿Su dirección principal es la misma donde trabajará(n)?',
                'property_path' => 'sameWorkHouse'))
            ->add('workplaces', 'collection', array(
                'type' => new WorkPlaceRegistration(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('save', 'submit', array(
                'label' => 'Guardar'
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