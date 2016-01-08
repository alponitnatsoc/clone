<?php 

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AffiliationEmployerEmployee extends AbstractType
{

    private $employer;
    private $employees;
    private $wealthEntities;
    private $pensionEntities;
    function __construct($employer,$employees,$wealthEntities,$pensionEntities){
        $this->employer=$employer;
        $this->employees=$employees;
        $this->wealthEntities=$wealthEntities;
        $this->pensionEntities=$pensionEntities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('idEmployer', 'hidden', array(
                'data' => $this->employer->getIdEmployer(),
                'mapped' => false,
            ));
        $i=0;
        /** @var Employee $employee */
        foreach ($this->employees as $employee) {
            $builder
                ->add('nameEmployee'.$i, 'text', array(
                    'data' => $employee->getPersonPerson()->getNames(),
                    'mapped' => false,
                    'read_only'=>true,
                    'disabled'=>true,
                    'label'=>' '
                ))
                ->add('wealth'.$i, 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:Entity',
                        'choices' => $this->wealthEntities,
                        'choice_label' =>'name',
                        'label'=>' '
                    ))
                ->add('pension'.$i, 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:Entity',
                        'choices' => $this->pensionEntities,
                        'choice_label' =>'name',
                        'label'=>' '
                    ))
                ->add('beneficiaries'.$i, 'choice', array(
                        'choices' => array(
                            1 => 'Si',
                            0 => 'No',
                        ),
                        'multiple' => false,
                        'expanded' => true,
                        'label'=>' '
                ))
                ->add('idEmployee'.$i, 'hidden', array(
                    'data' => $employee->getIdEmployee(),
                    'mapped' => false,
                ));
            $i++;
        }
        $builder

            ->add('save', 'submit', array(
                'label' => 'Save',
            ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
    
    public function getName()
    {
        return 'register_social_security';
    }
} 
?>