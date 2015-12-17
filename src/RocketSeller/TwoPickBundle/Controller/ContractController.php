<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\ContractHasBenefits;
use RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\ContractRegistration;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use FOS\RestBundle\Routing\Loader\Reader\RestActionReader;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Entity\Payroll;

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;

class ContractController extends Controller
{

    use EmployerHasEmployeeMethodsTrait;

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
            array('form' => $form->createView())
        );
    }
    /**
    * @param Id el id de la relación entre empleado y empleador EmployerHasEmployee
    * @return
	**/
    public function showContractsAction(Request $request, $id)
    {

        $contracts = $this->showContracts($id);

        return $this->render(
            'RocketSellerTwoPickBundle:Contract:contractManager.html.twig',array(
                'contracts'=>$contracts,
                'idEmployerEmployee' =>$id)
        );
    }

    /**
     * @param Request $request
     * @param integer $id El Id del contrato a ver
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewContractAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $details = array();

        $contractRepository = $em->getRepository("RocketSellerTwoPickBundle:Contract");
        /** @var Contract $contract */
        $contract = $contractRepository->findOneBy(
            array(
                "idContract" => $id
            )
        );

        $employee = $contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();

        $details["employee"]["name"] = $employee->getNames() . " " . $employee->getLastName1() . " " . $employee->getLastName2();
        $details["employee"]["document"]["type"] = $employee->getDocumentType();
        $details["employee"]["document"]["number"] = $employee->getDocument();

        $details["contract"]["type"] = $contract->getContractTypeContractType()->getName();
        $benefits = $contract->getBenefits();
        if (is_array($benefits)) {
            foreach($benefits as $benefit) {
                $details["contract"]["benefits"][] = $benefit;
            }
        }

        $documents = $contract->getDocumentDocument();
        if ($documents) {
            $details["contract"]["document"] = $documents->getName();
        }

        $details["contract"]["employeeType"] = $contract->getEmployeeContractTypeEmployeeContractType()->getName();
        $details["contract"]["salary"] = $contract->getSalary();
        $details["contract"]["payMethod"] = $contract->getPayMethodPayMethod()->getPayTypePayType()->getName();

        $payrolls = $contract->getPayrolls()->getValues();
        if (is_array($payrolls)) {
            /** @var Payroll $payroll */
            foreach($payrolls as $key => $payroll) {
                $details["contract"]["payrolls"][$key] = $payroll;
            }
        }
        $details["contract"]["position"] = $contract->getPositionPosition()->getName();
        $details["contract"]["state"] = $contract->getState();
        $details["contract"]["timeCommitment"] = $contract->getTimeCommitmentTimeCommitment()->getName();

        $workplaces = $contract->getWorkplaces()->getValues();
        if (is_array($workplaces)) {
            /** @var ContractHasWorkplace $workplace */
            foreach($workplaces as $key => $workplace) {
                $details["contract"]["workplaces"][$key]["city"] = $workplace->getWorkplaceWorkplace()->getCity();
                $details["contract"]["workplaces"][$key]["mainAddress"] = $workplace->getWorkplaceWorkplace()->getMainAddress();
            }
        }

        return $this->render('RocketSellerTwoPickBundle:Contract:view-contract.html.twig',
            array(
                "contract" => $details,
                'idContract' => $id
            )
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