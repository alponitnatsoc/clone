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
     * Obtener el estado de la suscripcion de un usuario a partir de la ultima orden de compra pagada.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener el estado de la suscripcion de un usuario a partir de la ultima orden de compra pagada.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id del usuario
     *
     * @return \FOS\RestBundle\View\View
     * Codigos de respuesta:
     *      0 - Suscripcion inactiva
     *      1 - Suscripcion activa
     *      2 - No hay suscripcion
     */
    public function getUserActiveSuscriptionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $purchaseOrdersRepository = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");

        /** @var PurchaseOrders $data */
        $data = $purchaseOrdersRepository->findOneBy(
            array(
                "idUser" => $id,
                "purchaseOrdersTypePurchaseOrdersType" => 1, //Tipo de orden de compra Servicio de Symplifica
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
                "code" => 1,
                "response" => "Suscripcion Activa"
            );
        } else if (isset($dateDiff) && $dateDiff > 30) {
            $response = array(
                "code" => 0,
                "response" => "Suscripcion Inactiva"
            );
        } else {
            $response = array(
                "code" => 404,
                "response" => "El usuario no tiene ordenes de pago del servicio"
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
     * Codigos de respuesta:
     *      0 - La suscripcion no cambia de estado
     *      1 - Suscripcion desactivada
     *      2 - Suscripcion activada
     */
    public function putUserStatusAction(ParamFetcher $paramFetcher)
    {
        $id = $paramFetcher->get("id");

        $statusActual = $this->userStatus($id);
        $resCode = $statusActual;
        $msgCode = "umm";
        switch ($statusActual) {
        	case 0: //Suscripcion inactiva
        	case 1: //Suscripcion activa
        	    $status = $this->getUserActiveSuscriptionAction($id);
        	    $data = $status->getData();
        	    $code = $data["code"];

        	    if ($code != $statusActual) {
        	        switch ($code) {
        	            case 1: //Suscripcion debe ser Activa
        	                $this->updateUserStatus($id, 1);
        	                $resCode = 2;
        	                $msgCode = "La suscripcion del usuario se ha activado";
        	                break;
    	                case 0: //Sucripicion debe ser Inactiva
        	            case 404: //No se encuentra pago de suscripcion alguna al servicio
        	            default:
        	                $this->updateUserStatus($id, 0);
    	                    $resCode = 1;
    	                    $msgCode = "La suscripcion del usuario se ha desactivado";
    	                    break;
        	        }
        	    } else {
        	        $resCode = 0;
        	        $msgCode = "La suscripcion del usuario no ha cambiado de estado";
        	    }
        	    break;
            case 2: //Suscripcion gratis por el primer mes
                $userManager = $this->container->get("fos_user.user_manager");
                /** @var User $user */
                $user = $userManager->findUserBy(array('id'=>$id));
                $dateCreated = $user->getDateCreated();
                $nowDate = new \DateTime();

                $dateDif = date_diff($nowDate, $dateCreated);
                $dias = $dateDif->format('%a');

                if ($dias > 31) {
                    $this->updateUserStatus($id, 0);
                    $resCode = 1;
                    $msgCode = "La suscripcion del usuario se ha desactivado " . $dateDif->format("%a dias");
                } else {
                    $resCode = 0;
                    $msgCode = "La suscripcion del usuario no ha cambiado de estado " . $dateDif->format("%a dias");
                }
                break;
            case 3: //Suscripcion gratis 3 primeros meses por completar el registro en 48 horas
                $userManager = $this->container->get("fos_user.user_manager");
                /** @var User $user */
                $user = $userManager->findUserBy(array('id'=>$id));
                $dateCreated = $user->getDateCreated();
                $nowDate = new \DateTime();
                $dateDif = date_diff($nowDate, $dateCreated);
                $days = $dateDif->format('%a');

                if ($days > 90) {
                    $this->updateUserStatus($id, 0);
                    $resCode = 1;
                    $msgCode = "La suscripcion del usuario se ha desactivado " . $dateDif->format("%a dias");
                } else {
                    $resCode = 0;
                    $msgCode = "La suscripcion del usuario no ha cambiado de estado " . $dateDif->format("%a dias");
                }
                break;
            default:
                $this->updateUserStatus($id, 0);
                $resCode = 1;
                $msgCode = "La suscripcion del usuario se ha desactivado";
                break;
        }

        $response = array(
            "code" => $resCode,
            "response" => $msgCode
        );

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * Estado de la suscripicion del usuario
     *
     * @param integer $id - Id del usuario
     * @return $status - Estado actual del usuario
     */
    private function userStatus($id)
    {
        $userManager = $this->container->get("fos_user.user_manager");
        /** @var User $user */
        $user = $userManager->findUserBy(array('id'=>$id));
        $status = $user->getStatus();

        return $status;
    }

    /**
     * Actualizar el estado del usuario
     *
     * @param integer $id - Id del usuario
     * @param integer $status - Estado del usuario a actualizar
     * @return View
     */
    private function updateUserStatus($id, $status)
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
