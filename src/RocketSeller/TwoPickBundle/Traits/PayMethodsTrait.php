<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait PayMethodsTrait
{
    protected function payDetail($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Pay');
        $pay = $repository->find($id);

        return $pay;
    }
}