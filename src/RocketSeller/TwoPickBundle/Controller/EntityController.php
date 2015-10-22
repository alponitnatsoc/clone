<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EntityFields;
use RocketSeller\TwoPickBundle\Entity\FilterType;
use RocketSeller\TwoPickBundle\Entity\SpecificData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class EntityController extends Controller
{	
	/**
    * @param 
    * @return 
	**/
    public function printFormAction(Request $request)
    {
        
        return $this->render('RocketSellerTwoPickBundle:Registration:entityForm.html.twig', 
            array('steps' => $steps ) );
    }
}
 ?>