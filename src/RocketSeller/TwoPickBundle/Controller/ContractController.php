<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\ContractRegistration;
class ContractController extends Controller
{	
	/**
    * @param Id el id de la relación entre empleado y empleador EmployerHasEmployee
    * @return 
	**/
    public function addContractAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $userWorkplaces= $user->getPersonPerson()->getEmployer()->getWorkplaces();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $employerHasEmployee=$repository->find($id);
        $contracts=$employerHasEmployee->getContracts();
        $contract= new Contract();
        
        //modificar los workplaces
		$form = $this->createForm(new ContractRegistration($userWorkplaces),$contract);

		$form->handleRequest($request);

        if ($form->isValid()) {
        	$benefits=$form->get('benefits')->getData();

        	foreach ($benefits as $key => $value) {
        		$contract->addBenefit(new ContractHasBenefits($contract,$value));
        	}
        	$workplaces=$form->get('workplaces')->getData();
        	foreach ($workplaces as $key => $value) {
        		$contract->addWorkplace(new ContractHasWorkplace($contract,$value));
        	}
        	$contract->setEmployerHasEmployeeEmployerHasEmployee($employerHasEmployee);
            $em = $this->getDoctrine()->getManager();
            $em->persist($contract);
            $em->flush();

            return $this->redirectToRoute('show_dashboard');
        }
        return $this->render('RocketSellerTwoPickBundle:Contract:contractForm.html.twig',
            array('form' => $form->createView()));
    }
    /**
    * @param Id el id de la relación entre empleado y empleador EmployerHasEmployee
    * @return 
	**/
    public function showContractsAction(Request $request, $id)
    {
        $user=$this->getUser();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $employerHasEmployee=$repository->find($id);
        $contracts=$employerHasEmployee->getContracts();

        return $this->render(
            'RocketSellerTwoPickBundle:Contract:contractManager.html.twig',array(
                'contracts'=>$contracts,
                'idEmployerEmployee' =>$id)
        );
    }
    /**
    * @param Id el id de la relación entre empleado y empleador EmployerHasEmployee
    * @return 
	**/
    public function editContractAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        $userWorkplaces= $user->getPersonPerson()->getEmployer()->getWorkplaces();
        $contract=$repository->find($id);

        $form = $this->createForm(new ContractRegistration($userWorkplaces),$contract);

		$form->handleRequest($request);

        if ($form->isValid()) {
        	$benefits=$form->get('benefits')->getData();

        	foreach ($benefits as $key => $value) {
        		$contract->addBenefit(new ContractHasBenefits($contract,$value));
        	}
        	$workplaces=$form->get('workplaces')->getData();
        	foreach ($workplaces as $key => $value) {
        		$contract->addWorkplace(new ContractHasWorkplace($contract,$value));
        	}
        	$contract->setEmployerHasEmployeeEmployerHasEmployee($employerHasEmployee);
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