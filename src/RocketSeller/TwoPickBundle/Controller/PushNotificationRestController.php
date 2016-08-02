<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Device;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

class PushNotificationRestController extends FOSRestController
{
    /**
     * send push notification to devices of a user
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "send push notification to devices of a user",
     *   statusCodes = {
     *     200 = "Notification sent successfully"
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="idUser", nullable=false, strict=true, description="id user")
     * @RequestParam(name="title", nullable=false, strict=true, description="notification title")
     * @RequestParam(name="message", nullable=false, strict=true, description="notification message")
     *
     * @return View
     */
    public function postPushNotificationAction(ParamFetcher $paramFetcher)
    {
        $ionicIoUrl = "https://api.ionic.io/push/notifications";
        $idUser = $paramFetcher->get('idUser');

        $user = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:User')
            ->find($idUser);

        $devices = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Device')
            ->findBy(array("userUser" => $user));

        if(!$devices) {
            $view = View::create();
            $view->setStatusCode(200);
            return $view->setData(array("status" => "no devices"));
        }

        $deviceTokens = array();

        /** @var Device $device */
        foreach($devices as $device) {
            $deviceTokens[] = $device->getToken();
        }

        $post_body = json_encode(array(
            'tokens' => $deviceTokens,
            "profile" => "tester",
            "notification" => array(
                "title" => "Hi",
                "message" => "Hello world!"
            ),
            "android" => array(
                "title" => "androidTitle"
            ),
            "ios" => array(
                "title" => "iosTitle"
            )
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $ionicIoUrl);
        $apiToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJiNTg0NmViNi1jZTJhLTQ5M2YtOTZkMi00Zjg0Yzg4NTZiMDgifQ.w5TO7G1_xRWKlc4sLe8SD8Ptq_ZGEGoQfAk-eQNAqUY";
        $headers = array("Content-type: application/json",
                         "Authorization: Bearer $apiToken" );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST,           1);
        curl_setopt($curl, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS,     $post_body);

        $result = curl_exec($curl);

        curl_close($curl);

        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData(array());
    }
}