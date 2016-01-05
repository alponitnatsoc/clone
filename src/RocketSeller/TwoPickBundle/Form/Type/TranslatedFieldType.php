<?php

namespace RocketSeller\TwoPickBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RocketSeller\TwoPickBundle\Form\EventListener\AddTranslatedFieldSubscriber;
 
/**
 * Class TranslatedFieldType
 * @package Escuela\BackendBundle\Form\Type
 */
class TranslatedFieldType extends AbstractType
{
    protected $container;
 
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
 
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!class_exists($options['personal_translation']))
        {
            Throw new \InvalidArgumentException(sprintf("Unable to find personal translation class: '%s'", $options['personal_translation']));
        }
        if(! $options['field'])
        {
            Throw new \InvalidArgumentException("You should provide a field to translate");
        }
 
        $subscriber = new AddTranslatedFieldSubscriber($builder->getFormFactory(), $this->container, $options);
        $builder->addEventSubscriber($subscriber);
    }
 
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'remove_empty' => true,
            'csrf_protection' => false,
            'personal_translation' => false, //Personal Translation class
            'locales' => array('en', 'es'), //the locales you wish to edit
            'required_locale' => array('en', 'es'), //the required locales cannot be blank
            'field' => false, //the field that you wish to translate
            'widget' => "text", //change this to another widget like 'texarea' if needed
            'entity_manager_removal' => true, //aut
        ));
 
    }
 
    public function getName()
    {
        return 'translated_field';
    }
}