<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitiesEmployerPick extends AbstractType
{
    private $severancesEntities;
    private $arlEntities;
    function __construct($severancesEntities,$arlEntities){
        $this->severancesEntities=$severancesEntities;
        $this->arlEntities=$arlEntities;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '
            ))
            ->add('arl', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->arlEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '
            ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee',
        ));
    }
    
    public function getName()
    {
        return 'pick_entities_employer';
    }
} 
?>