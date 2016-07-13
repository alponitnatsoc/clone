<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PlanillaType;

class LoadPlanillaTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $planillaTypeE = new PlanillaType();
        $planillaTypeE->setCode('E');
        $planillaTypeE->setDescription('Tiempo parcial afiliado a Sisben o Dependiente de empresa o persona natural con trabajo comercial');
        $manager->persist($planillaTypeE);

        $planillaTypeS = new PlanillaType();
        $planillaTypeS->setCode('S');
        $planillaTypeS->setDescription('Dependiente de persona natural con trabajo domestico o beneficiarios UPC adicional (fuera nucleofamiliar)');
        $manager->persist($planillaTypeS);

        $manager->flush();

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 7;
    }
}
