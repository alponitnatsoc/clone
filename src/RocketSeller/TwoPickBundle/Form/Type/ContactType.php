<?php
namespace RocketSeller\TwoPickBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', 'text', array(
            'attr' => array(
                'placeholder' => 'Quién eres? :)',
                'pattern'     => '.{2,}', //minlength
                'class' => ''
            )
        ))
        ->add('email', 'email', array(
            'attr' => array(
                'placeholder' => 'Tú correo electronico.'
            )
        ))
        ->add('topic', 'choice', array(
            'attr' => array(
                'onchange' => 'javascript:setSubject(this);'
            ),
            'choices'  => array(
                'plinio.romero@symplifica.com' => 'Facturacion',
                'romero.p.mfc@gmail.com' => 'Preguntas Laborales',
                'blue_dark1987@hotmail.com' => 'Problemas técnicos'
            ),
            'choice_attr' => function($val, $key, $index) {
                return array("label" => $key);
            },
            'label' => 'Tema de contacto',
            'multiple' => false,
            'expanded' => false,
            'empty_value' => 'Please select option'
        ))
        ->add('subject', 'hidden', array(
        ))
        ->add('message', 'textarea', array(
            'attr' => array(
                'cols' => 90,
                'rows' => 10,
                'placeholder' => 'Escribe Tu mensaje aquí ...'
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $collectionConstraint = new Collection(array(
            'name' => array(
                new NotBlank(array('message' => 'El nombre no puede ir vacio.')),
                new Length(array('min' => 10))
            ),
            'email' => array(
                new NotBlank(array('message' => 'El email no puede ir vacio.')),
                new Email(array('message' => 'Invalid email address.'))
            ),
            'topic' => array(
                new NotBlank(array('message' => 'Debe seleccionar un tema de contacto.')),
                new Length(array('min' => 3))
            ),
            'subject' => array(
//                 new NotBlank(array('message' => 'El asunto no puede ir vacio.')),
//                 new Length(array('min' => 3))
            ),
            'message' => array(
                new NotBlank(array('message' => 'El mensaje no puede ir vacio.')),
                new Length(array('min' => 5))
            )
        ));


        $resolver->setDefaults(array(
            'constraints' => $collectionConstraint,
            'csrf_protection' => true,
            'csrf_field_name' => '_token'
        ));
    }

    public function getName()
    {
        return 'contact';
    }
}