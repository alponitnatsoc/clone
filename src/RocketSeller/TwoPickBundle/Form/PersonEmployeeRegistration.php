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
use RocketSeller\TwoPickBundle\Form\BasicPersonRegistration;

class PersonEmployeeRegistration extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', new BasicPersonRegistration(), array(
                'property_path' => 'personPerson'))
            ->add('economicalTier', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),
                'property_path' => 'economicalTier'
            ))
            ->add('save', 'submit', array(
                'label' => 'Save',
            ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Employee',
        ));
    }
    
    public function getName()
    {
        return 'register_employee';
    }
} 
?>