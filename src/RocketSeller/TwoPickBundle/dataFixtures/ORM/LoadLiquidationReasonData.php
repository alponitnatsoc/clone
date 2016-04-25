<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\LiquidationReason;

class LiquidationReasonData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

//         $cambioRegimen = new LiquidationReason();
//         $cambioRegimen->setName('Cambio de regimen laboral a integral');
//         $cambioRegimen->setDescription('Cambio de regimen laboral a integral');
//         $cambioRegimen->setPayrollCode('1');
//         $manager->persist($cambioRegimen);

        $liquidationReasonJustaCausa = new LiquidationReason();
        $liquidationReasonJustaCausa->setName('Terminación del contrato con justa causa');
        $liquidationReasonJustaCausa->setDescription('El empleado incumplió el reglamento interno de trabajo');
        $liquidationReasonJustaCausa->setPayrollCode('2');
        $manager->persist($liquidationReasonJustaCausa);

        $liquidationReasonSinJustaCausa = new LiquidationReason();
        $liquidationReasonSinJustaCausa->setName('Terminación del contrato sin justa causa');
        $liquidationReasonSinJustaCausa->setDescription('El empleador finaliza el contrato sin una causa justa');
        $liquidationReasonSinJustaCausa->setPayrollCode('3');
        $manager->persist($liquidationReasonSinJustaCausa);

//         $eliminacionCargo = new LiquidationReason();
//         $eliminacionCargo->setName('Eliminación cargo');
//         $eliminacionCargo->setDescription('Se elimina el cargo que ocupa el empleado');
//         $eliminacionCargo->setPayrollCode('4');
//         $manager->persist($eliminacionCargo);

//         $fallecimiento = new LiquidationReason();
//         $fallecimiento->setName('Fallecimiento');
//         $fallecimiento->setDescription('Fallecimiento');
//         $fallecimiento->setPayrollCode('5');
//         $manager->persist($fallecimiento);

//         $pension = new LiquidationReason();
//         $pension->setName('Pension');
//         $pension->setDescription('Pension');
//         $pension->setPayrollCode('6');
//         $manager->persist($pension);

        $LiquidationReasonRenuncia = new LiquidationReason();
        $LiquidationReasonRenuncia->setName('Renuncia');
        $LiquidationReasonRenuncia->setDescription('El empleado renuncia a su puesto de trabajo');
        $LiquidationReasonRenuncia->setPayrollCode('7');
        $manager->persist($LiquidationReasonRenuncia);

//         $vencimientoContrato = new LiquidationReason();
//         $vencimientoContrato->setName('Vencimiento de contrato');
//         $vencimientoContrato->setDescription('Vencimiento de contrato');
//         $vencimientoContrato->setPayrollCode('8');
//         $manager->persist($vencimientoContrato);

        $periodoPrueba = new LiquidationReason();
        $periodoPrueba->setName('Despido en periodo de prueba');
        $periodoPrueba->setDescription('Despido durante el periodo de prueba');
        $periodoPrueba->setPayrollCode('9');
        $manager->persist($periodoPrueba);

        $LiquidationReasonAcuerdo = new LiquidationReason();
        $LiquidationReasonAcuerdo->setName('Mutuo acuerdo');
        $LiquidationReasonAcuerdo->setDescription('La finalización del contrato se da por mutuo acuerdo');
        $LiquidationReasonAcuerdo->setPayrollCode('10');
        $manager->persist($LiquidationReasonAcuerdo);

        $manager->flush();

//         $this->addReference('liquidationReason-cambio-regimen', $cambioRegimen);
        $this->addReference('liquidationReason-justa-causa', $liquidationReasonJustaCausa);
        $this->addReference('liquidationReason-sin-justa-causa', $liquidationReasonSinJustaCausa);
//         $this->addReference('liquidationReason-eliminacion-contrato', $eliminacionCargo);
//         $this->addReference('liquidationReason-fallecimiento', $fallecimiento);
//         $this->addReference('liquidationReason-pension', $pension);
        $this->addReference('liquidationReason-renuncia', $LiquidationReasonRenuncia);
//         $this->addReference('liquidationReason-vencimineto-contrato', $vencimientoContrato);
//         $this->addReference('liquidationReason-periodo-prueba', $periodoPrueba);
        $this->addReference('liquidationReason-acuerdo', $LiquidationReasonAcuerdo);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
