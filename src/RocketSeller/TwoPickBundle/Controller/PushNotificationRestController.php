<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Device;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

class PushNotificationRestController extends FOSRestController
{
    public function __construct( $container=null)
    {
        if($container)
            $this->setContainer($container);
    }
    
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
     *
     * @return View
     */
    public function postPushNotificationAction(Request $request)
    {
      // $ionicIoUrl = "https://api.ionic.io/push/notifications";

      //firebase Cloud Messaging api end point
      $firebaseUrl = "https://fcm.googleapis.com/fcm/send";
      $idUser = $request->request->get('idUser');
      $title = $request->request->get('title');
      $message = $request->request->get('message');
      $longMessage = $request->request->get('longMessage');
      $page = $request->request->get('page');
      if(!$page) {
          $page = '';
      }

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

      $iosDeviceTokens = array();
      $androidDeviceTokens = array();

      /** @var Device $device */
      foreach($devices as $device) {
        if($device->getPlatform() == 'android') {
          $androidDeviceTokens[] = $device->getToken();
        } else if($device->getPlatform() == 'ios') {
          $iosDeviceTokens[] = $device->getToken();
        }
      }
      $numDevices = count($devices);
      $resultIos = "";
      $resultAndroid = "";
      if(!empty($androidDeviceTokens)) {
        $post_body = json_encode(array(
            'registration_ids' => $androidDeviceTokens,
            "notification" => array(
                "title" => $title,
                "body" => $message,
                "sound" => "default",
                "click_action" => "FCM_PLUGIN_ACTIVITY"
            ),
            "data" => array(
              "longMessage" => $longMessage,
              "page" => $page
            ),
            "priority" => "high"
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $firebaseUrl);
        $apiToken = "AIzaSyBtWoWbLvG2awxkj3slD4a4lonmTKi0o7E";
        $headers = array("Content-type: application/json",
                         "Authorization: key=$apiToken" );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST,           1);
        curl_setopt($curl, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS,     $post_body);

        $resultAndroid = curl_exec($curl);
        $resultAndroid = json_decode($resultAndroid, true);
        curl_close($curl);
      }

      if(!empty($iosDeviceTokens)) {
        $post_body = json_encode(array(
            'registration_ids' => $iosDeviceTokens,
            "notification" => array(
                "title" => $title,
                "body" => $longMessage,
                "sound" => "default",
            ),
            "data" => array(
              "longMessage" => $longMessage,
              "page" => $page
            ),
            "priority" => "high"
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $firebaseUrl);
        $apiToken = "AIzaSyBtWoWbLvG2awxkj3slD4a4lonmTKi0o7E";
        $headers = array("Content-type: application/json",
                         "Authorization: key=$apiToken" );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST,           1);
        curl_setopt($curl, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS,     $post_body);

        $resultIos = curl_exec($curl);
        $resultIos = json_decode($resultIos, true);
        curl_close($curl);
      }

      $view = View::create();
      $view->setStatusCode(200);
      return $view->setData(array('status' => "$numDevices devices",
                                  'resultIos'=> $resultIos,
                                  'resultAndroid'=> $resultAndroid
                                 ));
    }

    public function sendMessageValidatingDocuments($idUser) {
        $message = "Estamos revisando tus documentos. Espéranos";
        $title = "Symplifica";
        $longMessage = "¡Hola! Estamos revisando tu documentación y pronto iniciaremos las afiliaciones a seguridad social si es necesario.";

        $request = new Request();
        $request->setMethod("POST");
        $request->request->add(array(
            "idUser" => $idUser,
            "title" => $title,
            "message" => $message,
            "longMessage" => $longMessage
        ));

        $result = $this->postPushNotificationAction($request);

        $collect = $result->getData();
        $resultUsers[] = array('userId' => $idUser, 'result' => $collect);
        // echo $resultUsers;
    }
	
	/**
	 * send generic push notification to all users with devices
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "send generic push notification to all users with devices",
	 *   statusCodes = {
	 *     200 = "Notification sent successfully"
	 *   }
	 * )
	 *
	 * @param ParamFetcher $paramFetcher Paramfetcher
	 *
	 * @RequestParam(name="title", nullable=false, strict=true, description="title")
	 * @RequestParam(name="message", nullable=false,  strict=true, description="title")
	 * @RequestParam(name="longMessage", nullable=false,  strict=true, description="title")
	 * @RequestParam(name="page", nullable=true,  strict=true, description="title")
	 * @return View
	 */
	public function postSendGenericMessageToAllUsersWithDeviceAction(ParamFetcher $paramFetcher) {
		
		$title = $paramFetcher->get('title');
		$message = $paramFetcher->get('message');
		$longMessage = $paramFetcher->get('longMessage');
		$page = $paramFetcher->get('page');
		
		$users = $this->getDoctrine()
		  ->getRepository('RocketSellerTwoPickBundle:User')
		  ->findAll();
		$resultUsers = array();
		
		/** @var User $user */
		foreach ($users as $user) {
			$devices = $this->getDoctrine()
			  ->getRepository('RocketSellerTwoPickBundle:Device')
			  ->findBy(array("userUser" => $user));
			
			if(!$devices) {
				continue;
			}
			
			$request = new Request();
			$request->setMethod("POST");
			$request->request->add(array(
			  "idUser" => $user->getId(),
			  "title" => $title,
			  "message" => $message,
			  "longMessage" => $longMessage,
			  "page" => $page
			));
			
			$result = $this->postPushNotificationAction($request);
			
			$collect = $result->getData();
			$resultUsers[] = array('userId' => $user->getId(), 'result' => $collect);
			// echo $resultUsers;
		}
		$view = View::create();
		$view->setStatusCode(200);
		return $view->setData(array('$resultUsers' => $resultUsers));
	}
}
