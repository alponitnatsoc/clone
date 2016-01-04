<?php 
 namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationType extends AbstractType
{
    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('email', 'email', array('translation_domain' => 'FOSUserBundle', "attr" => array('placeholder' => 'form.email')))
            ->add('name', 'text', array("attr" => array('placeholder' => 'Name'), 'mapped' => false))
            ->add('plainPassword','password', array("attr" => array('placeholder' => 'form.password'), 'translation_domain' => 'FOSUserBundle'))
        ;
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
 ?>