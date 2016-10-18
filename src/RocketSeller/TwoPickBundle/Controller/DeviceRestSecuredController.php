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
     *     200 = "Device added successfully"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="deviceToken", nullable=false, strict=true, description="token of device")
     * @RequestParam(name="platform", nullable=false, strict=true, description="platform of device (ios | android)")
     *
     * @return View
     */
    public function postAddDeviceUserAction(ParamFetcher $paramFetcher)
    {
        /** @var User $logedUser */
        $em = $this->getDoctrine()->getManager();

        $deviceToken = $paramFetcher->get('deviceToken');
        $platform = $paramFetcher->get('platform');
        $logedUser = $this->getUser();
        $idUser = $logedUser->getId();

        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->find($idUser);
        
        $deviceSearch = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Device')
            ->findOneBy(array("userUser" => $user, "token" => $deviceToken));
        
        if($deviceSearch) {
            $deviceSearch->setLastLoginInDevice(new DateTime());
            $em->persist($deviceSearch);
            $em->flush();
            return $view->setData(array());
        }

        $device = new Device();
        $device->setToken($deviceToken);
        $device->setPlatform($platform);
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
     *     400 = "Token not found"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="deviceToken", nullable=false, strict=true, description="token of device")
     *
     * @return View
     */
    public function postRemoveDeviceUserAction(ParamFetcher $paramFetcher)
    {
        /** @var User $logedUser */

        $em = $this->getDoctrine()->getManager();


        $deviceToken = $paramFetcher->get('deviceToken');
        $logedUser = $this->getUser();
        $idUser = $logedUser->getId();

        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->find($idUser);

        $devices = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Device')
            ->findBy(array("token" => $deviceToken));

        if(!$devices) {
            $view = View::create();
            $view->setStatusCode(400);
            return $view->setData(array());
        }
        foreach ($devices as $device) {
          $em->remove($device);
          $em->flush();
        }

        $view = View::create();
        $view->setStatusCode(200);

        return $view->setData(array());
    }
}
