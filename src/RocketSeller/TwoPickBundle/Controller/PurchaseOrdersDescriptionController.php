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

		$productIdToSearch = 7;
		$statusIdToSearch = 23;

		$pod = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findBy(array("productProduct"=>$productIdToSearch));

		$cPod = array();

		/*Los tipo E son:
			Personas dependiente de Empresas - Empleados contratados por personas naturales para trabajo comercial -Empleados de tiempo parcial afiliadas a Sisben

		  Los tipo S son:
			Empleados contratador por personas naturales para trabajo Domestico -Beneficiarios UPC adicional es decir afiliar a alguien por fuera de mi nucleo familiar directo
		*/

		$cPodFileType = array();
		$idEmployers = array();

		foreach( $pod as $singlePod){
			if($singlePod->getPurchaseOrdersStatus()->getIdNovoPay() != "-1"){
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
		
		public function persistPilaEnlaceOperativoCodeAction($id,$idPod){
			if($id != ""){
				$pod = $this->getdoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersDescription')->findOneBy(array("idPurchaseOrdersDescription" => $idPod));
				$pod->setEnlaceOperativoFileName($id);

				$em = $this->getDoctrine()->getManager();
				$em->persist($pod);
				$em->flush();
			}

			return $this->redirectToRoute("show_pilas");
		}
}
