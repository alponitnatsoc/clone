<?php
namespace RocketSeller\TwoPickBundle\Traits;

use \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;

trait EmployerHasEmployeeMethodsTrait
{
    protected function showContracts($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $contracts = $employerHasEmployee->getContracts();

        return $contracts;
    }

    protected function showLiquidations($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $repository->find($id);
        $liquidations = $employerHasEmployee->getLiquidations();

        return $liquidations;
    }
}