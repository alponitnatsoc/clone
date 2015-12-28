<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class BasicPersonRegistration extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('documentType', 'choice', array(
			    'choices' => array(
			        'cedulaCiudadania'   => 'Cedula Ciudadania',
			        'cedulaExtrangeria' => 'Cedula Extrangeria',
                    'pasaporte' => 'Pasaporte',
			    ),
			    'multiple' => false,
			    'expanded' => false,
			    'property_path' => 'documentType',)
            )
            ->add('document', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),
                'property_path' => 'document'
            ))
            ->add('names', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('lastName1', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('lastName2', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('birthDate', 'date', array(
                'years' => range(1900,2015),
                'constraints' => array(
                    new NotBlank(),
                ),))
            //Tab 2
        	
            ->add('mainAddress', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('neighborhood', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                ),))
            ->add('phones', 'collection', array(
                'type' => new PhoneRegistration(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('department', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Department',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'department',
                ))
            ->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                ));
            $formModifier = function (FormInterface $form, Department $department = null) {
                $citys = null === $department ? array() : $department->getCitys();

                $form->add('city', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:City',
                'placeholder' => '',
                'choices'     => $citys,
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'city',
                ));
                
            };

            $builder->get('department')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $department = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $department);
                }
            );




    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person',
        ));
    }
    
    public function getName()
    {
        return 'register_basic_person';
    }
} 
?>