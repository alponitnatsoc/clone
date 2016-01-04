<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\User as User;
use FOS\RestBundle\Controller\Annotations\Route;

class TokenRestController extends FOSRestController
{

    /**
     * Create a Token from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new token from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when the user don't have the proper role"
     *   }
     * )
     * 
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="username", nullable=false, strict=true, description="username.")
     * @RequestParam(name="password", nullable=false, strict=true, description="password.")
     * @RequestParam(name="salt", nullable=false, strict=true, description="salt.")
     *
     * @return View
     */
    public function postTokenAction(ParamFetcher $paramFetcher)
    {

        $view = View::create();

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($paramFetcher->get('username'));

        if (!$user instanceof User) {
            $view->setStatusCode(404)->setData("Data received succesfully but with errors.");

            return $view;
        }
        /*if(!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $view->setStatusCode(403)->setData("You don't have the proper role to get a Token.");

            return $view;
        }*/

        $factory = $this->get('security.encoder_factory');

        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($paramFetcher->get('password'), $paramFetcher->get('salt'));

        $header = $this->generateToken($paramFetcher->get('username'), $password);
        $data = array('X-WSSE' => $header);
        $view->setHeader("Authorization", 'WSSE profile="UsernameToken"');
        $view->setHeader("X-WSSE", $header);
        $view->setStatusCode(200)->setData($data);

        return $view;
    }

    /**
     * Generate token for username given
     *
     * @param  string $username username
     * @param  string $password password with salt included
     * @return string
     */
    private function generateToken($username, $password)
    {
        $created = date('c');
        $nonce = substr(md5(uniqid('nonce_', true)), 0, 16);
        $nonceSixtyFour = base64_encode($nonce);
        $passwordDigest = base64_encode(sha1($nonce . $created . $password, true));

        $token = sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $username,
            $passwordDigest,
            $nonceSixtyFour,
            $created
        );

        return $token;
    }

}
