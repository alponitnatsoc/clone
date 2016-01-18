<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvitationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
//             ->add('status')
//             ->add('sent')
//             ->add('userId')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\Invitation',
            'attr' => array('class'=>'form-horizontal')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rocketseller_twopickbundle_invitation';
    }
}
