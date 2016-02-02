<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use DateTime;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodController extends Controller
{	

    public function indexAction(Request $request, $idNotification)
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
            //TODO NovoPayment
            if($idNotification!=-1){
                $notificationRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification");
                /** @var Notification $realNotif */
                $realNotif=$notificationRepo->find($idNotification);
                if($realNotif!=null){
                    $realNotif->setStatus(-1);
                    $realNotif->setSawDate(new DateTime());
                    $realNotif->setRelatedLink(null);
                    $em=$this->getDoctrine()->getManager();
                    $em->persist($realNotif);
                    $em->flush();
                }
            }

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