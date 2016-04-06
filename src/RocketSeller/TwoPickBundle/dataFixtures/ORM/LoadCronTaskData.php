<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\CronTask;

class LoadCronTaskData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $cronTask = new CronTask();
        $cronTask->setName('Pago subscripcion');
        $cronTask->setLastRun(new \DateTime());
        $cronTask->setInterval(86400); //1 day =  24 hour * 60 min * 60 sec = 86400 seconds
        $cronTask->setCommands(array('symplifica:subscription:pay'));
        //$cronTask->setCommands('symplifica:subscription:pay'); //ambos comandos son validos
        $manager->persist($cronTask);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }

}