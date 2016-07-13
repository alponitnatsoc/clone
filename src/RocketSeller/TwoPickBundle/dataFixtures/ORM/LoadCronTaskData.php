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
//         Servicio que cierra nominas automaticamente Corre los días 25 a las 22:00
        $cronTask = new CronTask();
        $cronTask->setName('Cerrar nominas dia 25');
        $cronTask->setLastRun(new \DateTime());
        $cronTask->setInterval(86400); //1 day =  24 hour * 60 min * 60 sec = 86400 seconds
        $cronTask->setCommands(array('symplifica:payroll:close'));
        //$cronTask->setCommands('symplifica:subscription:pay'); //ambos comandos son validos
        $manager->persist($cronTask);

        $manager->flush();

        $cronTask = new CronTask();
        $cronTask->setName('Recordatorio');
        $cronTask->setLastRun(new \DateTime());
        $cronTask->setInterval(86400); //1 day =  24 hour * 60 min * 60 sec = 86400 seconds
        $cronTask->setCommands(array('symplifica:reminder'));
        $manager->persist($cronTask);

        $manager->flush();

        $cronTask = new CronTask();
        $cronTask->setName('Ultimo Recordatorio');
        $cronTask->setLastRun(new \DateTime());
        $cronTask->setInterval(86400); //1 day =  24 hour * 60 min * 60 sec = 86400 seconds
        $cronTask->setCommands(array('symplifica:lastReminder'));
        $manager->persist($cronTask);

        $manager->flush();

		$cronTask = new CronTask();
        $cronTask->setName('Recordatorio Daviplata');
        $cronTask->setLastRun(new \DateTime());
        $cronTask->setInterval(86400); //1 day =  24 hour * 60 min * 60 sec = 86400 seconds
        $cronTask->setCommands(array('symplifica:daviReminder'));
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
