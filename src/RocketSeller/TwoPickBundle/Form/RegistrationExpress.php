<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use RocketSeller\TwoPickBundle\Form\BasicPersonRegistration;
use Solarium\QueryType\Select\Result\Debug\Document;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use RocketSeller\TwoPickBundle\Form\AddCreditCard;
use RocketSeller\TwoPickBundle\Form\PhoneRegistration;

class RegistrationExpress extends AbstractType
{

    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->setAction($options['action'])
                ->setMethod($options['method'])
                ->add('documentType', 'choice', array(
                    'choices' => array(
                        'CC' => 'Cédula de ciudadanía',
                        'CE' => 'Cedula de extranjería',
                        'TI' => 'Tarjeta de identidad'
                    ),
                    'multiple' => false,
                    'expanded' => false,
                    'property_path' => 'documentType',
                    'label' => 'Tipo de documento*',
                    'placeholder' => 'Tu tipo de documento',
                    'required' => true
                ))
                ->add('document', 'integer', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'property_path' => 'document',
                    'label' => 'Número de documento*',
                    "attr" => array(
                        "data-toggle" => "tooltip",
                        "data-placement" => "right",
                        "data-container" => "body",
                        "title" => "Con esto validaremos tu información en las entidades de salud",
                        'placeholder' => 'Número de documento',
                    )
                ))
                ->add('names', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    "attr" => array('placeholder' => 'Tus Nombres'),
                    'label' => 'Nombres*'
                        )
                )
                ->add('lastName1', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    "attr" => array('placeholder' => 'Primer apellido'),
                    'label' => 'Primer Apellido*'))
                ->add('lastName2', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'label' => 'Segundo Apellido',
                    "attr" => array('placeholder' => 'Segundo apellido'),
                    'required' => false
                ))
                ->add('phone', new PhoneRegistration(), array(
                    'mapped' => false
                ))
                ->add('credit_card', new AddCreditCard(), array(
                    'mapped' => false
                ))
                ->add('save', 'submit', array(
                    'label' => 'Continuar',
                    'attr' => array(
                        'class' => 'btn register-AS1 btn-symplifica'
                    ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person',
        ));
    }

    public function getName()
    {
        return 'app_user_express_registration';
    }

}
