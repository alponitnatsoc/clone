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
            ->add('mainAddress', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),"label"=>"Dirección"))
            ->add('id', 'hidden', array(
                'property_path' => 'idWorkplace',
            ))
            ->add('department', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'department',
                "label" => "Departamento"
                ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                "label" => "Ciudad"
                ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Workplace',
        ));
    }
    
    public function getName()
    {
        return 'register_workplaces';
    }
} 
?>