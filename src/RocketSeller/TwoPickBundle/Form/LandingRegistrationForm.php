<?php
// src/AppBundle/Form/UserType.php
namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class LandingRegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entity_type', 'choice',  array('label' => false,
                  'choices'=>array('persona'=>'Persona', 'empresa'=>'Empresa'),
                  'attr'=>array('class'=>'radios'),
                  'multiple' => false,
                  'expanded' => true,
                  'required' => true))
            ->add('name', 'text',  array('label' => false,
                  'attr'=>array('class'=>'campos', 'placeholder'=>'Escríbe tu nombre y apellidos')))
            ->add('email', 'text',  array('label' => false,
                  'attr'=>array('class'=>'campos', 'placeholder'=>'Escríbe tu dirección de correo')))
            ->add('phone', 'text',  array('label' => false,
                  'attr'=>array('class'=>'campos', 'placeholder'=>'Escríbe tu número de celular')))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\LandingRegistration'
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'rocketseller_twopickbundle_landing_registration';
    }
}
