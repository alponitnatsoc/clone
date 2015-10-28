<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class PaymentMethodController extends Controller
{	

    public function indexAction(Request $request)
    {

        return $this->render('RocketSellerTwoPickBundle:Registration:paymentMethod.html.twig'
            );
    }
}
?>