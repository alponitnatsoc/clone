<?php
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\EntityType;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\PersonBeneficiaryRegistration;
use Doctrine\Common\Collections\ArrayCollection;

class PersonController extends Controller
{


    /**
    * Maneja la edición de una  persona con los datos básicos,
    * @return La vista de el formulario de editar persona
    **/
    public function editPersonAction()
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user=$this->getUser();
        if($user->getStatus()>=2 )
            return $this->forward('RocketSellerTwoPickBundle:DashBoardEmployer:showDashBoard');
        /** @var Person $people */
        $people = $user->getPersonPerson();
        $employer = $people->getEmployer();
        if ($employer==null) {
            $employer=new Employer();
            $people->setEmployer($employer);
        }

        if (count($employer->getWorkplaces())==0) {
            $workplace = new Workplace();
            $employer->addWorkplace($workplace);
            $people->setEmployer($employer);
        }
        $employer->setEmployerType("persona");
        if($people->getPhones()->count()==0||$people->getPhones()==null){
            $phone=new Phone();
            $people->addPhone($phone);
        }
        $entityTypeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EntityType");
        $entityTypes = $entityTypeRepo->findAll();
        $severances = null;
        $arls = null;


        /** @var EntityType $entityType */
        foreach ($entityTypes as $entityType) {
            if ($entityType->getName() == (isset($configData['ARL']) ? $configData['ARL'] : "ARL")) {
                $arls = $entityType->getEntities();
            }
            if ($entityType->getName() == (isset($configData['CC Familiar']) ? $configData['CC Familiar'] : "CC Familiar")) {
                $severances = $entityType->getEntities();
            }
        }
        if($employer->getEntities()->count()==0 ){
            $ehEtities= new EmployerHasEntity();
            $employer->addEntity($ehEtities);
        }
        $form = $this->createForm(new EmployerRegistration($severances,$arls), $employer, array(
            'action' => $this->generateUrl('api_public_post_edit_person_submit_step3', array('format'=>'json')),
            'method' => 'POST',
        ));
        $form->get("documentExpeditionDate")->setData($people->getDocumentExpeditionDate());

        $empEntities = $employer->getEntities();
        $actualSeverances= new ArrayCollection();
        if ($empEntities&&$empEntities->count() != 0) {
            /** @var EmployerHasEntity $enti */
            foreach ($empEntities as $enti) {
                if ($enti->getEntityEntity()!=null &&$enti->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "ARP") {
                    $form->get('arl')->setData($enti->getEntityEntity());
										$form->get('arlExists')->setData($enti->getState());
                }else {
                    $actualSeverances->add($enti);
                }
            }
        }
        $form->get('severances')->setData($actualSeverances);

        return $this->render(
            'RocketSellerTwoPickBundle:Registration:newPerson.html.twig',
            array('form' => $form->createView())
        );
    }
    /*
    /**
    * persiste la edición de una  persona con los datos básicos,
    * @param el Request que manjea el form que se envia por post
    * @return La vista de el formulario de editar persona
    *
    public function editPersonSubmitAction(Request $request)
    {
        $user=$this->getUser();
        $people = $user->getPersonPerson();
        $employer=$people->getEmployer();
        if ($employer==null) {
            $employer=new Employer();
        }
        if (count($employer->getWorkplaces())==0) {
            $workplace = new Workplace();
            $employer->addWorkplace($workplace);
            $people->setEmployer($employer);
        }
        $form = $this->createForm(new EmployerRegistration(), $employer, array(
            'action' => $this->generateUrl('edit_profile_submit') ,
            'method' => 'POST',
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $workplaces = new ArrayCollection();
            foreach ($user->getPersonPerson()->getEmployer()->getWorkplaces() as $work) {
                $workplaces->add($work);
            }

            foreach ($workplaces as $work) {
                if (false === $employer->getWorkplaces()->contains($work)) {
                    // remove the Task from the Tag
                    $work->setEmployerEmployer(null);
                    $em->persist($work);
                    $em->remove($work);
                }
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('show_dashboard');
        }
    }*/


}
?>
