<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Device;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class DeviceRestSecuredController extends FOSRestController {

    /**
     * add device to user
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "add device to user",
     *   statusCodes = {
     *     200 = "Device added successfully",
     *     409 = "Wrong credentials"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="idUser", nullable=false, strict=true, description="id user")
     * @RequestParam(name="deviceToken", nullable=false, strict=true, description="token of device")
     *
     * @return View
     */
    public function postAddDeviceUserAction(ParamFetcher $paramFetcher)
    {
        /** @var User $logedUser */

        $em = $this->getDoctrine()->getManager();

        $idUser = $paramFetcher->get('idUser');
        $deviceToken = $paramFetcher->get('deviceToken');
        $logedUser = $this->getUser();
        if( $logedUser->getId() != $idUser) {
            $view = View::create();
            $view->setStatusCode(409);
            return $view->setData(array());
        }

        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->find($idUser);

        $device = new Device();
        $device->setToken($deviceToken);
        $device->setUserUser($user);
        $device->setLastLoginInDevice(new DateTime());
        $em->persist($device);
        $em->flush();

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array());
    }

    /**
     * add device to user
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "remove specific device of user",
     *   statusCodes = {
     *     200 = "Device Removed successfully",
     *     409 = "Wrong credentials",
     *     400 = "Token not found"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="idUser", nullable=false, strict=true, description="id user")
     * @RequestParam(name="deviceToken", nullable=false, strict=true, description="token of device")
     *
     * @return View
     */
    public function postRemoveDeviceUserAction(ParamFetcher $paramFetcher)
    {
        /** @var User $logedUser */

        $em = $this->getDoctrine()->getManager();

        $idUser = $paramFetcher->get('idUser');
        $deviceToken = $paramFetcher->get('deviceToken');
        $logedUser = $this->getUser();
        if( $logedUser->getId() != $idUser) {
            $view = View::create();
            $view->setStatusCode(409);
            return $view->setData(array());
        }

        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->find($idUser);

        $device = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Device')
            ->findOneBy(array("token" => $deviceToken));

        if(!$device) {
            $view = View::create();
            $view->setStatusCode(400);
            return $view->setData(array());
        }

        $em->remove($device);
        $em->flush();

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array());
    }
}