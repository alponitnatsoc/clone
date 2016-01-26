<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RocketSeller\TwoPickBundle\Entity\RoleHasTask;


class LoadRoleHasTaskData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
	    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function load(ObjectManager $manager)
    {
        $RoleTaskExportBack = new RoleHasTask();
		$RoleTaskExportBack->setRoleRole($this->getReference('backoffice-role'));
		$RoleTaskExportBack->setTaskTask($this->getReference('export-docs-task'));
        $manager->flush();

        $this->addReference('role-back-task-export', $RoleTaskExportBack);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
