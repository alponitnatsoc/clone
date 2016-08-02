<?php
namespace RocketSeller\TwoPickBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class MailType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod($options['method'])
            ->add('email', 'email',array(
                        'label'   => 'Email:',
                        'required'=> true,
                        'attr' => array(
                            'placeholder' => 'Digita tu dirección de correo electrónico'
                        )
                    )
                )
            ->add('enviar', 'submit', array(
                'label' => 'Enviar',
                'attr'=> array('class'=>"naranja bold btn-symplifica btn", 'id'=>"submit_button", 'type'=>"submit")
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'recordatos';
    }
}