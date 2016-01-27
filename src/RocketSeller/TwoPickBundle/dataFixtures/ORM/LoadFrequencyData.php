<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Frequency;

class LoadFreuencyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $FrequencyDiaria = new Frequency();
        $FrequencyDiaria->setName('Diario');
        $FrequencyDiaria->setPayrollCode('J');

        $manager->persist($FrequencyDiaria);

        $FrequencyMensual = new Frequency();
        $FrequencyMensual->setName('Mensual');
        $FrequencyMensual->setPayrollCode('M');

        $manager->persist($FrequencyMensual);

        $FrequencyQuincenal = new Frequency();
        $FrequencyQuincenal->setName('Quincenal');
        $FrequencyQuincenal->setPayrollCode('Q');

        $manager->persist($FrequencyQuincenal);

        $manager->flush();

        $this->addReference('frequency-diaria', $FrequencyDiaria);
        $this->addReference('frequency-mensual', $FrequencyMensual);
        $this->addReference('frequency-quincenal', $FrequencyQuincenal);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
