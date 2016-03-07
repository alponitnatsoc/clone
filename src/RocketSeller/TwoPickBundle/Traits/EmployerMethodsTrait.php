<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait EmployerMethodsTrait
{
    protected function getEmployerDetails($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer');
        $employee = $repository->find($id);

        return $employee;
    }

    protected function getEmployerArl($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "ARP") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function getEmployerCcf($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employer');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getName() == "CC Familiar") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }
}