<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PurchaseOrdersDescriptionController extends Controller
{
		public function indexAction()
    {
		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

		$product = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:Product')->findOneBy(array("simpleName"=>"PP"));
		$pod = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$product->getIdProduct()));

		$cPod = array();

		/*Los tipo E son:
			Personas dependiente de Empresas - Empleados contratados por personas naturales para trabajo comercial -Empleados de tiempo parcial afiliadas a Sisben

		  Los tipo S son:
			Empleados contratador por personas naturales para trabajo Domestico -Beneficiarios UPC adicional es decir afiliar a alguien por fuera de mi nucleo familiar directo
		*/

		$cPodFileType = array();
		$idEmployers = array();

		foreach( $pod as $singlePod){
			if( $singlePod->getPurchaseOrdersStatus() == null || $singlePod->getPurchaseOrdersStatus()->getIdNovoPay() != "-1"){
				if( count($singlePod->getPayrollsPila()) > 0 ){
					$cPod[] = $singlePod;
					$localArr = $singlePod->getPayrollsPila();
					$cPodFileType[] = $localArr[0]->getContractContract()->getPlanillaTypePlanillaType()->getCode();
				}
			}
		}

		return $this->render(
            '@RocketSellerTwoPick/BackOffice/pila.html.twig',array('pilas'=>$cPod, 'tipoPlanilla' =>$cPodFileType));
    }

		public function persistPilaEnlaceOperativoCodeAction($fileName,$idPod, $payFile){
			if($fileName != ""){
				/** @var PurchaseOrdersDescription $pod */
				$pod = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findOneBy(array("idPurchaseOrdersDescription" => $idPod));
				$oldFileName = $pod->getEnlaceOperativoFileName();
				$pod->setEnlaceOperativoFileName($fileName);
				
				if($payFile == "ok"){
					$pod->setUploadedFile(-1);
				}
				else{
					$pod->setUploadedFile(-2);
				}
				
				$em = $this->getDoctrine()->getManager();
				$em->persist($pod);
				$em->flush();

				//Si existe un PO ok
				//Si already received en 1 ok
				//Si el PO tiene IdNovopayStatus en 00 (aprobado)
				//Si la planilla esta aprobada
				//Si ya se intentó mandar a dispersar
				$po = $pod->getPurchaseOrders();
				if( !is_null($po)
							&& $po->getAlreadyRecived() == 1
								&& $po->getPurchaseOrdersStatus()->getIdNovoPay() == "00"
									&& $pod->getUploadedFile() == -1
										&& $pod->getPayPay() != null ) {
					$answerHighTech = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getDispersePurchaseOrder', array('idPurchaseOrder' => $po->getIdPurchaseOrders()), array('_format' => 'json'));
				}

			}

			return $this->redirectToRoute("show_pilas");
		}
}
