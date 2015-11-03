<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BenefitPick extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('benefits', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Benefits',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'benefitsBenefits'
                ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\ContractHasBenefits',
        ));
    }
    
    public function getName()
    {
        return 'pick_benefits';
    }
} 
?>