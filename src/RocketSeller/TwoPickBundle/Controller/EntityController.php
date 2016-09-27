<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EntityFields;
use RocketSeller\TwoPickBundle\Entity\FilterType;
use RocketSeller\TwoPickBundle\Entity\SpecificData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\EntityRegistration;
class EntityController extends Controller
{	
	/**
    * @param 
    * @return 
	**/
    public function printFormAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EntityFields');
        $query = $repository->createQueryBuilder('e')
		    ->where("e.tableReferenced = 'specific_data'")
		    ->andWhere("e.entityEntity = " . $id)
		    ->getQuery();

		$specificFields = $query->getResult();
		$fields=[];
		foreach ($specificFields as $key => $value) {
			$fields[]=$value->getName();
		}
		$form = $this->createForm(new EntityRegistration($fields));
        return $this->render('RocketSellerTwoPickBundle:Registration:entityForm.html.twig',
            array('form' => $form->createView()));
    }



}
 ?>