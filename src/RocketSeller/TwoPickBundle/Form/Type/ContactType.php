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

class ContactType extends AbstractType
{
    private $name;
    private $email;

    public function __construct($name,$email){
        $this->name  = $name;
        $this->email = $email;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->setMethod($options['method'])
            ->add('name', 'text',array(
                    'data'     =>$this->name,
                    'label'    => 'Nombre:',
                    'required' => true
                )
            )

            ->add('email', 'email',array(
                    'data'    =>$this->email,
                    'label'   => 'Email:',
                    'required'=> true
                )
            )

            ->add('subject', 'choice', array(
                    'label'   =>'Con que necesitas ayuda?',
                    'choices' => array(
                        0 => 'Preguntas del Registro',
                        1 => 'Preguntas de pago de nómina y aportes',
                        2 => 'Preguntas sobre la calculadora salarial',
                        3 => 'Consulta jurídica',
                        4 => 'Consulta de planes y precios',
                        5 => 'Otros'
                    ),
                    'multiple' => false,
                    'expanded' => false,
                    'mapped'   => false,
                    'required' => true
                )
            )
        ->add('message', 'textarea', array(
            'attr' => array(
                'placeholder' => 'Escribe Tu mensaje aquí, recibiras respuesta del equipo de symplifica lo antes posible'
            )
        ))

        ->add('enviar', 'submit', array(
            'label' => 'Enviar el correo',
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
        return 'contact';
    }
}