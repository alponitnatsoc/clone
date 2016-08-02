<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait ContractMethodsTrait
{
    protected function contractDetail($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract');
        $contract = $repository->find($id);

        return $contract;
    }
}