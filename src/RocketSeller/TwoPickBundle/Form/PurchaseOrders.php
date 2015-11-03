<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrders extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('purchaseOrdersType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:PurchaseOrdersType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'purchaseOrdersTypePurchaseOrdersType',
                ))
            ->add('payroll', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Payroll',
                'placeholder' => '',
                'property' => 'idPayroll',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'payrollPayroll',
                ))
            ->add('purchaseOrdersStatus', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:PurchaseOrdersStatus',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'purchaseOrdersStatusPurchaseOrdersStatus',
                ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\PurchaseOrders',
        ));
    }

    public function getName()
    {
        return 'register_purchase_orders';
    }
}