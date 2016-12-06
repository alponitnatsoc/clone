<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CalculatorController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showCalculatorAction()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('api_public_post_calculator_submit', array('format'=>'json')))
            ->setMethod('POST')
            ->add('tipo', 'choice', array(
                'choices' => array(
                    'days' => 'Por Días',
                    'complete' => 'Tiempo Completo'
                ),
                'expanded'=>true,
                'multiple'=>false,
                'label'=>'Seleccione el tipo de empleado y la base del cálculo'))
            ->add('salarioM', 'money', array(
                'currency'=>'COP',
                'label'=>'Salario Mensual',
                'required'=>false,
                'attr' => array(
                    'onclick' => 'formatMoney($(this))'
                )
            ))
            ->add('salarioD', 'money', array(
                'currency'=>'COP',
                'label'=>'Salario Diario',
                'required'=>false,
                'attr' => array(
                    'onclick' => 'formatMoney($(this))'
                )
            ))
            ->add('numberOfDays', 'choice',array(
                'choices'=>range(1,30),
                'label'=>'Número de días trabajados al mes'))
            ->add('transporte', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Residirá en el lugar de trabajo?'))
            /*
		        ->add('auxilio', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Le paga bonificación / auxilio anual?'))
            ->add('auxilioD', 'money',array(
                'currency'=>'COP',
                'label'=>' ',
                'data'=>'0',
                'required'=>false,
                'attr' => array(
                    'onclick' => 'formatMoney($(this))'
                )
            ))
            ->add('sisben', 'choice',array('choices'=>array('1'=>'si','0'=>'no'),'empty_value'=>false, 'expanded'=>true,'multiple'=>false,'label'=>'¿Pertenece al Sisben?',"required"=>false))
	          */
            ->add('pension', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿El empleado requiere aportar a pensión?'))
		        ->add('position', 'entity', array(
			        'class' => 'RocketSellerTwoPickBundle:Position',
			        'property' => 'name',
			        'multiple' => false,
			        'expanded' => false,
			        'property_path' => 'positionPosition',
			        'label'=>'Defina el cargo que asignará a su empleado:',
			        'placeholder' => 'Seleccionar una opción',
			        'required' => true
		        ))
            ->add('save', 'submit', array('label' => 'Calcular'))
            ->add('minimumTC', 'button', array(
                'label' => 'Salario Mínimo Mensual',
                'attr' => array(
                    'onclick' => 'setMinimumTC()'
                )
            ))
            ->add('minimumXD', 'button', array(
                'label' => 'Salario Mínimo Diario',
                'attr' => array(
                    'onclick' => 'setMinimumXD()'
                )
            ))
            ->getForm();

        return $this->render('RocketSellerTwoPickBundle:Calculator:showCalculatorForm.html.twig',
            array('form' => $form->createView())
        );
    }
}
