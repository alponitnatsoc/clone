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
        $domestico = new Position();
        $domestico->setName('Empleada(o) domestico');
        $domestico->setPayrollCoverageCode('1'); // Code 0.522.
        $domestico->setIdentBy("ed");
        $manager->persist($domestico);

//         $PositionJardinero = new Position();
//         $PositionJardinero->setName('Jardinero(a)');
//         $PositionJardinero->setPayrollCoverageCode('3'); //  Code 2.436
//         $manager->persist($PositionJardinero);

//         $PositionVarios = new Position();
//         $PositionVarios->setName('Oficios Varios');
//         $PositionVarios->setPayrollCoverageCode('1'); // Code 0.522
//         $manager->persist($PositionVarios);

        $conductor = new Position();
        $conductor->setName('Conductor(a)');
        $conductor->setPayrollCoverageCode('4'); // Code 4.35
        $conductor->setIdentBy("c");
        $manager->persist($conductor);

        $ninera = new Position();
        $ninera->setName('Niñero(a)');
        $ninera->setPayrollCoverageCode('1'); // Code 0.522
        $ninera->setIdentBy("n");
        $manager->persist($ninera);

        $enfermera = new Position();
        $enfermera->setName('Enfermero(a)');
        $enfermera->setPayrollCoverageCode('1'); // Code 0.522
        $enfermera->setIdentBy("e");
        $manager->persist($enfermera);

        $mayordomo = new Position();
        $mayordomo->setName('Mayordomo');
        $mayordomo->setPayrollCoverageCode('3'); // Code 2.436
        $mayordomo->setIdentBy("m");
        $manager->persist($mayordomo);

//         $amaLlaves = new Position();
//         $amaLlaves->setName('Ama de Llaves ');
//         $amaLlaves->setPayrollCoverageCode('1'); // Code 0.522
//         $manager->persist($amaLlaves);

        $manager->flush();

        $this->addReference('position-domestico', $domestico);
//         $this->addReference('position-jardinero', $PositionJardinero);
//         $this->addReference('position-varios', $PositionVarios);
        $this->addReference('position-otros', $conductor);
        $this->addReference('position-ninera', $ninera);
        $this->addReference('position-enfermera', $enfermera);
        $this->addReference('position-mayordomo', $mayordomo);
//         $this->addReference('position-amaLlaves', $amaLlaves);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
