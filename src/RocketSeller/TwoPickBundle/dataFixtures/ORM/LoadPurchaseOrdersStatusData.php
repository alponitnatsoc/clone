<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;

class LoadPurchaseOrdersStatusData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Pendiente');
        $PurchaseOrdersStatus->setDescription('Pendiente');
        $PurchaseOrdersStatus->setIdNovoPay('S1');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Procesando');
        $PurchaseOrdersStatus->setDescription('Procesando');
        $PurchaseOrdersStatus->setIdNovoPay('S2');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Candelada');
        $PurchaseOrdersStatus->setDescription('Candelada');
        $PurchaseOrdersStatus->setIdNovoPay('S3');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Aprobando');
        $PurchaseOrdersStatus->setDescription('Aprobando');
        $PurchaseOrdersStatus->setIdNovoPay('S4');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatusAprobado = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado->setName('Aprobado');
        $PurchaseOrdersStatusAprobado->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado->setIdNovoPay('00');
        $manager->persist($PurchaseOrdersStatusAprobado);

        $PurchaseOrdersStatusAprobado08 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado08->setName('Aprobado');
        $PurchaseOrdersStatusAprobado08->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado08->setIdNovoPay('08');
        $manager->persist($PurchaseOrdersStatusAprobado08);

        $PurchaseOrdersStatusRechazado12 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado12->setName('Rechazado');
        $PurchaseOrdersStatusRechazado12->setDescription('Transaccion Invalida');
        $PurchaseOrdersStatusRechazado12->setIdNovoPay('12');
        $manager->persist($PurchaseOrdersStatusRechazado12);

        $PurchaseOrdersStatusRechazado13 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado13->setName('Rechazado');
        $PurchaseOrdersStatusRechazado13->setDescription('Monto Invalido');
        $PurchaseOrdersStatusRechazado13->setIdNovoPay('13');
        $manager->persist($PurchaseOrdersStatusRechazado13);

        $PurchaseOrdersStatusRechazado14 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado14->setName('Rechazado');
        $PurchaseOrdersStatusRechazado14->setDescription('Tarjeta Invalida');
        $PurchaseOrdersStatusRechazado14->setIdNovoPay('14');
        $manager->persist($PurchaseOrdersStatusRechazado14);

        $PurchaseOrdersStatusRechazado31 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado31->setName('Rechazado');
        $PurchaseOrdersStatusRechazado31->setDescription('Tarjeta No Soportada');
        $PurchaseOrdersStatusRechazado31->setIdNovoPay('31');
        $manager->persist($PurchaseOrdersStatusRechazado31);

        $PurchaseOrdersStatusRechazado51 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado51->setName('Rechazado');
        $PurchaseOrdersStatusRechazado51->setDescription('Fondos Insuficientes');
        $PurchaseOrdersStatusRechazado51->setIdNovoPay('51');
        $manager->persist($PurchaseOrdersStatusRechazado51);

        $PurchaseOrdersStatusRechazado54 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado54->setName('Rechazado');
        $PurchaseOrdersStatusRechazado54->setDescription('Tarjeta Vencida');
        $PurchaseOrdersStatusRechazado54->setIdNovoPay('54');
        $manager->persist($PurchaseOrdersStatusRechazado54);

        $PurchaseOrdersStatusRechazado57 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado57->setName('Rechazado');
        $PurchaseOrdersStatusRechazado57->setDescription('Transacción No Permitida');
        $PurchaseOrdersStatusRechazado57->setIdNovoPay('57');
        $manager->persist($PurchaseOrdersStatusRechazado57);

        $PurchaseOrdersStatusRechazado61 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado61->setName('Rechazado');
        $PurchaseOrdersStatusRechazado61->setDescription('Excede Monto Limite');
        $PurchaseOrdersStatusRechazado61->setIdNovoPay('61');
        $manager->persist($PurchaseOrdersStatusRechazado61);

        $PurchaseOrdersStatusRechazado65 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado65->setName('Rechazado');
        $PurchaseOrdersStatusRechazado65->setDescription('Excede Uso Dia');
        $PurchaseOrdersStatusRechazado65->setIdNovoPay('65');
        $manager->persist($PurchaseOrdersStatusRechazado65);

        $PurchaseOrdersStatusRechazado86 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado86->setName('Rechazado');
        $PurchaseOrdersStatusRechazado86->setDescription('Cuenta Invalida');
        $PurchaseOrdersStatusRechazado86->setIdNovoPay('86');
        $manager->persist($PurchaseOrdersStatusRechazado86);

        $PurchaseOrdersStatusRechazado87 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado87->setName('Rechazado');
        $PurchaseOrdersStatusRechazado87->setDescription('Excede Monto Diario');
        $PurchaseOrdersStatusRechazado87->setIdNovoPay('87');
        $manager->persist($PurchaseOrdersStatusRechazado87);

        $PurchaseOrdersStatusRechazado94 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado94->setName('Rechazado');
        $PurchaseOrdersStatusRechazado94->setDescription('Transacción duplicada');
        $PurchaseOrdersStatusRechazado94->setIdNovoPay('94');
        $manager->persist($PurchaseOrdersStatusRechazado94);

        $PurchaseOrdersStatusRechazado96 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado96->setName('Rechazado');
        $PurchaseOrdersStatusRechazado96->setDescription('Transacción no puede ser procesada');
        $PurchaseOrdersStatusRechazado96->setIdNovoPay('96');
        $manager->persist($PurchaseOrdersStatusRechazado96);

        $PurchaseOrdersStatusRechazadoB6 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB6->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB6->setDescription('Supera el numero maximo de transacciones diarias');
        $PurchaseOrdersStatusRechazadoB6->setIdNovoPay('B6');
        $manager->persist($PurchaseOrdersStatusRechazadoB6);

        $PurchaseOrdersStatusRechazadoB7 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB7->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB7->setDescription('Supera el valor maximo de la transaccion');
        $PurchaseOrdersStatusRechazadoB7->setIdNovoPay('B7');
        $manager->persist($PurchaseOrdersStatusRechazadoB7);

        $PurchaseOrdersStatusDispersion1 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusDispersion1->setName('DispersionAprobada');
        $PurchaseOrdersStatusDispersion1->setDescription('La dispersion fue aprobada');
        $PurchaseOrdersStatusDispersion1->setIdNovoPay('-1');
        $manager->persist($PurchaseOrdersStatusDispersion1);

        $PurchaseOrdersStatusDispersion2 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusDispersion2->setName('DispersionRechazada');
        $PurchaseOrdersStatusDispersion2->setDescription('La dispersion fue rechazada');
        $PurchaseOrdersStatusDispersion2->setIdNovoPay('-2');
        $manager->persist($PurchaseOrdersStatusDispersion2);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }

}
