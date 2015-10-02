<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function connect(){
        $link =  mysql_connect('localhost', 'root', '');
        if (!$link) {
            return "could not connect: ".mysql_errno();
        }
        $database= mysql_select_db('test', $link);
        if (!$database) {
            return "could not connect: ".mysql_errno();
        }
        return "Connections succesfull!!!!! :)";
    }
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('home/index.html.twig');
    }
    /**
     * @Route("/connect", name="connect_db")
     */
    public function connectAction(Request $request)
    {
        // replace this example code with whatever you need
        $tryconnect = connect();
        return new Response('<html><body>'.$tryconnect.'</body></html>');
    }
    /**
     * @Route("/login" , name = "user_login")
     */
    public function loginAction(Request $request){
        return $this->render('/home/login.html.twig');
    }

    /**
     * @Route("/Register" , name = "user_register")
     */
    public function registerAction(Request $request){
        return $this->render('home/register.html.twig');
    }
    
}
