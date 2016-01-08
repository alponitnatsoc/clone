<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitiesPick extends AbstractType
{
    private $wealthEntities;
    private $pensionEntities;
    function __construct($wealthEntities,$pensionEntities){
        $this->wealthEntities=$wealthEntities;
        $this->pensionEntities=$pensionEntities;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameEmployee', 'text', array(
                'mapped' => false,
                'read_only'=>true,
                'disabled'=>true,
                'label'=>' '
            ))
            ->add('wealth', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->wealthEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '
            ))
            ->add('pension', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->pensionEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '
            ))
            ->add('beneficiaries', 'choice', array(
                'choices' => array(
                    1 => 'Si',
                    0 => 'No',
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>' '
            ))
            ->add('idEmployerHasEmployee', 'hidden');


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee',
        ));
    }
    
    public function getName()
    {
        return 'pick_entities';
    }
} 
?>