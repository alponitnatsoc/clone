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

class PersonExtraData extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civilStatus', 'choice', array(
                    'choices' => array(
                        'soltero'   => 'Soltero',
                        'casado' => 'Casado',
                        'unionLibre' => 'Union Libre',
                        'viudo' => 'Viudo',
                    ),
                    'multiple' => false,
                    'expanded' => false,
                    'property_path' => 'civilStatus',)
            )
            ->add('birthCountry', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Country',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthCountry',
            ))
            ->add('birthDepartment', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthDepartment',
            ))
            ->add('birthCity', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthCity',
            ))
            ->add('email', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),
                'property_path' => 'email',));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person',
        ));
    }
    
    public function getName()
    {
        return 'register_person_extra';
    }
} 
?>