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

class DocumentRegistration extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('mediaMedia', 'sonata_media_type', array(
                'label'=> 'media',
                'provider' => 'sonata.media.provider.file',
                'context'  => 'person'
            ))
            ->add('documentTypeDocumentType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:DocumentType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentTypeDocumentType'
            ))
            ->add('save', 'submit', array(
                'label' => 'Subir',
            ))
            ;

//        $builder->get('mediaMedia')->add('galleryHasMedias','sonata_type_collection',array());
//        $builder->get('mediaMedia')->add('binaryContent', 'file', array('label' => 'Subir Archivo'));
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