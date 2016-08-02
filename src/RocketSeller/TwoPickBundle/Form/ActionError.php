<?php
namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionError extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('description','text'
        );        
    }
}