<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends Admin
{
    //Routs available
    protected function configureRoutes(RouteCollection $collection)
    {
        //removing delete and create route
        $collection
            ->remove('delete')
            ->remove('create');

    }

    //Actions that are available in the forms and list views
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        //remove option delete in all forms
        unset($actions['delete']);
        return $actions;
    }

    // Fields to be shown on create/edit forms
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('username', 'text', array('label' => 'Username', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('email','email', array('label' => 'Email', 'translation_domain' => 'RocketSellerTwoPickBundle'))
//            ->add('personPerson', 'sonata_type_model_list', array(
//                'btn_add'       => false,      //Specify a custom label
//                'btn_list'      => 'Mostrar Personas',     //which will be translated
//                'btn_delete'    => false,             //or hide the button.
//                'btn_catalogue' => 'SonataNewsBundle' //Custom translation domain for buttons
//            ), array(
//                'placeholder' => 'Seleccionar persona'
//            ))
//            ->add('realProcedure','sonata_type_collection',array(
//                'type_options' => array(
//                    // Prevents the "Delete" option from being displayed
//                    'delete' => false,
//                    'delete_options' => array(
//                        // You may otherwise choose to put the field but hide it
//                        'type'         => 'hidden',
//                        // In that case, you need to fill in the options as well
//                        'type_options' => array(
//                            'mapped'   => false,
//                            'required' => false,
//                        )
//                    )
//                ),
//
//            ), array(
//                'edit' => 'inline',
////                'edit' => 'standard',
//                'sortable' => 'position',
//            ))
            ;
    }

    // Fields to be shown on filter forms
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('username',null,array('label'=>'Correo'))
            ->add('personPerson.names',null,array('label'=>'Nombres'))
            ->add('personPerson.lastName1',null,array('label'=>'Primer Apellido'))
            ->add('personPerson.lastName2',null,array('label'=>'Segundo Apellido'))
            ->add('personPerson.document',null,array('label'=>'Numero de documento empleador'))
            ->add('personPerson.employer.employerHasEmployees.employeeEmployee.personPerson.document',null,array('label'=>'Numero de documento empleado'))
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id','sonata_type_model_hidden')
            ->add('username','text',array('label'=>'Usuario','translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('dateCreated','date', array('show_filter'=>true,'label'=>'Fecha de creacion','años'=> range(1910,2050),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson','sonata_type_admin')
            ->add('status', 'choice', array(
                'label'=>'Estado',
                'choices'  => array('0' => 'Usuario inactivo', '1' => 'E-mail confirmado' ,'2' => 'Usuario pago')
            ))
            ->add('is_free',null,array('show_filter'=>true,'label'=>'Meses gratis','translation_domain' => 'RocketSellerTwoPickBundle','editable'=>true))
            ->add('dataCreditStatus','choice', array(
                'label'=>'Estado',
                'choices'  => array(
                    '0' => 'No Enviado',
                    '1' => 'Enviado',
                    '2' => 'Aprobado',
                    '3' => 'No Aprobado',
                    '4' => 'Exedio el numero de intentos',
                    '5' => 'DataCredito Toteo')
            ))
            ->add('realProcedure',null,array('label'=>'Tramites','associated_property'=>'procedureTypeProcedureType.name'))
//            ->add('realProcedure','sonata_type_admin',array(
//                'route'=>array('name'=>'show'),
//                'label'=>'Tramites',
//            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array()
                )
            ))

            ;
    }

    // Fields to be shown on show action
    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {

        $showMapper
            ->add('id')
            ->add('email','email',array('label'=>'Correo'))
            ->add('personPerson','sonata_type_admin',array('label'=>'Nombre','route'=>array('name'=>'show')))
            ->add('dateCreated',null,array('label'=>'Fecha de creación'))
            ->add('')

        ;
    }

}
