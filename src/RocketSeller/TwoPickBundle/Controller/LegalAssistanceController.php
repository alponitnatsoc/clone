<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\AddCreditCard;

class LegalAssistanceController extends Controller
{
	public function indexAction(){

		$totalValue = $this->getPaymentValue();
		return $this->render('RocketSellerTwoPickBundle:legalAssistance:index.html.twig',
			array(
				'totalValue'=>floor($totalValue)
				)
			);
	}
	public function startPaymentAction(Request $request){
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        

        $form = $this->createForm(new AddCreditCard(), null, array(
            'action' => $this->generateUrl('legal_payment'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
        	$em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            /** @var Person $person */
            $person = $user->getPersonPerson();
            $person->setDocumentType($form->get("documentType")->getData());
            $person->setDocument($form->get("document")->getData());
            $phone = new Phone();
            $phone->setPhoneNumber($form->get("phoneNumber")->getData());
            $person->setDocument($form->get("document")->getData());
            $person->addPhone($phone);
            $em->persist($phone);
            $em->persist($person);
            $em->flush();

            $data = $form->getData();

            //TODO NovoPayment
            $request->setMethod("POST");
            $request->request->add(array(
                "documentType" => $person->getDocumentType(),
                "documentNumber" => $person->getDocument(),
                "credit_card" => $form->get("credit_card")->getData(),
                "expiry_date_year" => $form->get("expiry_date_year")->getData(),
                "expiry_date_month" => $form->get("expiry_date_month")->getData(),
                "cvv" => $form->get("cvv")->getData(),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('_format' => 'json'));

            $response = json_decode($insertionAnswer->getContent());
            $methodId = $response->{'response'}->{'method-id'};

            if ($insertionAnswer->getStatusCode() != 201) {

                return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                            'form' => $form->createView(),
                            'errno' => "Not a valid Credit Card check the data again"
                ));
            }


            return $this->payLegalAssistance($methodId);
        }
        return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                    'form' => $form->createView(),
        ));
	}
	public function getPaymentValue()
	{
		$user = $this->getUser();
        $product = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Product')
                ->findOneBySimpleName("PAL");
        $tax = ($product->getTaxTax() != null) ? $product->getTaxTax()->getValue() : 0;
        $totalValue = $product->getPrice() * (1 + $tax);
        return $totalValue;
	}
    public function payLegalAssistance($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $format = array('_format' => 'json');
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ExpressRegistrationRest:postPayRegisterExpress', array('id' => $user->getId(), 'idPayMethod' => $id), $format);

        if ($insertionAnswer->getStatusCode() == 200) {
        	$flag = $this->changeFlagAction();
            return $this->successExpress();
            exit();

            //return $this->redirectToRoute('DashBoard');
        } elseif ($insertionAnswer == 404) {
            dump("Procesando");
            exit();
        } else {
            dump($insertionAnswer);
            exit();
        }
    }
    public function changeFlagAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->setLegalFlag(1);
        $em->persist($user);
        $em->flush();

        return true;
    }
    public function successExpress()
    {
        $user = $this->getUser();
        $role = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Role')
                ->findByName("ROLE_BACK_OFFICE");

        $notification = new Notification();
        $notification->setPersonPerson($user->getPersonPerson());
        $notification->setType("Asistencia legal");
        $notification->setAccion("Asistir a usuario");
        $notification->setRoleRole($role[0]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentSuccess.html.twig');
    }


}

