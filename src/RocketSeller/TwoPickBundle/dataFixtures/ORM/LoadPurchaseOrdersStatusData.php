<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;

class PurchaseOrdersStatusData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PurchaseOrdersStatusPagada = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusPagada->setName('Pagada');        
        
        $manager->persist($PurchaseOrdersStatusPagada);
        
        $PurchaseOrdersStatusPendiente = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusPendiente->setName('Pendiente');               

        $manager->persist($PurchaseOrdersStatusPendiente);

        $PurchaseOrdersStatusCancelada = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusCancelada->setName('Cancelada');               

        $manager->persist($PurchaseOrdersStatusCancelada);

        
        $manager->flush();

        $this->addReference('purchaseOrdersStatus-pagada', $PurchaseOrdersStatusPagada);
        $this->addReference('purchaseOrdersStatus-pendiente', $PurchaseOrdersStatusPendiente);
        $this->addReference('purchaseOrdersStatus-cancelada', $PurchaseOrdersStatusCancelada);
        
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
    