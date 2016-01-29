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
        $LiquidationReasonRenuncia = new LiquidationReason();
        $LiquidationReasonRenuncia->setName('Renuncia');
        $LiquidationReasonRenuncia->setDescription('El empleado renuncia a su puesto de trabajo');

        $manager->persist($LiquidationReasonRenuncia);

        $LiquidationReasonAcuerdo = new LiquidationReason();
        $LiquidationReasonAcuerdo->setName('Mutuo acuerdo');
        $LiquidationReasonAcuerdo->setDescription('La finalización del contrato se da por mutuo acuerdo');

        $manager->persist($LiquidationReasonAcuerdo);

        $liquidationReasonJustaCausa = new LiquidationReason();
        $liquidationReasonJustaCausa->setName('Despido con justa causa');
        $liquidationReasonJustaCausa->setDescription('El empleado incumplió el reglamento interno de trabajo');

        $manager->persist($liquidationReasonJustaCausa);

        $liquidationReasonSinJustaCausa = new LiquidationReason();
        $liquidationReasonSinJustaCausa->setName('Despido sin justa causa');
        $liquidationReasonSinJustaCausa->setDescription('El empleador finaliza el contrato sin una causa justa');

        $manager->persist($liquidationReasonSinJustaCausa);

        $manager->flush();

        $this->addReference('liquidationReason-renuncia', $LiquidationReasonRenuncia);
        $this->addReference('liquidationReason-acuerdo', $LiquidationReasonAcuerdo);
        $this->addReference('liquidationReason-justa-causa', $liquidationReasonJustaCausa);
        $this->addReference('liquidationReason-sin-justa-causa', $liquidationReasonSinJustaCausa);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}