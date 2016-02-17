<?php
namespace RocketSeller\TwoPickBundle\Traits;

trait NoveltyMethodsTrait
{
    protected function noveltiesByGroup($group)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        $noveltyTypes = $repository->findBy(
            array(
                'grupo' => $group
            )
        );
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Novelty');

        $novelties = array();
        /** @var \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyType */
        foreach($noveltyTypes as $noveltyType) {
            $novelty = $repository->findBy(
                array(
                    'noveltyTypeNoveltyType' => $noveltyType->getIdNoveltyType()
                )
            );

            if ($novelty) {
                $novelties[] = $novelty;
            }
        }

        return $novelties;
    }
}