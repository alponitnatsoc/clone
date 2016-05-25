<?php
 namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array("attr" => array('placeholder' => 'Nombres'), 'mapped' => false))
            ->add('lastName', 'text', array("attr" => array('placeholder' => 'Primer apellido'), 'mapped' => false))
            ->add('email', 'email', array('translation_domain' => 'FOSUserBundle', "attr" => array('placeholder' => 'form.email')))
            ->add('plainPassword','password', array("attr" => array('placeholder' => 'form.password', 'pattern'=>'^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{7,})\S$') ,'translation_domain' => 'FOSUserBundle'))
            /*->add('invitation', 'text', array(
                        "attr" => array(
                            'placeholder' => 'Ingresa un código de referido'
                        ),
                        'mapped' => false,
                        'property_path' => 'invitation',
                        'required' => false,
                    )*/
                );
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\User',
            'intention'  => 'registration',
        ));
    }

    public function getName()
    {
        return 'app_user_registration';
    }
}
