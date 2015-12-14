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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DocumentPick extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('mediaMedia', 'sonata_media_type', array(
                'provider' => 'sonata.media.provider.image',
                'context'  => 'person'
            ))
            ->add('documentTypeDocumentType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:DocumentType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentTypeDocumentType',
                'read_only' =>true,
            ))
            ->add('name', 'text', array(
                'label' => 'name',
                'read_only' =>true,
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
        return 'pick_document';
    }
} 
?>