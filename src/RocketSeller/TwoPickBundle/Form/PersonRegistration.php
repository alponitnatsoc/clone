<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonRegistration extends AbstractType
{
    private $citys;
    private $departments;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
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
                    'pasaporte' => 'Pasaporte',
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
                ),))
            //Tab 2
        	
            ->add('mainAddress', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('neighborhood', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('phone', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('department', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentType',
                ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentType',
                ))

            ;

        


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