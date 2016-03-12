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
            ->add('tipo', 'choice',array('choices'=>array('days'=>'Por Días','complete'=>'Tiempo Completo'), 'expanded'=>true,'multiple'=>false,'label'=>'1. Por favor seleccione el tipo de empleado y la base del cálculo'))
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
            ->add('transporte', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Ese valor incluye el subsidio de transporte?'))
            ->add('auxilio', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Le paga bonificación / auxilio anual?'))
            ->add('sisben', 'choice',array('choices'=>array('1'=>'si','0'=>'no'),'empty_value'=>false, 'expanded'=>true,'multiple'=>false,'label'=>'¿Pertenece al Sisben?',"required"=>false))
            ->add('auxilioD', 'money',array(
                'currency'=>'COP',
                'label'=>' ',
                'data'=>'0',
                'required'=>false,
                'attr' => array(
                    'onclick' => 'formatMoney($(this))'
                )
            ))
            ->add('save', 'submit', array('label' => 'Calculate'))
            ->getForm();

        return $this->render('RocketSellerTwoPickBundle:Calculator:showCalculatorForm.html.twig',
            array('form' => $form->createView())
        );
    }
}