<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\UserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    use SubscriptionMethodsTrait;
    public function checkLogin(){
        if($this->getUser()==null)
            return null;
        return $this->getUser();
    }

    public function myAccountShowAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $user */
        $user=$this->getUser();
        $person=$user->getPersonPerson();
        $employer=$person->getEmployer();
        //HightTEc Inscription
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////
        /*

        if($this->addToHighTech($user)){
            dump("suscrito o actualizado en HighTech");
        }else{
            dump("no se pudo inscribir o actualizar en HighTech");

        }*/
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////





        $invoicesEmited=new ArrayCollection();
        $purchaseOrders=$user->getPurchaseOrders();
        /** @var PurchaseOrders $purchaseOrder */
        foreach ($purchaseOrders as $purchaseOrder) {
            $id=$purchaseOrder->getPurchaseOrdersStatus()->getIdNovoPay();
            if($id==0||$id==8){//this ids for novo mean aproved
                $purchaseOrdersDetails=$purchaseOrder->getPurchaseOrderDescriptions();
                /** @var PurchaseOrdersDescription  $pOD */
                foreach ($purchaseOrdersDetails as $pOD) {
                    $simpleName=$pOD->getProductProduct()->getSimpleName();
                    if($simpleName!="PN"&&$simpleName!="PP"){//PA pago Pila PN pago nomina
                        $invoicesEmited->add($pOD);
                    }
                }

            }
        }
        //Get pay Methods from Novo
        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user->getId()), array('_format' => 'json'));
        $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);
        //get the remaining days of service
        $dEnd  = new DateTime();
        $dStart = new DateTime();
        $dStart->setDate($dEnd->format("Y"), $dEnd->format("m")+1, $user->getDayToPay());
        $dDiff = $dStart->diff($dEnd);
        //amount to pay and each active employee
        $productRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
        /** @var Product $productSymp1 */
        /** @var Product $productSymp2 */
        /** @var Product $productSymp3 */
        $productSymp1=$productRepo->findOneBy(array("simpleName"=>"PS1"));
        $productSymp2=$productRepo->findOneBy(array("simpleName"=>"PS2"));
        $productSymp3=$productRepo->findOneBy(array("simpleName"=>"PS3"));
        //get the price for one employee
        $eHEToSend=new ArrayCollection();
        $amountToPay=0;
        $employerHasEmployees=$employer->getEmployerHasEmployees();
        $fullTime=0;$atemporel=0;
        /** @var EmployerHasEmployee $eHE */
        foreach ($employerHasEmployees as $eHE) {
            if($eHE->getState()!=0){
                $eHEToSend->add($eHE);
                if($eHE->getState()==2){
                    continue;
                }
                $contracts=$eHE->getContracts();
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    if($contract->getState()==1){
                        $wdm=$contract->getWorkableDaysMonth();
                        $amountToPay+= $wdm<=10 ? $productSymp1->getPrice():$wdm<=19 ? $productSymp2->getPrice():$productSymp3->getPrice();
                        if(!$wdm<=19)
                            $fullTime++;
                        else
                            $atemporel++;
                        break;
                    }
                }
            }
        }
        $form = $this->createFormBuilder()
            ->setAction("/user/show")
            ->setMethod('POST')
            ->add('name', 'hidden')
            ->add('lastName1', 'hidden')
            ->add('lastName2', 'hidden')
            ->add('documentType', 'hidden')
            ->add('document', 'hidden')
            ->add('email', 'text', array(
                'label' => 'Email'))
            ->add('save', 'submit', array(
                'label' => 'Actualizar Datos',
                'attr' => array(
                    'class' => 'btn-symplifica btn'
                )
            ))
            ->add('modify', 'button', array(
                'label' => 'Cambiar datos',
                'attr' => array(
                    'class' => 'btn-symplifica btn'
                )
            ))
            ->getForm();
        $flag=!($user->getFacebookId()!=null||$user->getGoogleId()!=null||$user->getLinkedinId()!=null);


        $form->get("name")->setData($user->getPersonPerson()->getNames());
        $form->get("lastName1")->setData($user->getPersonPerson()->getLastName1());
        $form->get("lastName2")->setData($user->getPersonPerson()->getLastName2());
        $form->get("documentType")->setData($user->getPersonPerson()->getDocumentType());
        $form->get("document")->setData($user->getPersonPerson()->getDocument());
        $form->get("email")->setData($user->getEmail());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $nEmail = $form->get("email")->getData();
            $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
            $doesExist = $userRepo->findOneBy( array("email" => $nEmail) );

            if ($doesExist == null) {
                $user->setEmail($nEmail);
                $user->setUsername($nEmail);
                $user->setEmailCanonical(strtolower($nEmail));
                $user->setUsernameCanonical(strtolower($nEmail));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
        }

        $paymentMethodsType = array();

        if (isset($responsePaymentsMethods["payment-methods"]) && is_array($responsePaymentsMethods["payment-methods"])) {
            foreach ($responsePaymentsMethods["payment-methods"] as $pmt) {
                if ($pmt['bank'] != null) {
                    $paymentMethodsType['bankAccounts'][] = $pmt;
                } else {
                    $paymentMethodsType['creditCards'][] = $pmt;
                }
            }
        }

        return $this->render('RocketSellerTwoPickBundle:User:show.html.twig', array(
            'form' => $form->createView(),
            'flag' => $flag,
            'invoices' => $invoicesEmited,
//             'payMethods' => isset($responsePaymentsMethods["payment-methods"])?$responsePaymentsMethods["payment-methods"]:null,
            'payMethods' => $paymentMethodsType,
            'dayService' => $dDiff->days,
            'eHEToSend' => array('fullTime'=>$fullTime, 'partialTime'=>$atemporel),
            'amountToPay' => $amountToPay,
            'factDate' => $dStart,
            'dateCreated' => $user->getDateCreated()
        ));
    }

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Template("RocketSellerTwoPickBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("RocketSellerTwoPickBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RocketSellerTwoPickBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Metodo para actualizar el estado de la suscripcion de un usuario.
     *
     * @param Request $request
     * @param unknown $id
     *
     * @return status:
     *              0 - No se recibió status nuevo para actualizar
     *              1 - Se actualiza el estado del usuario
     *              2 - No se puede actualizar el estado del usuario
     */
    public function updateUserStatusAction(Request $request, $id)
    {
        $status = $request->request->get("status");
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("RocketSellerTwoPickBundle:User");
        $user = $userRepository->find($id);

        $response = 0;
        if ($status) {
            try {
                $em->persist($user);
                $em->flush();
                $user->setStatus($status);
                $em->persist($user);
                $em->flush();
                $response = 1;
            } catch (\Exception $e) {
                $response = 2;
            }
        }

        $res = array(
            "status" => $response
        );
        return new JsonResponse($res);
    }

    public function getTokenAction()
    {
        return new Response($this->container->get('form.csrf_provider')
                                ->generateCsrfToken('authenticate'));
    }
}
