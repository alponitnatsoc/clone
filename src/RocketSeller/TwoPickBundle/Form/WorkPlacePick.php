<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkPlacePick extends AbstractType
{
    private $workplaces;
    function __construct($workplaces){
        $this->workplaces=$workplaces;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('workplaces', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Workplace',
                'placeholder' => '',
                'choices' => $this->workplaces,
                'property' => 'mainAddress',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'workplaceWorkplace',
                "label" => "Dirección"
                ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace',
        ));
    }
    
    public function getName()
    {
        return 'pick_workplaces';
    }
} 
?>