<?php

namespace RocketSeller\TwoPickBundle\Form;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class EmployeeProfileEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('documentType', 'choice', array(
			    'choices' => array(
			        'CC'   => 'Cedula Ciudadania',
			        'CE' => 'Cedula Extranjeria',
                    'TI' => 'Tarjeta de identidad'
			    ),
			    'multiple' => false,
			    'expanded' => false,
			    'property_path' => 'documentType',
                'label' => 'Tipo de documento*',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
		    ))
            ->add('document', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'property_path' => 'document',
                'label' => 'Número de documento*'
            ))
            ->add('names', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),'label' => 'Nombres*'))
            ->add('lastName1', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),'label' => 'Primer Apellido*'))
            ->add('lastName2', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'label' => 'Segundo Apellido',
                'required' => false
            ))
            ->add('birthDate', 'date', array(
                'data' => new DateTime('1975-01-01'),
                'years' => range(1900,2015),
                'constraints' => array(
                    new NotBlank()
                ),'label' => 'Fecha de Nacimiento*'))
            //Tab 2
            ->add('mainAddress', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),'label' => 'Dirección Principal*'))
            ->add('department', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'department',
                'label' => 'Departamento*',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                'label' => 'Ciudad*',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))            
            ->add('save', 'submit', array(
                'label' => 'Save',
            ))
            ;
            $formModifier = function (FormInterface $form, Department $department = null) {
                $citys = null === $department ? array() : $department->getCitys();
                $form->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'choices'     => $citys,
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
                ));
            };

            $builder->get('department')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $department = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $department);
                }
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person'
        ));
    }

    public function getName()
    {
        return 'register_basic_person';
    }
}