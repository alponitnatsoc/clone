<?php

namespace RocketSeller\TwoPickBundle\Traits;


trait LiquidationReasonMethodsTrait
{

    /**
     * @param integer $id - Id de liquidationReason
     * @return $liquidationReason
     */
    protected function liquidationReasonByPayrollCode($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:LiquidationReason');
        $liquidationReason = $repository->findOneBy(array('payroll_code' => $id));

        return $liquidationReason;
    }
}