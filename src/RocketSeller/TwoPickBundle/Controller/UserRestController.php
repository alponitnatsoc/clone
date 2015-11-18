<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\User;
use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Tests\Request\ParamFetcherTest;

class UserRestController extends FOSRestController
{

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getUsersAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUsers();

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $view = View::create();
        $view->setData($entity)->setStatusCode(200);

        return $view;
    }

    /**
     * Return an user identified by username/email.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return an user identified by username/email",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $slug username or email
     *
     * @return View
     */
    public function getUserAction($slug)
    {

        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByUsernameOrEmail($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $view = View::create();
        $view->setData($entity)->setStatusCode(200);

        return $view;
    }

    /**
     * Create a User from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new user from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="username", nullable=false, strict=true, description="Username.")
     * @RequestParam(name="email", nullable=false, strict=true, description="Email.")
     * @RequestParam(name="name", nullable=false, strict=true, description="Name.")
     * @RequestParam(name="lastname", nullable=false, strict=true, description="Lastname.")
     * @RequestParam(name="password", nullable=false, strict=true, description="Plain Password.")
     *
     * @return View
     */
    public function postUserAction(ParamFetcher $paramFetcher)
    {

        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setUsername($paramFetcher->get('username'));
        $user->setEmail($paramFetcher->get('email'));
        $user->setPlainPassword($paramFetcher->get('password'));
        $user->setName($paramFetcher->get('name'));
        $user->setLastname($paramFetcher->get('lastname'));
        $user->setEnabled(true);
        $user->addRole('ROLE_API');

        $view = View::create();

        $errors = $this->get('validator')->validate($user, array('Registration'));

        if (count($errors) == 0) {
            $userManager->updateUser($user);
            $view->setData($user)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Update a User from the submitted data by ID.<br/>
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Updates a user from the submitted data by ID.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="id.")
     * @RequestParam(name="username", nullable=true, strict=true, description="Username.")
     * @RequestParam(name="email", nullable=true, strict=true, description="Email.")
     * @RequestParam(name="name", nullable=true, strict=true, description="Name.")
     * @RequestParam(name="lastname", nullable=true, strict=true, description="Lastname.")
     * @RequestParam(name="password", nullable=true, strict=true, description="Plain Password.")
     * @RequestParam(name="status", nullable=true, strict=true, description="Estado de la suscripcion del usuario")
     *
     * @return View
     */
    public function putUserAction(ParamFetcher $paramFetcher)
    {

        $entity = $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
            array('id' => $paramFetcher->get('id'))
        );

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($entity->getUsername());

        if($paramFetcher->get('username')){ $user->setUsername($paramFetcher->get('username')); }
        if($paramFetcher->get('email')){$user->setEmail($paramFetcher->get('email')); }
        if($paramFetcher->get('password')){$user->setPlainPassword($paramFetcher->get('password')); }
        if($paramFetcher->get('name')){$user->setName($paramFetcher->get('name')); }
        if($paramFetcher->get('lastname')){$user->setLastname($paramFetcher->get('lastname')); }
        if($paramFetcher->get('status')){$user->setStatus($paramFetcher->get('status')); }

        $view = View::create();

        $errors = $this->get('validator')->validate($user, array('Update'));

        if (count($errors) == 0) {
            $userManager->updateUser($user);
            $view->setData($user)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Delete an user identified by username/email.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete an user identified by username/email",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $slug username or email
     *
     * @return View
     */
    public function deleteUserAction($slug)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByUsernameOrEmail($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $userManager->deleteUser($entity);

        $view = View::create();
        $view->setData("User deteled.")->setStatusCode(204);

        return $view;
    }

    /**
     * Get User Salt.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get user salt by its username",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $id the user username
     *
     * @return View
     */
    public function getUserSaltAction($slug)
    {

        $entity = $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
            array('username' => $slug)
        );

        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }

        $salt = $entity->getSalt();

        $view = View::create();
        $view->setData(array('salt' => $salt))->setStatusCode(200);

        return $view;
    }

    /**
     * Obtener las ordenes de compra de un usuario
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id del usuario
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getUserPurchaseorderAction($id)
    {
        $userManager = $this->container->get("fos_user.user_manager");

        $user = $userManager->findUserBy(array('id'=>$id));
        $ordersByUser = $user->getPurchaseOrders();

        $view = View::create();

        if (isset($ordersByUser)) {
            $view->setData($ordersByUser)->setStatusCode(200);
            return $view;
        } else {
            $errors = new ConstraintViolationList();
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Obtener el estado de la suscripcion de un usuario.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id del usuario
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getUserActiveSuscriptionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

        /** @var PurchaseOrders $data */
        $data = $purchaseOrdersRepository->findOneBy(
            array(
                "idUser" => $id,
                "purchaseOrdersTypePurchaseOrdersType" => 2, //Tipo de orden de compra Servicio de Symplifica
                "purchaseOrdersStatusPurchaseOrdersStatus" => 1 //Estatus de orden de compra Pagada
            ),
            array("date_created" => "DESC")
        );

        if ($data) {
            $dateMod = $data->getDateCreated();
            $date = new \DateTime();

            $inicio = date( "Y-m-d ", $dateMod->getTimestamp());
            $fin = date( "Y-m-d ", $date->getTimestamp());

            $start_ts = strtotime($inicio);
            $end_ts = strtotime($fin);
            $diff = $end_ts - $start_ts;
            $dateDiff = round(round($diff / 3600)/24);
        }

        if (isset($dateDiff) && $dateDiff <= 30) {
            $response = array(
                "code" => "1",
                "response" => "Suscripcion Activa"
            );
        } else if (isset($dateDiff) && $dateDiff > 30) {
            $response = array(
                "code" => "0",
                "response" => "Suscripcion Inactiva"
            );
        } else {
            $response = array(
                "code" => "2",
                "response" => "El usuario no tiene suscripcion"
            );
        }

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Actualizar estado de la suscripcion de un usuario
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener todos los datos de una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @RequestParam(name="id", nullable=false, strict=true, description="id del usuario")
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getUserStatusAction(ParamFetcher $paramFetcher)
    {
//         $id = $paramFetcher->get("id");
$id = 1;
        $status = $this->getUserActiveSuscriptionAction($id);

        $data = $status->getData();
        $code = $data["code"];

        $userManager = $this->container->get("fos_user.user_manager");

        /** @var User $user */
        $user = $userManager->findUserBy(array('id'=>$id));
        $statusActual = $user->getStatus();

        if ($code != $statusActual) {
            switch ($code) {
                case 0: //Suscripcion Activa
                    $this->updateUserStatus($id, 1);
                    $resCode = 2;
                    $msgCode = "La suscripción del usuario se ha activado";
                    break;
                case 1: //Sucripicion Inactiva
                case 2: //No tiene suscripcion
                default:
                    $this->updateUserStatus($id, 0);
                    $resCode = 1;
                    $msgCode = "La suscripción del usuario se ha desactivado";
                    break;
            }
        } else {
            $resCode = 0;
            $msgCode = "La suscripción del usuario no ha cambiado de estado";
        }

        $response = array(
            "code" => $resCode,
            "response" => $msgCode
        );

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    protected function updateUserStatus($id, $status)
    {
        $userManager = $this->container->get("fos_user.user_manager");
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $userManager->findUserBy(array('id'=>$id));
        $em->persist($user);
        $em->flush();

        if(isset($status)){
            $user->setStatus($status);
        }

        $view = View::create();

        $errors = $this->get('validator')->validate($user, array('Update'));

        if (count($errors) == 0) {
            $em->persist($user);
            $em->flush();
            $view->setData($user)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }

}
