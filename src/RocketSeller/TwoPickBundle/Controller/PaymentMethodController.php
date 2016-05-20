<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Form\AddPayMethod;

class PaymentMethodController extends Controller
{

    public function indexAction(Request $request)
    {

        $form = $this->createFormBuilder()
                ->add('credit_card', 'text')
                ->add('expiry_date_year', 'text')
                ->add('expiry_date_month', 'text')
                ->add('cvv', 'text')
                ->add('name_on_card', 'text')
                ->add('save', 'submit', array('label' => 'Submit'))
                ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $user = $this->getUser();
            /** @var Person $person */
            $person = $user->getPersonPerson();
            $data = $form->getData();

            //TODO NovoPayment
            $request->setMethod("POST");
            $request->request->add(array(
                "documentType" => $person->getDocumentType(),
                "documentNumber" => $person->getDocument(),
                "accountNumber" => $form->get("credit_card")->getData(),
                "expirationYear" => $form->get("expiry_date_year")->getData(),
                "expirationMonth" => $form->get("expiry_date_month")->getData(),
                "codeCheck" => $form->get("cvv")->getData(),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsMethodRest:postAddCreditCard', array('_format' => 'json'));
            echo "Status Code Employee PayMethod: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode() . " content" . $insertionAnswer->getContent();
            if ($insertionAnswer->getStatusCode() != 201) {
                return $this->render('RocketSellerTwoPickBundle:Registration:paymentMethod.html.twig', array(
                            'form' => $form->createView(),
                            'errno' => "Not a valid Credit Card check the data again"
                ));
            }

            return $this->render('RocketSellerTwoPickBundle:Registration:cardSuccess.html.twig', array(
                        'data' => $data,
            ));
        }
        return $this->render('RocketSellerTwoPickBundle:Registration:paymentMethod.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function addCreditCardAction(Request $request)
    {
        //dump($this->getUser());
        return $this->render('RocketSellerTwoPickBundle:Registration:addCreditCard.html.twig', array(
                    'user' => $this->getUser()
        ));
    }

    public function addGenericPayMethodAction(Request $request)
    {
      $user=$this->getUser();

      $bankRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Bank");
      $bankEntities = $bankRepo->findAll();

      $accountTypeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:AccountType");
      $accountTypeEntities = $accountTypeRepo->findAll();

      $form = $this->createForm(new AddPayMethod($user, $bankEntities, $accountTypeEntities), null,array(
          'action' => $this->generateUrl('api_public_post_add_generic_pay_method', array('format'=>'json')),
          'method' => 'POST'
        ));

      return $this->render('RocketSellerTwoPickBundle:Registration:addPayMethod.html.twig', array(
                  'form' => $form->createView()
      ));
    }
}

?>
