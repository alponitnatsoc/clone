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

class EntityRegistration extends AbstractType
{
    private $fields;
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        foreach ($this->fields as $key => $value) {
            $builder->add($value, 'text', array(
                'mapped' => false));
        }
        


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'fields' => array(),
        ));
    }
    
    public function getName()
    {
        return 'register_person_tabs';
    }
} 
?>