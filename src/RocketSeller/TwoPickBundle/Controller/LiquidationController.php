<?php

namespace RocketSeller\TwoPickBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Liquidation;

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;

/**
 * Liquidation controller.
 *
 */
class LiquidationController extends Controller
{

    use EmployerHasEmployeeMethodsTrait;

    /**
     * Lists all Liquidation entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:Liquidation')->findAll();

        return $this->render('RocketSellerTwoPickBundle:Liquidation:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Liquidation entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:Liquidation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Liquidation entity.');
        }

        return $this->render('RocketSellerTwoPickBundle:Liquidation:show.html.twig', array(
            'entity'      => $entity
        ));
    }

    /**
     * List Liquidation by relation EmployerHasEmployee
     * @var Integer $id - Id de la relacion EmployerHasEmployee
     */
    public function listByEhEAction($id)
    {
        $entities = $this->showLiquidations($id);
        return $this->render("RocketSellerTwoPickBundle:Liquidation:list.html.twig", array(
            "entities" => $entities,
            "idEhE" => $id
        ));
    }
}
