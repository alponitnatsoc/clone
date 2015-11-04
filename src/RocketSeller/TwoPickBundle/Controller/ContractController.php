<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\ContractRegistration;
use Doctrine\Common\Collections\ArrayCollection;
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

        $contract->addWorkplace(new ContractHasWorkplace($contract,null));
        $contract->addBenefit(new ContractHasBenefits($contract,null));


        //modificar los workplaces
		$form = $this->createForm(new ContractRegistration($userWorkplaces),$contract);

		$form->handleRequest($request);

        if ($form->isValid()) {
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
        $current= new ArrayCollection();
        $current['benefits']= new ArrayCollection();
        $current['workplaces']= new ArrayCollection();
        foreach ($contract->getBenefits() as $value) {
        	$current['benefits']->add($value);
        }
        foreach ($contract->getWorkplaces() as $key =>  $value) {
        	$current['workplaces']->add($value);
        }
		$form->handleRequest($request);

        if ($form->isValid()) {
        	foreach ($current['benefits'] as $benefit) {
	            if (false === $contract->getBenefits()->contains($benefit)) {
                    $em->remove($benefit);
	            }
	        }
	        foreach ($current['workplaces'] as $workplace) {
	            if (false === $contract->getWorkplaces()->contains($workplace)) {
                    $em->remove($workplace);
	            }
	        }
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