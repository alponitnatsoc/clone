<?php 

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\PayMethodFields;
use Solarium\QueryType\Select\Result\Debug\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NoveltyForm extends AbstractType
{
    private $fields;
    function __construct($fields){
        $this->fields=$fields;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var NoveltyTypeHasDocumentType $field */
        foreach ($this->fields as $field){
            $builder->add($field->getNoveltyTypeNoveltyType()->getName(), 'sonata_media_type', array(
                'provider' => 'sonata.media.provider.image',
                'context'  => 'person'
            ));
        }


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
    
    public function getName()
    {
        return 'novelty_fields';
    }
} 
?>