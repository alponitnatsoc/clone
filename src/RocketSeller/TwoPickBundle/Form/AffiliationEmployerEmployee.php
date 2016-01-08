<?php 

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AffiliationEmployerEmployee extends AbstractType
{
    private $employerHasEmployees;
    private $wealthEntities;
    private $pensionEntities;
    function __construct($employerHasEmployees,$wealthEntities,$pensionEntities){
        $this->employerHasEmployees=$employerHasEmployees;
        $this->wealthEntities=$wealthEntities;
        $this->pensionEntities=$pensionEntities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('idEmployer', 'hidden')
            ->add('employerHasEmployees', 'collection', array(
                'type' => new EntitiesPick($this->wealthEntities,$this->pensionEntities),
                'allow_add'    => false,
                'allow_delete' => false,
                'by_reference' => false,
            ))
            ->add('save', 'submit', array(
                'label' => 'Save',
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
        return 'register_social_security';
    }
} 
?>