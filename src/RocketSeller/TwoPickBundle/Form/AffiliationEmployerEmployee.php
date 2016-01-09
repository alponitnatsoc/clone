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
    private $wealthEntities;
    private $pensionEntities;
    private $severancesEntities;
    private $arlEntities;
    function __construct($wealthEntities,$pensionEntities,$severancesEntities,$arlEntities){
        $this->wealthEntities=$wealthEntities;
        $this->pensionEntities=$pensionEntities;
        $this->severancesEntities=$severancesEntities;
        $this->arlEntities=$arlEntities;
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
            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>'Caja de Compensación Familiar'
            ))
            ->add('arl', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->arlEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>'Administradora de Riesgos Labolares'
            ))
            ->add('economicalActivity', 'text')
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