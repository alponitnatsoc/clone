<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Department;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EntityFields;
use RocketSeller\TwoPickBundle\Entity\EntityType;
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
/*
 * @param Request $request
 */
	public function entitySearchAction(Request $request) {
		
		$entityTypes = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EntityType')->findAll();
		$deparments = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department')->findAll();
		
		$entityDisplayName = array(
			"EPS" => "Salud EPS",
			"ARL" => "Riesgos laborales ARL",
			"Pension" => "Fondo de pensiones",
			"CC Familiar" => "Caja de compensación",
			"ARS" => "Salud ARS",
			"FCES" => "Fondo de cesantías",
			"Severances" => "Fondo de cesantías"
		);
		
		$entityTypeArray = array();
		
		/** @var EntityType $entityType */
		foreach ($entityTypes as $entityType) {
			$entityTypeArray[] = $entityDisplayName[$entityType->getName()];
		}
		
		$deparmentArray = array();
		
		/** @var Department $deparment */
		foreach ($deparments as $deparment) {
			$deparmentArray[] = $deparment->getName();
		}
		
		$form = $this->get('form.factory')->createNamedBuilder('formFilter')
			->add('entityType','choice', array(
				'label' => false,
				'expanded'=>false,
				'multiple'=>false,
				'placeholder' => 'Selecciona el tipo de entidad',
				'required' => false,
				'choices' => $entityTypeArray
			))
//			->add('department','choice', array(
//				'label' => false,
//				'expanded'=>false,
//				'multiple'=>false,
//				'placeholder' => 'Selecciona el lugar',
//				'required' => false,
//				'choices' => $deparmentArray
//			))
			->add('department', 'entity', array(
				"class" => "RocketSellerTwoPickBundle:Department",
				"choice_label" => 'name',
				'label' => false,
				'placeholder' => 'Selecciona el lugar',
				'required' => false,
			))
			->add('search','submit',array('label' => 'Filtrar'))->getForm();
		
		$form->handleRequest($request);
		if($form->isSubmitted() and $form->isValid()){
			$selectedEntityTypeIndex = $form->get('entityType')->getData();
			/** @var Department $selectedDepartment */
			$selectedDepartment = $form->get('department')->getData();
			
			$em = $this->getDoctrine()->getManager();
			/** @var QueryBuilder $query */
			$query = $em->createQueryBuilder();
			$query->add('select', 'E');
			$query->from("RocketSellerTwoPickBundle:Entity",'E')
				->leftJoin("E.entityTypeEntityType",'et')
				->leftJoin("E.departments",'d');
			
			
			if($selectedEntityTypeIndex === null && $selectedDepartment === null) {
				$entities = $query->getQuery()->getResult();
			} else {
				if($selectedDepartment === null) {
					/** @var EntityType $selectedEntityType */
					$selectedEntityType = $entityTypes[$selectedEntityTypeIndex];
					$query->andWhere($query->expr()->eq(
						'et.payroll_code', '?1'
					))
						->setParameter(1, $selectedEntityType->getPayrollCode());
					$entities = $query->getQuery()->getResult();
				} elseif ($selectedEntityTypeIndex === null) {
					$query->andWhere($query->expr()->eq(
						'd.departmentCode', '?2'
					))
						->setParameter(2, $selectedDepartment->getDepartmentCode());
					$entities = $query->getQuery()->getResult();
				} else {
					$selectedEntityType = $entityTypes[$selectedEntityTypeIndex];
					
					$query->andWhere($query->expr()->andX(
							$query->expr()->eq('et.payroll_code', '?3'),
							$query->expr()->eq('d.departmentCode', '?4'))
					)
						->setParameter(3, $selectedEntityType->getPayrollCode())
						->setParameter(4, $selectedDepartment->getDepartmentCode());
					
					$entities = $query->getQuery()->getResult();
				}
			}
			
			return $this->render('RocketSellerTwoPickBundle:Public:entidades.html.twig', array(
				'form' => $form->createView(),
				'entities' => $entities,
				'entityDisplayName' => $entityDisplayName
			));
		}
		return $this->render('RocketSellerTwoPickBundle:Public:entidades.html.twig', array(
				'form' => $form->createView()
			));
	}

}
 ?>