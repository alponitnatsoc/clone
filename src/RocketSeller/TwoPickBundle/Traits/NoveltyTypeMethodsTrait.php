<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait NoveltyTypeMethodsTrait
{
    protected function noveltyTypeByPayrollCode($payroll_code)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        $noveltyType = $repository->findOneBy(
            array(
                'payroll_code' => $payroll_code
            )
        );

        return $noveltyType;
    }

    protected function noveltyTypeByGroup($group)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        $noveltyType = $repository->findBy(
            array(
                'grupo' => $group
            )
        );

        return $noveltyType;
    }
}