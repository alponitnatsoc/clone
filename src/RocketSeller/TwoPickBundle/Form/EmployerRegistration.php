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

class EmployerRegistration extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numberOfWorkplaces', 'choice', array(
                'choices' => array(
                    '1'   => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ),
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,))
            ->add('workplaces', 'collection', array(
                'type' => new WorkPlaceRegistration(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Employer',
        ));
    }
    
    public function getName()
    {
        return 'register_employer';
    }
} 
?>