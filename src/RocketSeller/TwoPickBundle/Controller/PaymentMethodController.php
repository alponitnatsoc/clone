<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class PaymentMethodController extends Controller
{	

    public function indexAction(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('credit_card', 'text')
            ->add('expiry_date', 'text')
            ->add('cvv', 'text')
            ->add('name_on_card', 'text')
            ->add('save', 'submit', array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {     
            $data = $form->getData();      
            return $this->render('RocketSellerTwoPickBundle:Registration:cardSuccess.html.twig', array(
                'data' => $data,
                ));
        }
            return $this->render('RocketSellerTwoPickBundle:Registration:paymentMethod.html.twig', array(
                'form' => $form->createView(),
        ));
    }
}
?>