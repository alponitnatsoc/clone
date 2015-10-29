<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\ContractRegistration;
class ContractController extends Controller
{	
	/**
    * @param 
    * @return 
	**/
    public function addContractAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $employerHasEmployee=$repository->find($id);
        $contracts=$employerHasEmployee->getContracts();
        $contract=false;
        foreach ($contracts as $key => $value) {
        	if ($value->getState()=="activo") {
        		$contract= $value;
        	}
        }
        if ($contract===false) {
        	$contract= new Contract();
        }

		$form = $this->createForm(new ContractRegistration());

		$form->handleRequest($request);

        if ($form->isValid()) {
        	$benefits=$form->get('benefits')->getData();

        	foreach ($benefits as $key => $value) {
        		$contract->addBenefit(new ContractHasBenefits($contract,$value));
        	}
        	setEmployerHasEmployeeEmployerHasEmployee($user->getPersonPerson()->getEmployer());
            $em = $this->getDoctrine()->getManager();
            $em->persist($contract);
            $em->flush();

            return $this->redirectToRoute('show_dashboard');
        }
        return $this->render('RocketSellerTwoPickBundle:Contract:contractForm.html.twig',
            array('form' => $form->createView()));
    }
}
 ?>