<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Position;

class LoadPositionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PositionDomestica = new Position();
        $PositionDomestica->setName('Empleada(o) domestico');
        $PositionDomestica->setPayrollCoverageCode('1'); // Code 0.522.
        $manager->persist($PositionDomestica);

        $PositionJardinero = new Position();
        $PositionJardinero->setName('Jardinero(a)');
        $PositionJardinero->setPayrollCoverageCode('3'); //  Code 2.436
        $manager->persist($PositionJardinero);

        $PositionVarios = new Position();
        $PositionVarios->setName('Oficios Varios');
        $PositionVarios->setPayrollCoverageCode('2'); // Code 1.044
        $manager->persist($PositionVarios);

        $PositionOtros = new Position();
        $PositionOtros->setName('Conductor');
        $PositionOtros->setPayrollCoverageCode('4'); // Code 4.35
        $manager->persist($PositionOtros);

        $ninera = new Position();
        $ninera->setName('Niñera');
        $ninera->setPayrollCoverageCode(''); // Code 4.35
        $manager->persist($ninera);

        $enfermera = new Position();
        $enfermera->setName('Enfermera');
        $enfermera->setPayrollCoverageCode(''); // Code 4.35
        $manager->persist($enfermera);

        $mayordomo = new Position();
        $mayordomo->setName('Mayordomo');
        $mayordomo->setPayrollCoverageCode(''); // Code 4.35
        $manager->persist($mayordomo);

        $amaLlaves = new Position();
        $amaLlaves->setName('Ama de Llaves ');
        $amaLlaves->setPayrollCoverageCode(''); // Code 4.35
        $manager->persist($amaLlaves);

        $manager->flush();

        $this->addReference('position-domestico', $PositionDomestica);
        $this->addReference('position-jardinero', $PositionJardinero);
        $this->addReference('position-varios', $PositionVarios);
        $this->addReference('position-otros', $PositionOtros);
        $this->addReference('position-ninera', $ninera);
        $this->addReference('position-enfermera', $enfermera);
        $this->addReference('position-mayordomo', $mayordomo);
        $this->addReference('position-amaLlaves', $amaLlaves);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
