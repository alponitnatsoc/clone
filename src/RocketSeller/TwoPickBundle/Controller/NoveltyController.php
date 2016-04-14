<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Document\Media;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PayrollDetail;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\NoveltyForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoveltyController extends Controller {

    public function selectNoveltyAction($idPayroll, Request $request) {
        $noveltyTypeRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NoveltyType");
        $noveltyTypes=$noveltyTypeRepo->findAll();
        $noveltyTypesGroups=array();
        $noveltyTypeToShow=new ArrayCollection();
        /** @var NoveltyType $NT */
        foreach ($noveltyTypes as $NT) {
            if($NT->getGrupo()!="no_show"){
                $noveltyTypeToShow->add($NT);
            }
            if(!isset($noveltyTypesGroups[$NT->getGrupo()])&&$NT->getGrupo()!="no_show"){
                $noveltyTypesGroups[$NT->getGrupo()]=$NT->getGrupo();
            }
        }

        $form = $this->createFormBuilder()
                ->setAction("/novelty/add/$idPayroll/")
                ->setMethod('POST')
                ->add('noveltyTypeGroup', 'choice', array(
                    'choices' => $noveltyTypesGroups,
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Tipo de novedad',
                    'property_path' => 'noveltyTypeNoveltyType'))
                ->add('noveltyType', 'entity', array(
                    'class' => 'RocketSellerTwoPickBundle:NoveltyType',
                    'choices' =>$noveltyTypeToShow,
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Tipo de novedad',
                    'property_path' => 'noveltyTypeNoveltyType'))
                ->add('save', 'submit', array(
                    'label' => 'Siguiente',
                ))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var NoveltyType $noveltyType */
            $noveltyType = $form->get('noveltyType')->getData();

            return $this->redirectToRoute('novelty_add', array(
                        'idPayroll' => $idPayroll,
                        'noveltyTypeId' => $noveltyType->getIdNoveltyType()), 301);
        }
        $options = $form->get('noveltyType')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();
        return $this->render('RocketSellerTwoPickBundle:Novelty:selectNovelty.html.twig', array(
            'form' => $form->createView(),
            'choices' => $choices));
    }

    /**
     * @param $idPayroll
     * @param $noveltyTypeId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addNoveltyAction($idPayroll, $noveltyTypeId, Request $request) {
        $payRollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        /** @var Payroll $payRol */
        $payRol = $payRollRepo->find($idPayroll);
        if ($payRol == null) {
            return $this->redirectToRoute('ajax', array(), 301);
        }
        $payRollDetail = new PayrollDetail();
        $novelty = new Novelty();
        $novelty->setPayrollDetailPayrollDetail($payRollDetail);
        $noveltyTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        /** @var NoveltyType $noveltyType */
        $noveltyType = $noveltyTypeRepo->find($noveltyTypeId);
        if ($noveltyType == null) {
            return $this->redirectToRoute('novelty_select', array('idPayroll' => $idPayroll), 301);
        }
        $novelty->setNoveltyTypeNoveltyType($noveltyType);
        $requiredDocuments = $noveltyType->getRequiredDocuments();
        $hasDocuments = false;
        $user = $this->getUser();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach ($requiredDocuments as $rd) {
            $hasDocuments = true;
            $tempDoc = new Document();
            if ($rd->getPersonType() == "employer") {
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson());
            } else if ($rd->getPersonType() == "employee") {
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson());
            } else {
                $tempDoc->setContractContract($payRol->getContractContract());
            }
            $tempDoc->setDocumentTypeDocumentType($rd->getDocumentTypeDocumentType());
            $tempDoc->setName(str_replace(" ", "_", $rd->getDocumentTypeDocumentType()->getName()));
            $tempDoc->setStatus(true);
            $novelty->addDocument($tempDoc);
        }
        $requiredFields = $noveltyType->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields, /*$hasDocuments*/ false,$this->generateUrl("novelty_add",array("noveltyTypeId"=>$noveltyType->getIdNoveltyType(),"idPayroll"=>$idPayroll)),$idPayroll), $novelty);// This is because Camilo wanted that its simple to the user to create novelties
        $form->handleRequest($request);
        if ($form->isValid()) {
            //check if novelty date start is valid
            if($novelty->getDateStart()!=null){
                $plus=$payRol->getPeriod()==4?24:14;
                $dateEndPayroll=new DateTime();
                $dateEndPayroll->setDate($payRol->getYear(),$payRol->getMonth(),1+$plus);
                if($novelty->getDateStart()>$dateEndPayroll){
                    return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array(
                        'form' => $form->createView(),
                        'errno'=> 'La fecha no puede ser mayor a la fecha de terminación del periodo de nómina! '.$dateEndPayroll->format("Y-m-d")
                    ));
                }
            }
            //si es una novedad de vacaciones
            if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode()==145){
                $request->setMethod("GET");
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getValidVacationDaysContract',array(
                    "dateStart"=>$novelty->getDateStart()->format("Y-m-d"),
                    "dateEnd"=>$novelty->getDateEnd()->format("Y-m-d"),
                    "contractId"=>$payRol->getContractContract()->getIdContract(),
                    "payrollId"=>"-1",
                    ), array('_format' => 'json'));
                $days=json_decode($insertionAnswer->getContent(),true)["days"];
                $novelty->setUnits($days);
            }
            $novelty->setName($noveltyType->getName());
            $payRol->addNovelty($novelty);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($payRol);
            $em->flush();
            $request->setMethod("GET");
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getAddNoveltySql',array(
                "idNovelty"=>$novelty->getIdNovelty()), array('_format' => 'json'));
            if($insertionAnswer->getStatusCode()!=201){
                $payRol->removeNovelty($novelty);
                $em->remove($novelty);
                $em->persist($payRol);
                $em->flush();
                return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array(
                    'form' => $form->createView(),
                    'errno'=> 'No Se puedo agregar la novedad intente mas tarde!'
                ));
            }
            if ( !$this->checkNoveltyFulfilment($novelty, $form)) {
                /** @var User $user */
                $user = $this->getUser();
                $notification = $notification=$this->createNotification(null,1,null,"","Faltan llenar algunos datos de la novedad " . $novelty->getName(),"Novedad Incompleta","Completar","alert",$user->getPersonPerson());
                $em->persist($notification);
                $em->flush();
                $notification->setRelatedLink($this->generateUrl("novelty_edit", array('noveltyId' => $novelty->getIdNovelty(), 'notificationReferenced' => $notification->getId())));
                $em->persist($notification);
                $em->flush();
            }
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array('form' => $form->createView()));
    }

    public function editNoveltyAction($noveltyId, $notificationReferenced, Request $request) {

        $noveltyRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Novelty');
        /** @var Novelty $novelty */
        $novelty = $noveltyRepo->find($noveltyId);
        if ($novelty == null) {
            return $this->redirectToRoute('ajax', array(), 301);
        }
        if ($novelty->getDocuments()->count() == 0)
            $hasDocuments = false;
        else
            $hasDocuments = true;

        $requiredFields = $novelty->getNoveltyTypeNoveltyType()->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields, $hasDocuments), $novelty);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($novelty);
            $em->flush();
            if (!$this->checkNoveltyFulfilment($novelty, $form)) {
                if ($notificationReferenced != -1) {
                    $user = $this->getUser();
                    $notificationRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification");
                    /** @var Notification $notification */
                    $notification = $notificationRepo->find($notificationReferenced);
                    $notification=$this->createNotification($notification,0,new \DateTime(),null,"Los datos de la novedad se llenaron correctamente",null,null,null,$user->getPersonPerson());
                    $em->persist($notification);
                    $em->flush();
                }
            } else {
                if ($notificationReferenced == -1) {
                    /** @var User $user */
                    $user = $this->getUser();
                    $notification=$this->createNotification(null,1,null,"","Faltan llenar algunos datos de la novedad " . $novelty->getName(),"Novedad Incompleta","Completar","alert",$user->getPersonPerson());
                    $em->persist($notification);
                    $em->flush();
                    $notification->setRelatedLink($this->generateUrl("novelty_edit", array('noveltyId' => $novelty->getIdNovelty(), 'notificationReferenced' => $notification->getId())));
                    $em->persist($notification);
                    $em->flush();
                }
            }
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Notification $notification
     * @param $status
     * @param $sawDate
     * @param $relatedLink
     * @param $description
     * @param $title
     * @param $action
     * @param $type
     * @param Person $person
     * @return Notification
     */
    private function createNotification($notification=null, $status, $sawDate,$relatedLink,$description, $title, $action,$type, $person) {
        $notification= $notification==null?new Notification():$notification;
        $notification->setAccion($action);
        $notification->setRelatedLink($relatedLink);
        $notification->setSawDate($sawDate);
        $notification->setStatus($status);
        $notification->setDescription($description);
        $notification->setTitle($title);
        $notification->setType($type);
        $notification->setPersonPerson($person);
        return $notification;
    }

    /**
     *
     * @param Novelty $novelty
     * @param Form $form
     * @return bool
     */
    private function checkNoveltyFulfilment($novelty, $form) {
        //check each document
        $noveltyType = $novelty->getNoveltyTypeNoveltyType();
        $documents = $novelty->getDocuments();
        $requiredDocuments = $noveltyType->getRequiredDocuments();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach ($requiredDocuments as $rd) {
            /** @var Document $document */
            foreach ($documents as $document) {
                if ($rd->getDocumentTypeDocumentType() == $document->getDocumentTypeDocumentType()) {
                    if ($document->getMediaMedia() == null) {
                        return false;
                    }
                }
            }
        }
        //check each field
        $requiredFields = $noveltyType->getRequiredFields();
        /** @var NoveltyTypeFields $field */
        foreach ($requiredFields as $field) {
            if ($form->get($field->getColumnName())->getData() == null)
                return false;
        }
        return true;
    }

}
