<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Liquidation;
use RocketSeller\TwoPickBundle\Traits\GetTransactionDetailTrait;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;


class ExpressRegistrationRestController extends FOSRestController
{


    /**
     * Obtener pagos de un empleado relacionado con un empleador employerhasemployee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener las liquidaciones de un empleado",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la relacion EmployerHasEmployee
     *
     * @return View
     */
    public function getPaymentAction($id)
    {
    	$em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepository->findOneBy(
                array(
                    "id" => $id
                )
        );
    	$person = $user->getPersonPerson();
        $date = new \DateTime('02/31/1970');
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType"=>$person->getDocumentType(),
            "documentNumber"=>$person->getDocument(),
            "name"=>$person->getNames(),
            "lastName"=>$person->getLastName1()." ".$person->getLastName2(),
            "year"=>$date->format("Y"),
            "month"=>$date->format("m"),
            "day"=>$date->format("d"),
            "phone"=>$person->getPhones()->get(0)->getPhoneNumber(),
            "email"=>$user->getEmail()
            ));
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClient', array('_format' => 'json'));
      
        return $insertionAnswer;

    }
    /**
     * 200 tiene que crearlo y lo crea.
     * 400 si no lo esta proscesando.
     */
    public function postPayRegisterExpressAction($id,$idPayMethod)
    {
    	$em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepository->findOneBy(
                array(
                    "id" => $id
                )
        );
        $repository = $this->getDoctrine()
						   ->getRepository('RocketSellerTwoPickBundle:PurchaseOrders');
		$repositoryP = $this->getDoctrine()
						   ->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
		$purchaseOrderStatus = $repositoryP->findOneBy(
								array(
										"idNovoPay"=>"00",
									)
								);				   		
	    $PurchaseOrderExpressByUser = $repository->findBy(
								    array('idUser' => $user->getId(),
								    	  'name'=>'Registro express',
								    	  'purchaseOrdersStatus'=>$purchaseOrderStatus
								    	)
								);
	    $PurchaseOrderExpressNullByUser = $repository->findBy(
								    array('idUser' => $user->getId(),
								    	  'name'=>'Registro express',
								    	  'purchaseOrdersStatus'=>NULL
								    	)
								);	    
	    if (!$PurchaseOrderExpressByUser || $PurchaseOrderExpressNullByUser) {
		        $person = $user->getPersonPerson();
		        $PurchaseOrders = new PurchaseOrders();
		        $PurchaseOrders->setIdUser($user);
		        $PurchaseOrders->setPayMethodId($id);
		        $PurchaseOrders->setName("Registro express");

		        $PurchaseOrdersDescription = new PurchaseOrdersDescription();        
		        $product = $this->getDoctrine()
		        ->getRepository('RocketSellerTwoPickBundle:Product')
		        ->findOneBySimpleName("PRE");
		     
		        $PurchaseOrdersDescription->setProductProduct($product);
		        $value = $product->getPrice() * (1 + $product->getTaxTax()->getValue());
		        $PurchaseOrders->setValue($value);
		        $PurchaseOrdersDescription->setValue($value);
		        $PurchaseOrdersDescription->setDescription($product->getDescription());
		        $em->persist($PurchaseOrdersDescription);
		        $PurchaseOrders->addPurchaseOrderDescription($PurchaseOrdersDescription);
		        $em->persist($PurchaseOrders);
		        $em->flush();
		        $format = array('_format'=>'json');
		        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array('idPurchaseOrder' => $PurchaseOrders->getIdPurchaseOrders()),$format);
		        $user->setExpress(2);
		        $em->persist($user);
		        $em->flush();

		        if ($insertionAnswer->getStatusCode()==200) {
		        	$user = $this->getUser();
			        $role = $this->getDoctrine()
			        ->getRepository('RocketSellerTwoPickBundle:Role')
			        ->findByName("ROLE_BACK_OFFICE");
			        
			        $notification = new Notification();
			        $notification->setPersonPerson($user->getPersonPerson());
			        $notification->setType("Registro express");
			        $notification->setAccion("Registrar usuario");
			        $notification->setRoleRole($role[0]);
			        $em = $this->getDoctrine()->getManager();
			        $em->persist($notification);
			        $em->flush();
		        }
		    	return $insertionAnswer;  	
	    }else{
	    	$insertionAnswer = 400;
	    	return 404;
	    }


    }

}
 ?>