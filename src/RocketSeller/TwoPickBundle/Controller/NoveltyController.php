<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Document\Media;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\NotificationEmployer;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PayrollDetail;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\NoveltyForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoveltyController extends Controller
{

    public function selectNoveltyAction($idPayroll,Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('noveltyType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:NoveltyType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,
                'label' => 'Tipo de novedad',
                'property_path' => 'noveltyTypeNoveltyType'))
            ->add('save', 'submit', array(
                'label' => 'Create',
            ))
            ->getForm();
        $form->handleRequest($request);
        if($form->isValid()){
            /** @var NoveltyType $noveltyType */
            $noveltyType=$form->get('noveltyType')->getData();

            return $this->redirectToRoute('novelty_add', array(
                'idPayroll' => $idPayroll,
                'noveltyTypeId' => $noveltyType->getIdNoveltyType()), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:selectNovelty.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @param $idPayroll
     * @param $noveltyTypeId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addNoveltyAction($idPayroll, $noveltyTypeId, Request $request)
    {
        $payRollRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        /** @var Payroll $payRol */
        $payRol=$payRollRepo->find($idPayroll);
        if($payRol==null){
            return $this->redirectToRoute('ajax', array(), 301);
        }
        $payRollDetail= new PayrollDetail();
        $novelty=new Novelty();
        $novelty->setPayrollDetailPayrollDetail($payRollDetail);
        $noveltyTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        /** @var NoveltyType $noveltyType */
        $noveltyType=$noveltyTypeRepo->find($noveltyTypeId);
        if($noveltyType==null){
            return $this->redirectToRoute('novelty_select', array('idPayroll'=>$idPayroll), 301);
        }
        $novelty->setNoveltyTypeNoveltyType($noveltyType);
        $requiredDocuments=$noveltyType->getRequiredDocuments();
        $hasDocuments=false;
        $user=$this->getUser();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach($requiredDocuments as $rd){
            $hasDocuments=true;
            $tempDoc=new Document();
            if($rd->getPersonType()=="employer"){
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson());
            }else if($rd->getPersonType()=="employee"){
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson());
            }else{
                $tempDoc->setContractContract($payRol->getContractContract());
            }
            $tempDoc->setDocumentTypeDocumentType($rd->getDocumentTypeDocumentType());
            $tempDoc->setName(str_replace(" ", "_", $rd->getDocumentTypeDocumentType()->getName()));
            $novelty->addDocument($tempDoc);
        }
        $requiredFields=$noveltyType->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields,$hasDocuments),$novelty);
        $form->handleRequest($request);
        if($form->isValid()){

            $novelty->setName($noveltyType->getName());
            $payRollDetail->setPayrollPayroll($payRol);
            $em=$this->getDoctrine()->getEntityManager();
            $em->persist($novelty);
            $em->flush();
            if ($form->get('later')->isClicked()||!$this->checkNoveltyFulfilment($novelty, $form)) {
                /** @var User $user */
                $user=$this->getUser();
                $notificationEmployer=new NotificationEmployer();
                $notificationEmployer->setEmployerEmployer($user->getPersonPerson()->getEmployer());
                $notificationEmployer->setStatus(-1);
                $notificationEmployer->setDescription("Faltan llenar algunos datos de la novedad");
                $em->persist($notificationEmployer);
                $em->flush();
                $notificationEmployer->setRelatedLink($this->generateUrl("novelty_edit",array('noveltyId' => $novelty->getIdNovelty(),'notificationReferenced'=>$notificationEmployer->getId())));
                $em->persist($notificationEmployer);
                $em->flush();

            }
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig',
            array('form' => $form->createView()));
    }
    public function editNoveltyAction($noveltyId,$notificationReferenced,Request $request)
    {

        $noveltyRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Novelty');
        /** @var Novelty $novelty */
        $novelty=$noveltyRepo->find($noveltyId);
        if($novelty==null){
            return $this->redirectToRoute('ajax', array(), 301);
        }
        if($novelty->getDocuments()->count()==0)
            $hasDocuments=false;
        else
            $hasDocuments=true;

        $requiredFields=$novelty->getNoveltyTypeNoveltyType()->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields,$hasDocuments),$novelty);
        $form->handleRequest($request);
        if($form->isValid()){
            $em=$this->getDoctrine()->getEntityManager();
            $em->persist($novelty);
            $em->flush();
            if (!($form->get('later')->isClicked()||!$this->checkNoveltyFulfilment($novelty,$form))) {
                if($notificationReferenced!=-1) {
                    $notificationEmployerRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NotificationEmployer");
                    /** @var NotificationEmployer $notificationEmployer */
                    $notificationEmployer=$notificationEmployerRepo->find($notificationReferenced);
                    $notificationEmployer->setStatus(0);
                    $notificationEmployer->setSawDate(new \DateTime());
                    $notificationEmployer->setRelatedLink(null);
                    $notificationEmployer->setDescription("Los datos de la novedad se llenaron correctamente");
                    $em->persist($notificationEmployer);
                    $em->flush();
                }

            }else{
                if($notificationReferenced==-1){
                    /** @var User $user */
                    $user=$this->getUser();
                    $notificationEmployer=new NotificationEmployer();
                    $notificationEmployer->setEmployerEmployer($user->getPersonPerson()->getEmployer());
                    $notificationEmployer->setRelatedLink($this->generateUrl("novelty_edit",array('noveltyId' => $novelty->getIdNovelty())));
                    $notificationEmployer->setStatus(-1);
                    $notificationEmployer->setDescription("Faltan llenar algunos datos de la novedad");
                    $em->persist($notificationEmployer);
                    $em->flush();
                    $notificationEmployer->setRelatedLink($this->generateUrl("novelty_edit",array('noveltyId' => $novelty->getIdNovelty(),'notificationReferenced'=>$notificationEmployer->getId())));
                    $em->persist($notificationEmployer);
                    $em->flush();
                }
            }
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @param Novelty $novelty
     * @param Form $form
     * @return bool
     */
    private function checkNoveltyFulfilment($novelty,$form)
    {
        //check each document
        $noveltyType=$novelty->getNoveltyTypeNoveltyType();
        $documents=$novelty->getDocuments();
        $requiredDocuments=$noveltyType->getRequiredDocuments();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach($requiredDocuments as $rd){
            /** @var Document $document */
            foreach ($documents as $document) {
                if($rd->getDocumentTypeDocumentType()==$document->getDocumentTypeDocumentType()){
                    if($document->getMediaMedia()==null){
                        return false;
                    }
                }

            }
        }
        //check each field
        $requiredFields=$noveltyType->getRequiredFields();
        /** @var NoveltyTypeFields $field */
        foreach ($requiredFields as $field) {
            if($form->get($field->getColumnName())->getData()==null)
                return false;
        }
        return true;
    }
}
