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
            ->add('tipo', 'choice',array('choices'=>array('dias'=>'Por Días','completo'=>'Tiempo Completo'), 'expanded'=>true,'multiple'=>false,'label'=>'1. Por favor seleccione el tipo de empleado y la base del cálculo'))
            ->add('salarioM', 'money',array('currency'=>'COP','label'=>'Salario Mensual'))
            ->add('salarioD', 'money',array('currency'=>'COP','label'=>'Salario Diario'))
            ->add('numberOfDays', 'choice',array(
                'choices'=>array(
                    '1'=>'1',
                    '2'=>'2',
                    '3'=>'3',
                    '4'=>'4',
                    '5'=>'5',
                    '6'=>'6',
                    '7'=>'7',
                    '8'=>'8',
                    '9'=>'9',
                    '10'=>'10',
                    '11'=>'11',
                    '12'=>'12',
                    '13'=>'13',
                    '14'=>'14',
                    '15'=>'15',
                    '16'=>'16',
                    '17'=>'17',
                    '18'=>'18',
                    '19'=>'19',
                    '20'=>'20',
                    '21'=>'21',
                    '22'=>'22',
                    '23'=>'23',
                    '24'=>'24',
                    '25'=>'25',
                    '26'=>'26',
                    '27'=>'27',
                    '28'=>'28',
                    '29'=>'29',
                    '30'=>'30',
                ),
                'label'=>'Número de días trabajados al mes'))
            ->add('transporte', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Ese valor incluye el subsidio de transporte?'))
            ->add('auxilio', 'choice',array('choices'=>array('1'=>'si','0'=>'no'), 'expanded'=>true,'multiple'=>false,'label'=>'¿Le paga bonificación / auxilio anual?'))
            ->add('auxilioD', 'money',array('currency'=>'COP','label'=>' ','data'=>'0'))
            ->add('save', 'submit', array('label' => 'Create Task'))
            ->getForm();

        return $this->render('RocketSellerTwoPickBundle:Calculator:showCalculatorForm.html.twig',
            array('form' => $form->createView()));
    }
}
 ?>