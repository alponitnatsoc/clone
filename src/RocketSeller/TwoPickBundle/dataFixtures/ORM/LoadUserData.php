<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RocketSeller\TwoPickBundle\Entity\User;


class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $userAdmin = new User();
        $userAdmin->setUsername('Admin');
        $userAdmin->setEnabled(1);
        $userAdmin->setEmail('adminSymplifica@gmail.com');        
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($userAdmin, 'test');
        $userAdmin->setPassword($password);

        $rolesArr = array('ROLE_SUPER_ADMIN');
        $userAdmin->setRoles($rolesArr);

        $userAdmin->setPersonPerson($this->getReference('admin-person'));
        $manager->persist($userAdmin);
        $manager->flush();

        $this->addReference('admin-user', $userAdmin);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
//(1, 1, 'nicomalacho', 'nicomalacho', 'nico@gmail.com', 'nico@gmail.com', 1, 'lpd2ssl37pwo0kccwo0o0gcossc88so', 'N2avqBDr5zIwaqljT94l3tnflFytTcCfFXWZM8yE4N6W1aS9IxdhqhI9xU70zRRFkGdc9sdl7//RTIRd4Aph5w==', '2016-01-15 14:49:18', 0, 0, NULL, NULL, NULL, 'a:2:{i:0;s:16:"ROLE_SUPER_ADMIN";i:1;s:8:"ROLE_NEW";}', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2016-01-12 14:14:44'),