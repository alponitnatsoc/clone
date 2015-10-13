<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonRegistration extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $basicData = $builder->create('basic', 'tab', array(
            'label' => 'Datos básicos',
            'icon' => 'pencil',
            'inherit_data' => true,
        ));

        $basicData
            ->add('tuEres', 'choice', array(
			    'choices' => array(
			        'persona'   => 'Persona',
			        'empresa' => 'Empresa',
			    ),
			    'multiple' => false,
			    'expanded' => true,
			    'mapped' => false,))
            ->add('tipoDocumento', 'choice', array(
			    'choices' => array(
			        'cedulaCiudadania'   => 'Cedula Ciudadania',
			        'cedulaExtrangeria' => 'Cedula Extrangeria',
			    ),
			    'multiple' => false,
			    'expanded' => false,
			    'property_path' => 'documentType',)
            )
            ->add('Documento', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),
                'property_path' => 'document'
            ))
            ->add('names', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('lastName1', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('lastName2', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('birthDate', 'date', array(
                'constraints' => array(
                    new NotBlank(),
                ),));

        $contact = $builder->create('contact', 'tab', array(
            'label' => 'Datos de Contacto',
            'icon' => 'user',
            'inherit_data' => true,
        ));

        $contact
        	->add('address', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ;

        

        /**
         * Add both tabs to the main form
         */
        $builder
            ->add($basicData)
            ->add($contact);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'tabs_class' => 'nav nav-pills nav-stacked',
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person',
        ));
    }

    
    public function getName()
    {
        return 'register_person_tabs';
    }
} 
?>