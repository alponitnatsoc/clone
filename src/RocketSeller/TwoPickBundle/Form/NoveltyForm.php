<?php

namespace RocketSeller\TwoPickBundle\Form;

use DateTime;
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
    private $url;
    private $today;
    private $payrollId;
    function __construct($fields,$hasDocuments,$url=null,$payrollId=null){
        $this->url=$url;
        $this->fields=$fields;
        $this->hasDocuments=$hasDocuments;
        $this->today=new DateTime();
        $this->payrollId=$payrollId;
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
            'property_path' => 'noveltyTypeNoveltyType'))
            ->setAction($this->url);
        //if has documents add the form for the medias
        if($this->hasDocuments){
            $builder->add('documents', 'collection', array(
                'type' => new DocumentPick(),
                'by_reference' => false,
                'required' => false));
        }
        //if has extra fields add the fields to the main form
        /** @var NoveltyTypeFields $field */
        foreach ($this->fields as $field){
            if($field->getDisplayable() == false){
              continue;
            }
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
                }elseif($field->getDataType()=='date'){
                    $builder
                        ->add($field->getColumnName(), $field->getDataType(), array(
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years'=>range(intval($this->today->format("Y"))-1,intval($this->today->format("Y"))+1),
	                          'label' => $field->getName(),
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
        $builder
            ->add('noveltyCheckBox','checkbox',array(
                'label' => 'Estoy seguro que deseo agregar esta novedad y entiendo que posteriormente no podré modificarla',
                'required' => true,
                'mapped'=> false,
            ))
            ->add('idPayroll', 'hidden', array(
                'data' => $this->payrollId,
                'mapped'=>false))
            ->add('save', 'submit', array(
            'label' => 'Crear',));

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
