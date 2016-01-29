<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait EmployeeMethodsTrait
{
    protected function getEmployee($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        $employee = $repository->find($id);

        return $employee;
    }
}