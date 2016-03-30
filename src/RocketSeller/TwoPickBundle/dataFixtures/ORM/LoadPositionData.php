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
        $domestico->setObligations("1. mantener en perfecto estado de limpieza la casa de habitación de EL EMPLEADOR; 2.  lavar la ropa y elementos que se le indiquen; 3. planchar; cocinar; atender a las personas que habitan en la casa y a las personas que les visiten; 4. efectuar las compras que se le indiquen y en general cumplir con todas las órdenes e instrucciones que le imparta.");
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
        $conductor->setObligations("1. preparar y alistar el vehiculo asignado; 2.  mantener vigentes todos los documentos necesarios para realizar la actividad dentro lo cuales se encuentran (pase, soat, tecnomecanica); 3.utilizar el vehiculo solo para realizar las actividades para las cual fue contratado exclucivamente; 4. cumplir con la normatividad que regula la conduccion en colombia y respetar las señales de transito.");
        $manager->persist($conductor);

        $ninera = new Position();
        $ninera->setName('Niñero(a)');
        $ninera->setPayrollCoverageCode('1'); // Code 0.522
        $ninera->setIdentBy("n");
        $ninera->setObligations("1. Preparar las comidas de los niños y darles de comer; 2.  mantener las habiataciones limpias y arregladas; 3. Realizar activades fuera del hogar con el(los) niños; 4. vigilar y cuidar a los niños; 5. comprar los implementos que necesite el(los) niños.");
        $manager->persist($ninera);

        $enfermera = new Position();
        $enfermera->setName('Enfermero(a)');
        $enfermera->setPayrollCoverageCode('1'); // Code 0.522
        $enfermera->setIdentBy("e");
        $enfermera->setObligations("1. Mantener la higiene personal del paciente y los objetos de uso inherentes a cargo; 2. proporcionar los cuidados básicos y avanzados del paciente; 3. Realizar activadas fuera del hogar con el paciente; 4. vigilar y cuidar al paciente; 5. comprar los implementos que necesite el paciente.");
        $manager->persist($enfermera);

        $mayordomo = new Position();
        $mayordomo->setName('Mayordomo');
        $mayordomo->setPayrollCoverageCode('3'); // Code 2.436
        $mayordomo->setObligations("1. Cuidado y mantenimiento general del bien e inmueble tanto de forma interna como externa ; 2. pago de servicios y demas facturas; 3. Atender a los visitantes del empleador ; 4. Notificar inmendiatamente al empleador, de cualquier eventualidad que se presente en el inmueble.");
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
