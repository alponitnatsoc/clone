<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\TimeCommitment;

class LoadTimeCommitmentData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $TimeCommitmentCompleto = new TimeCommitment();
        $TimeCommitmentCompleto->setName('Tiempo completo');        
        $manager->persist($TimeCommitmentCompleto);
        
        $TimeCommitmentMedio = new TimeCommitment();
        $TimeCommitmentMedio->setName('Medio tiempo');        
        $manager->persist($TimeCommitmentMedio);

        $timeCommitmentDias = new TimeCommitment();
        $timeCommitmentDias->setName('Trabajo por dias');        
        $manager->persist($timeCommitmentDias);

        $manager->flush();

        $this->addReference('contractType-completo', $TimeCommitmentCompleto);
        $this->addReference('contractType-medio', $TimeCommitmentMedio);
        $this->addReference('contractType-dias', $timeCommitmentDias);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
    