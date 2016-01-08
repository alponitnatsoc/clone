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
use RocketSeller\TwoPickBundle\Form\WorkPlaceRegistration;

class PersonRegistration extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('youAre', 'choice', array(
			    'choices' => array(
			        'persona'   => 'Persona',
			        'empresa' => 'Empresa',
			    ),
			    'multiple' => false,
			    'expanded' => true,
			    'mapped' => false,))
            ->add('documentType', 'choice', array(
			    'choices' => array(
			        'CC'   => 'Cedula Ciudadania',
			        'CE' => 'Cedula Extrangeria',
                    'PP' => 'Pasaporte',
			    ),
			    'multiple' => false,
			    'expanded' => false,
			    'property_path' => 'documentType',)
            )
            ->add('document', 'text', array(
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
                'years' => range(1900,2015),
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
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'department',
                ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
            ))
            ->add('employer', new EmployerRegistration())


            ->add('save', 'submit', array(
                'label' => 'Create',
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
        return 'register_person_tabs';
    }
} 
?>