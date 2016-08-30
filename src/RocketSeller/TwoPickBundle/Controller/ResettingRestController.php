<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserInterface;

class ResettingRestController extends FOSRestController
{
    /**
     * .<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener las liquidaciones de un empleado",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     * (name="username", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="Username o email")
     *
     * @return View
     */
    public function postResettingSendEmailAction(Request $request)
    {
        $view = View::create();

        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            $data = array(
                "msj" => "Email incorrecto"
            );

            $view->setData($data)->setStatusCode(400);

            return $view;
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            $data = array(
                "msj" => "El password ya fue solicitado",
                "ttl" => $this->container->getParameter('fos_user.resetting.token_ttl')
            );

            $view->setData($data)->setStatusCode(401);

            return $view;
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }
        $tmp = $this->get('fos_user.mailer')->sendEmailByTypeMessage(array('emailType'=>'resetting','user'=>$user));
        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);

        $data = array(
            "msj" => "Fue enviado un email al email " . $username . " para restablecer la contraseña",
            "tmp" => $tmp,
            "user" => $user->getEmail(),
            "ttl" => $this->container->getParameter('fos_user.resetting.token_ttl')
        );

        $view->setData($data)->setStatusCode(200);

        return $view;
    }
}
