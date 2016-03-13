<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PayType;

class LoadPayTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PayTypeRetiro = new PayType();
        $PayTypeRetiro->setName('Daviplata');
        $PayTypeRetiro->setDescripcion('Su empleado podrá retirar sin ningún costo desde los cajeros de Davivienda y sin cuotas de manejo.','El empleado recibirá su dinero de forma segura y usted conocerá la trazabilidad de cada transacción. El empleado NO REQUIRE CUENTA BANCARIA así como tampoco asumirá costos por transacción.<br />\nIdeal para empleados que desean tener efectivo a la mano siempre\n');
        $PayTypeRetiro->setImage('/img/icon_servibanca.png');
        $PayTypeRetiro->setPayrollCode('CON');

        $manager->persist($PayTypeRetiro);

        $PayTypeTransferencia = new PayType();
        $PayTypeTransferencia->setName('Transferencia bancaria');
        $PayTypeTransferencia->setDescripcion('El empleado recibirá su dinero directamente a su cuenta bancaria.\nIdeal para empleadores que asignaron cuentas de nómina a sus empleados\n');
        $PayTypeTransferencia->setImage('/img/icon_transfer.png');
        $PayTypeTransferencia->setPayrollCode('CON');

        $manager->persist($PayTypeTransferencia);

        $PayTypeEfectivo = new PayType();
        $PayTypeEfectivo->setName('En efectivo');
        $PayTypeEfectivo->setDescripcion('El pago se realizará de forma directa al empleado, pero no podrá llevar trazabilidad de la operación. Una vez al mes deberá subir a Symplifica comprobante de pago firmado por el empleado');
        $PayTypeEfectivo->setImage('/img/icon_cash.png');
        $PayTypeEfectivo->setPayrollCode('EFE');

        $manager->persist($PayTypeEfectivo);

        $manager->flush();

        $this->addReference('payType-transferencia', $PayTypeTransferencia);
        $this->addReference('payType-efectivo', $PayTypeEfectivo);
        $this->addReference('payType-retiro', $PayTypeRetiro);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
