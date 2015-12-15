<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Document\Media;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
    public function addNoveltyAction($idPayroll,$noveltyTypeId,Request $request)
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
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach($requiredDocuments as $rd){
            $hasDocuments=true;
            $tempDoc=new Document();
            $tempDoc->setDocumentTypeDocumentType($rd->getDocumentTypeDocumentType());
            $tempDoc->setName(str_replace(" ", "_", $rd->getDocumentTypeDocumentType()->getName()));
            $novelty->addDocument($tempDoc);
        }
        $requiredFields=$noveltyType->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields,$hasDocuments),$novelty);
        $form->handleRequest($request);
        if($form->isValid()){
            if ($form->get('later')->isClicked()) {
                //add new notification
            }
            $novelty->setName($noveltyType->getName());
            $payRollDetail->setPayrollPayroll($payRol);
            $em=$this->getDoctrine()->getEntityManager();
            $em->persist($novelty);
            $em->flush();
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig',
            array('form' => $form->createView()));
    }
    public function noveltyTypeFieldsAction($noveltyId)
    {
        $idNoveltyType=$noveltyId;
        $noveltyTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');


        /** @var NoveltyType $novelty */
        $novelty= $noveltyTypeRepo->find($idNoveltyType);
        $requiredDocuments=$novelty->getRequiredDocuments();
        $form = $this->createForm(new NoveltyForm($requiredDocuments));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:generalFormRender.html.twig',
            array('form' => $form->createView())
        );
    }
}
