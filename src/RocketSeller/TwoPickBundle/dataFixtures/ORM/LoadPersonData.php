<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Person;

class LoadPersonData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $personAdmin = new Person();
        $personAdmin->setNames('Nicolas');
        $personAdmin->setLastName1('Gaviria');        

        $manager->persist($personAdmin);

        $personBackOffice = new Person();
        $personBackOffice->setNames('Back');
        $personBackOffice->setLastName1('Office');        

        $manager->persist($personBackOffice);
        
        $manager->flush();

        $this->addReference('admin-person', $personAdmin);
        $this->addReference('back-office-person', $personBackOffice);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}
    