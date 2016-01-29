<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class PagoMembresiaForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->setAction($options['action'])
                ->setMethod($options['method'])
                ->add('personType', 'choice', array(
                    'choices' => array(
                        'persona' => 'Persona',
                        'empresa' => 'Empresa'
                    ),
                    'multiple' => false,
                    'expanded' => true,
                    //'choice_attr' => function($key, $val, $index) {
                    //           $disabled = $key == "empresa";
                    //    return $disabled ? ['disabled' => 'disabled'] : [];
                    //},
                    'label' => 'Usted es*',
                    'required' => true,
                    'property_path' => 'personType'
                ))
                ->add('documentType', 'choice', array(
                    'choices' => array(
                        'CC' => 'Cedula Ciudadania',
                        'CE' => 'Cedula Extranjeria',
                        'TI' => 'Tarjeta de identidad'
                    ),
                    'multiple' => false,
                    'expanded' => false,
                    'label' => 'Tipo de documento*',
                    'placeholder' => 'Seleccionar una opción',
                    'required' => true,
                    'property_path' => 'documentType'
                ))
                ->add('document', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required' => true,
                    'label' => 'Número de documento*',
                    'property_path' => 'document'
                ))
                ->add('razonSocial', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required' => true,
                    'label' => 'Razón Social*',
                    'property_path' => 'razonSocial'
                ))
                ->add('names', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required' => true,
                    'label' => 'Nombres*',
                    'property_path' => 'names'
                ))
                ->add('lastName1', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required' => true,
                    'label' => 'Primer Apellido*',
                    'property_path' => 'lastName1'
                ))
                ->add('lastName2', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'label' => 'Segundo Apellido',
                    'property_path' => 'lastName2'
                ))
                ->add('phone', 'text', array(
                    'required' => true,
                    'label' => 'Telefono*',
                    'property_path' => 'phone'
                ))
                ->add('address', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required' => true,
                    "label" => "Dirección*"
                ))
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
        ));


        $formModifier = function (FormInterface $form, Department $department = null) {
            $citys = null === $department ? array() : $department->getCitys();
            $form->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'choices' => $citys,
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ));
        };

        $builder->get('department')->addEventListener(
                FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $department = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $department);
        }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\BillingAddress'
        ));
    }

    public function getName()
    {
        return 'pago_membresia';
    }

    public function getId()
    {
        return 'pago_membresia';
    }

}
