<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CurrencyAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Name', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('translations', 'translated_field', array(
                'field'                => 'name',
                'personal_translation' => 'RocketSeller\TwoPickBundle\Entity\CurrencyTranslation',
                'property_path'        => 'translations',
                'translation_domain' => 'RocketSellerTwoPickBundle'
            ))
            ->add('code', 'text', array('label' => 'Code', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('code')
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name')
            ->add('code')
            ->add('created_datetime');
    }
}