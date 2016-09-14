<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentRegistration extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('mediaMedia', 'sonata_media_type', array(
                'label'=> 'media',
                'provider' => 'sonata.media.provider.file',
                'context'  => 'person',
                'constraints'=>array(
                    new NotBlank(array(
                        'message'=>'Por favor selecciona un archivo'
                    ))
                )
            ))
            ->add('documentTypeDocumentType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:DocumentType',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'documentTypeDocumentType'
            ))
            ->add('save', 'submit', array(
                'label' => 'Subir',
            ))
            ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

        ));
    }
    
    public function getName()
    {
        return 'add_document';
    }
} 
?>