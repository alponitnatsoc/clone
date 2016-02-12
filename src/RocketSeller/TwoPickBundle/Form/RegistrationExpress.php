<?php
 namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use RocketSeller\TwoPickBundle\Form\BasicPersonRegistration;
use Solarium\QueryType\Select\Result\Debug\Document;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationExpress extends AbstractType
{
    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', new BasicPersonRegistration(), array(
                'property_path' => 'personPerson'))
            ->add('email', 'email', array('translation_domain' => 'FOSUserBundle', "attr" => array('placeholder' => 'form.email')))
            ->add('plainPassword','password', array("attr" => array('placeholder' => 'form.password'), 'translation_domain' => 'FOSUserBundle'))
            ->add('save', 'submit', array(
                'label' => 'Create',
            ));
            
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\User',            
        ));
    }

    public function getName()
    {
        return 'app_user_express_registration';
    }
}