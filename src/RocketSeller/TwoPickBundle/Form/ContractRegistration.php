<?php 

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Department;

class ContractRegistration extends AbstractType
{
    private $workplaces;
    private $benefits;
    function __construct($workplaces){
        $this->workplaces=$workplaces;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('employeeType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:EmployeeContractType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'employeeContractTypeEmployeeContractType',
            ))
            ->add('contractType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:ContractType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'contractTypeContractType',
            ))
            ->add('timeCommitment', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:TimeCommitment',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'timeCommitmentTimeCommitment',
            ))
            ->add('position', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:TimeCommitment',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'positionPosition',
            ))
            ->add('salary', 'money', array(
                'constraints' => array(
                    new NotBlank(),),
                'currency' => 'COP',
                'property_path' => 'salary',
                ))

            ->add('benefits', 'collection', array(
                'type' => new BenefitPick(),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                ))

            
            ->add('workplaces', 'collection', array(
                'type' => new WorkPlacePick($this->workplaces),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                ))
            ->add('payMethod', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:PayType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'property_path' => 'payTypePayType',
            ));


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Contract',
        ));
    }
    
    public function getName()
    {
        return 'add_contract';
    }
} 
?>