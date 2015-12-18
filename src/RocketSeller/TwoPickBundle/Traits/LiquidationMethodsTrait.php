<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait LiquidationMethodsTrait
{
    protected function liquidationDetail($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Liquidation');
        $liquidation = $repository->find($id);

        return $liquidation;
    }
}