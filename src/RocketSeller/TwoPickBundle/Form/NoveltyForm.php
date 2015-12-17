<?php 

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Form\DocumentPick;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
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
    private $hasDocuments;
    function __construct($fields,$hasDocuments){
        $this->fields=$fields;
        $this->hasDocuments=$hasDocuments;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('noveltyType', 'entity', array(
            'class' => 'RocketSellerTwoPickBundle:NoveltyType',
            'placeholder' => '',
            'property' => 'name',
            'multiple' => false,
            'expanded' => false,
            'read_only' =>true,
            'label' => 'Tipo de novedad',
            'property_path' => 'noveltyTypeNoveltyType'));
        //if has documents add the form for the medias
        if($this->hasDocuments){
            $builder->add('documents', 'collection', array(
                'type' => new DocumentPick(),
                'by_reference' => false,
                'required' => false));
        }
        //if has extra fields add te fields to the main form
        /** @var NoveltyTypeFields $field */
        foreach ($this->fields as $field){
            // leading letter means that is an entity
            if($field->getDataType()[0]<="Z"){
                $builder
                    ->add($field->getDataType(), 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:'.$field->getDataType(),
                        'placeholder' => '',
                        'property' => 'name',
                        'multiple' => false,
                        'expanded' => false,
                    ));
            }else{
                if($field->getDataType()=='money'){
                    $builder
                        ->add($field->getColumnName(), $field->getDataType(), array(
                            'label' => $field->getName(),
                            'currency' => 'COP',
                            'required' => false
                        ));
                }else{
                    $builder
                        ->add($field->getColumnName(), $field->getDataType(), array(
                            'label' => $field->getName(),
                            'required' => false
                        ));
                }
            }
        }
        $builder->add('save', 'submit', array(
            'label' => 'Create',));
        $builder->add('later', 'submit', array(
            'label' => 'Later',));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\Novelty',
        ));
    }
    
    public function getName()
    {
        return 'novelty_fields';
    }
} 
?>