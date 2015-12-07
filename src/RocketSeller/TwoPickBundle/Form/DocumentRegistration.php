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
use RocketSeller\TwoPickBundle\Form\MediaForm;

class DocumentRegistration extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('mediaMedia', 'sonata_media_type', array(
                'provider' => 'sonata.media.provider.image',
                'context'  => 'engine'
            ))
            ->add('documentTypeDocumentType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:DocumentType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentTypeDocumentType'
            ))
            ->add('name', 'text', array(
                'label' => 'name',
            ))
            ->add('save', 'submit', array(
                'label' => 'Create',
            ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Document',
        ));
    }
    
    public function getName()
    {
        return 'add_document';
    }
} 
?>