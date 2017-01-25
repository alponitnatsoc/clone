<?php

namespace RocketSeller\TwoPickBundle\Form;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class PersonExtraData extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civilStatus', 'choice', array(
                'choices' => array(
                    'soltero'   => 'Soltero(a)',
                    'casado' => 'Casado(a)',
                    'unionLibre' => 'Union Libre',
                    'viudo' => 'Viudo(a)'
                ),
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'civilStatus',
                'label' => 'Estado civil',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('gender', 'choice', array(
                'choices' => array(
                    'MAS'   => 'Masculino',
                    'FEM' => 'Femenino'
                ),
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'gender',
                'label' => 'Género',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('documentExpeditionDate', 'date', array(
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                ),
                'years' => range(2018,1900),
                'label' => 'Fecha de expedición de documento de identidad',
                'required' => true
            ))
            ->add('documentExpeditionPlace', 'text', array(
                'label' => 'Lugar de expedición de documento de identidad',
                'required' => true
            ))
            ->add('birthCountry', 'entity', array(
                'label' => 'País de Nacimiento',
                'translation_domain' => 'messages',
                'class' => 'RocketSellerTwoPickBundle:Country',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthCountry',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('birthDepartment', 'entity', array(
                'label' => 'Departamento de Nacimiento',
                'translation_domain' => 'messages',
                'class' => 'RocketSellerTwoPickBundle:Department',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthDepartment',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('birthCity', 'entity', array(
                'label' => 'Ciudad de Nacimiento',
                'translation_domain' => 'messages',
                'class' => 'RocketSellerTwoPickBundle:City',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'birthCity',
                'placeholder' => 'Seleccionar una opción',
                'required' => true
            ))
            ->add('email', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'property_path' => 'email',
                'required' => false
            ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Person $data */
            $data = $event->getData();
            if($data==null){
                return;
            }else{
                $country = $data->getBirthCountry();
                $departs = $country?$country->getDepartments():array();
                $cities =  $data->getBirthCity()?$data->getBirthCity()->getDepartmentDepartment()->getCitys():array();
                $event->getForm()->add('birthDepartment', 'entity', array(
                    'label' => 'Departamento de Nacimiento',
                    'translation_domain' => 'messages',
                    'class' => 'RocketSellerTwoPickBundle:Department',
                    'choices' => $departs,
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => false,
                    'property_path' => 'birthDepartment',
                    'placeholder' => 'Seleccionar una opción',
                    'required' => true
                ))->add('birthCity', 'entity', array(
                    'label' => 'Ciudad de Nacimiento',
                    'translation_domain' => 'messages',
                    'class' => 'RocketSellerTwoPickBundle:City',
                    'choices' => $cities,
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => false,
                    'property_path' => 'birthCity',
                    'placeholder' => 'Seleccionar una opción',
                    'required' => true
                ));
                return;
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person'
        ));
    }

    public function getName()
    {
        return 'register_person_extra';
    }
}
