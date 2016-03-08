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

class RegistrationExpress extends AbstractType
{
    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('documentType', 'choice', array(
                'choices' => array(
                    'CC'   => 'Cédula de ciudadanía',
                    'CE' => 'Cedula de extranjería',
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
                'label' => 'Número de documento*',
                "attr" => array(
                    "data-toggle" => "tooltip", 
                    "data-placement" => "right",
                    "data-container" => "body",
                    "title" => "Texto de ayuda"
                )
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
            ->add('save', 'submit', array(
                'label' => 'Continuar',
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