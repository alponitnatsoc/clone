<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Invitation;
use RocketSeller\TwoPickBundle\Form\InvitationType;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Mailer\MailerInterface;

/**
 * Invitation controller.
 *
 */
class InvitationController extends Controller
{
    /**
     * Lists all Invitation entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:Invitation')->findBy(array(
            'userId' => $userId
        ));

        return $this->render('RocketSellerTwoPickBundle:Invitation:index.html.twig', array(
            'entities' => $entities,
            'code' => $user->getCode()
        ));
    }
    /**
     * Creates a new Invitation entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Invitation();
        $entity->setUserId($this->getUser());
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $toEmail = $entity->getEmail();

            /** @var \RocketSeller\TwoPickBundle\Mailer\TwigSwiftMailer $smailer */
            $smailer = $this->get('symplifica.mailer.twig_swift');
            $send = $smailer->sendEmail($this->getUser(), "FOSUserBundle:Invitation:email.txt.twig", "from.email@com.co", $toEmail);
            if ($send) {
                $entity->setSent(true);
                $em->persist($entity);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('invitation_show', array('id' => $entity->getId())));
        }

        return $this->render('RocketSellerTwoPickBundle:Invitation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Invitation entity.
     *
     * @param Invitation $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Invitation $entity)
    {
        $form = $this->createForm(new InvitationType(), $entity, array(
            'action' => $this->generateUrl('invitation_create'),
            'method' => 'POST'
        ));

        $form->add('submit', 'submit', array('label' => 'Enviar'));

        return $form;
    }

    /**
     * Displays a form to create a new Invitation entity.
     *
     */
    public function newAction()
    {
        $entity = new Invitation();
        $entity->setUserId($this->getUser());
        $form   = $this->createCreateForm($entity);

        return $this->render('RocketSellerTwoPickBundle:Invitation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Invitation entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:Invitation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invitation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('RocketSellerTwoPickBundle:Invitation:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Invitation entity.
     *
     */
//     public function editAction($id)
//     {
//         $em = $this->getDoctrine()->getManager();

//         $entity = $em->getRepository('RocketSellerTwoPickBundle:Invitation')->find($id);

//         if (!$entity) {
//             throw $this->createNotFoundException('Unable to find Invitation entity.');
//         }

//         $editForm = $this->createEditForm($entity);
//         $deleteForm = $this->createDeleteForm($id);

//         return $this->render('RocketSellerTwoPickBundle:Invitation:edit.html.twig', array(
//             'entity'      => $entity,
//             'edit_form'   => $editForm->createView(),
//             'delete_form' => $deleteForm->createView(),
//         ));
//     }

    /**
    * Creates a form to edit a Invitation entity.
    *
    * @param Invitation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
//     private function createEditForm(Invitation $entity)
//     {
//         $form = $this->createForm(new InvitationType(), $entity, array(
//             'action' => $this->generateUrl('invitation_update', array('id' => $entity->getId())),
//             'method' => 'PUT',
//         ));

//         $form->add('submit', 'submit', array('label' => 'Update'));

//         return $form;
//     }
    /**
     * Edits an existing Invitation entity.
     *
     */
//     public function updateAction(Request $request, $id)
//     {
//         $em = $this->getDoctrine()->getManager();

//         $entity = $em->getRepository('RocketSellerTwoPickBundle:Invitation')->find($id);

//         if (!$entity) {
//             throw $this->createNotFoundException('Unable to find Invitation entity.');
//         }

//         $deleteForm = $this->createDeleteForm($id);
//         $editForm = $this->createEditForm($entity);
//         $editForm->handleRequest($request);

//         if ($editForm->isValid()) {
//             $em->flush();

//             return $this->redirect($this->generateUrl('invitation_edit', array('id' => $id)));
//         }

//         return $this->render('RocketSellerTwoPickBundle:Invitation:edit.html.twig', array(
//             'entity'      => $entity,
//             'edit_form'   => $editForm->createView(),
//             'delete_form' => $deleteForm->createView(),
//         ));
//     }
    /**
     * Deletes a Invitation entity.
     *
     */
//     public function deleteAction(Request $request, $id)
//     {
//         $form = $this->createDeleteForm($id);
//         $form->handleRequest($request);

//         if ($form->isValid()) {
//             $em = $this->getDoctrine()->getManager();
//             $entity = $em->getRepository('RocketSellerTwoPickBundle:Invitation')->find($id);

//             if (!$entity) {
//                 throw $this->createNotFoundException('Unable to find Invitation entity.');
//             }

//             $em->remove($entity);
//             $em->flush();
//         }

//         return $this->redirect($this->generateUrl('invitation'));
//     }

    /**
     * Creates a form to delete a Invitation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('invitation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
