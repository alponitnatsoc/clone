<?php

namespace RocketSeller\TwoPickBundle\Admin;

use RocketSeller\TwoPickBundle\Entity\Person;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Config\Definition\Exception\Exception;

class PersonAdmin extends Admin
{
//    protected function configureRoutes(RouteCollection $collection)
//    {
//        $collection
//            ->remove('delete')
//            ->remove('create')
//        ;
//
//    }
//
//    public function getBatchActions()
//    {
//        $actions = parent::getBatchActions();
//        unset($actions['delete']);
//        return $actions;
//    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        /** @var Person $person */
        $person = $formMapper->getAdmin()->getSubject();
        $formMapper
            ->with('Datos Basicos')
            ->add('names', 'text', array('label' => 'Name', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle','required'=>false))
            ->add('documentType', 'choice', array(
                'label'=>'Document Type',
                'choices'  => array('CC' => 'Cédula ciudadana', 'TI' => 'Tarjeta de identidad' ,'CE' => 'Cédula de extranjeria'),
                'help'=>'Escoja el tipo de documento',
            ))
            ->add('document','text', array('label' => 'Document', 'translation_domain' => 'RocketSellerTwoPickBundle'))                          
            ->add('birthDate','date', array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('mainAddress','text', array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->end()
        ;

//        if($person->getDocumentDocument()){
//            $formMapper
//                ->with('Cédula')
//                ->add('documentDocument.mediaMedia.binaryContent','sonata_type_model',array())
//                ->add('documentDocument.mediaMedia')
////                ->add('documentDocument.mediaMedia','sonata_media_type', array(
////                'label'=>'Archivo del documento de identidad',
////                'provider' => 'sonata.media.provider.file',
////                'context'  => 'backoffice',
////                    'required' => false
////                ))
//                ->end()
//            ;
//        }
//        if($person->getRutDocument()){
//            $formMapper
//                ->with('RUT')
//                ->add('rutDocument','entity',array('class'=>'RocketSeller\TwoPickBundle\Entity\Document'))
////                ->add('rutDocument.mediaMedia','sonata_media_type', array(
////                'label'=>'Archivo del documento RUT',
////                'provider' => 'sonata.media.provider.file',
////                'context'  => 'backoffice',
////
////                ))
//                ->end()
//            ;
//        }else{
//            if($person->getEmployer()==null){
//                $formMapper
//
//                    ->with('Rut', array(
//                    'description' => 'El usuario no ha subido el rut'
//                    ))
//                    ->end();
//            }
//        }
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('names')
            ->add('lastName1')
            ->add('lastName2')
            ->add('document')            
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('idPerson','text',array('label'=>'id'))
            ->add('names',null,array('label'=>'Nombres'))
            ->add('lastName1',null,array('label'=>'Primer Apellido'))
            ->add('lastName2',null,array('label'=>'Segundo Apellido'))
            ->add('document',null,array('label'=>'Documento'))
            ->add('documentType',null,array('label'=>'Tipo'))
            ->add('mainAddress',null,array('label'=>'Direccion'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
            ;
    }
    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('names')
        ;
    }
}