<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DocumentRestController extends FOSRestController
{


    /**
     * Obtener pagos de un empleado relacionado con un empleador employerhasemployee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Obtener las liquidaciones de un empleado",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $id - Id de la relacion EmployerHasEmployee
     *
     * @return View
     */
    public function getDocFromAction($id,$idDocumentType)
    {

    	$products = array("hola");
        $view = $this->view($products, 200)
            ->setTemplate("RocketSellerTwoPickBundle:Employee:testDoc.html.twig")
            ->setTemplateVar('products')
            ->setFormat('html');
            //->setTemplateData($templateData)
        

        return $this->handleView($view);

    }

    
    /**
     * Insert a new client into the payments system(3.1 in Novopayment).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Inserts a new client into the payments system.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
     * (name="lastName", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
     * (name="year", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
     * (name="month", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
     * (name="day", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
     * (name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
     * (name="email", nullable=false, strict=true, description="email.")
     *
     * @return View
     */
    public function postDocFromAction(Request $request)
    {    	
    	$products = array("hola");
        $view = $this->view($products, 200)
            ->setTemplate("RocketSellerTwoPickBundle:Employee:testDoc.html.twig")
            ->setTemplateVar('products')
            ->setFormat('html');
            //->setTemplateData($templateData)
        

        return $this->handleView($view);
    }

}
