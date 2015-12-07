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

class Settings extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notificaciones', 'choice', array(
                'choices' => array(
                    'todas'   => 'Todas las notificaciones',
                    'account' => 'Solo de mi cuenta',
                ),
                'multiple' => false,
                'expanded' => false)
            )
            ->add('credit_card', 'text')
            ->add('expiry_date', 'text')
            ->add('cvv', 'text')
            ->add('name_on_card', 'text')
            ->add('save', 'submit', array('label' => 'Submit'));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Settings',
            'validation_groups' => false
        ));
    }
    
    public function getName()
    {
        return 'settings';
    }
} 
?>