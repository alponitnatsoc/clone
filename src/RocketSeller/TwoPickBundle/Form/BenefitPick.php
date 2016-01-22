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
            ->add('benefitType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Benefits',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'benefitsBenefits',
                'label'=>'Tipo de Beneficio'
            ))
            ->add('amount', 'money', array(
                'currency' => 'COP',
                'property_path' => 'amount',
                'label'=>' '
            ))
            ->add('periodicity', 'choice', array(
                'choices' => array(
                    ''   => 'Periodicidad',
                    1 => 'Mensual',
                    3 => 'Trimestral',
                    6 => 'Semestral',
                    12=> 'Anual',
                ),
                'multiple' => false,
                'expanded' => false,
                'label'=>' '
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\ContractHasBenefits'
        ));
    }

    public function getName()
    {
        return 'pick_benefits';
    }
}