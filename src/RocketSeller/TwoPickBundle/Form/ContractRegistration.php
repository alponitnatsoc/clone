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

class ContractRegistration extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('currentState', 'choice', array(
                'choices' => array(
                    'active'   => 'Active',
                    'unactive' => 'unActive',
                    'standby' => 'standBy',
                ),
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'state',))
            ->add('salary', 'money', array(
                'constraints' => array(
                    new NotBlank(),),
                'currency' => 'COP',
                'property_path' => 'salary',
                ))
            ->add('benefits', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Benefits',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                ))
            ->add('workplaces', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Workplace',
                'placeholder' => '',
                'property' => 'mainAddress',
                'multiple' => true,
                'expanded' => true,
                'property_path' => 'workplaces',
                ))
            ->add('save', 'submit', array(
                'label' => 'Save',
            ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Contract',
        ));
    }
    
    public function getName()
    {
        return 'add_contract';
    }
} 
?>