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
    private $phone;
    private $subject;

    public function __construct($name,$email,$phone,$subject = "default"){
        $this->name  = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->subject = $subject;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->setMethod($options['method']);

        if($this->name!='' and $this->email!='' and $this->phone!=''){
            $builder
                ->add('name', 'text',array(
                        'data'     =>$this->name,
                        'label'    => 'Nombre:',
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'Digita tu nombre'
                        )
                    )
                )
                ->add('email', 'email',array(
                        'data'    =>$this->email,
                        'label'   => 'Email:',
                        'required'=> true,
                        'attr' => array(
                            'placeholder' => 'Digita un correo electrónico de contacto'
                        )
                    )
                )
                ->add('phone', 'number',array(
                        'data'  =>$this->phone,
                        'label' => 'Telefono / Celular:',
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'Digita tu telefono o numero celular'
                        )
                    )
                );
        }else{
            $builder
                ->add('name', 'text',array(
                        'label'    => 'Nombre:',
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'Digita tu nombre'
                        )
                    )
                )
                ->add('email', 'email',array(
                        'label'   => 'Email:',
                        'required'=> true,
                        'attr' => array(
                            'placeholder' => 'Digita un correo electrónico de contacto'
                        )
                    )
                )
                ->add('phone', 'number',array(
                        'label' => 'Telefono / Celular:',
                        'required' => true,
                        'attr' => array(
                            'placeholder' => 'Digita tu telefono o numero celular'
                        )
                    )
                );
        }

        if($this->subject == "default"){
          $builder
              ->add('subject', 'choice', array(
                      'label'   =>'Con que necesitas ayuda?',
                      'choices' => array(
                          0 => 'Registro',
                          1 => 'Nómina y aportes',
                          2 => 'Calculadora salarial',
                          3 => 'Consulta jurídica',
                          4 => 'Planes y precios',
                          5 => 'Otros'
                      ),
                      'multiple' => false,
                      'expanded' => false,
                      'mapped'   => false,
                      'required' => true
                  )
              );
        }

        $builder
            ->add('subject', 'choice', array(
                    'label'   =>'Con que necesitas ayuda?',
                    'choices' => array(
                        0 => 'Registro',
                        1 => 'Nómina y aportes',
                        2 => 'Calculadora salarial',
                        3 => 'Consulta jurídica',
                        4 => 'Planes y precios',
                        5 => 'Otros'
                    ),
                    'multiple' => false,
                    'expanded' => false,
                    'mapped'   => false,
                    'required' => true
                )
            )
        ->add('message', 'textarea', array(
            'label' => 'Mensaje',
            'attr' => array(
                'placeholder' => 'Escribe tu mensaje aquí, recibiras respuesta del equipo de symplifica lo antes posible'
            )
        ))
          ->add('message', 'textarea', array(
              'attr' => array(
                  'placeholder' => 'Escribe tu mensaje aquí, recibiras respuesta del equipo de symplifica lo antes posible'
              )
          ))
          
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
        return 'contact';
    }
}
