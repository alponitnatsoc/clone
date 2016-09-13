<?php

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\TempFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * Class FileForm
 * @package RocketSeller\TwoPickBundle\Form
 */
class FileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image','file',array(
                'label_attr' => array(
                    'style' => 'display:none'
                    ),
                'by_reference'=>false,
                'required'=>true,
                'multiple' => false,
                'attr'=>array(
                    'accept'=>"image/jpeg,image/tiff,image/png,image/bmp",
                    'style'=>'margin: 1px, padding: 1px',
                    'class'=>'col-md-12'
                ),
                'property_path'=>'image',
                'constraints'=>array(
                    new NotBlank(),
                )
            ))
            ->add('idTempFile', 'hidden', array(
                'property_path' => 'idTempFile',
                'required'=>false
            ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'=>'RocketSeller\TwoPickBundle\Entity\TempFile'
        ));
    }

    public function getName()
    {
        return 'add_file';
    }

}
