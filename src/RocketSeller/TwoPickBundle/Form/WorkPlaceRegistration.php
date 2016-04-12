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

class WorkPlaceRegistration extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                "label"=>"Nombre asignado a este lugar de trabajo*",
                "attr" => array(
                    "placeholder"=>"(Casa, Finca, Apartamento)",
                ),
                'required' => true
            ))
            ->add('mainAddress', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                "label"=>"Dirección*",
                'required' => true
            ))
            ->add('id', 'hidden', array(
                'property_path' => 'idWorkplace'
            ))
            ->add('department', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'department',
                "label" => "Departamento*",
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                "label" => "Ciudad*",
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Workplace'
        ));
    }

    public function getName()
    {
        return 'register_workplaces';
    }
}