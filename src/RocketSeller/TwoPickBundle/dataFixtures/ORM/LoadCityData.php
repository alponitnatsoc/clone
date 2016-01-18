<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\City;

class LoadCityData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cityMedellin = new City();
        $cityMedellin->setName('Medellin');        
        $cityMedellin->setDepartmentDepartment($this->getReference('department-antioquia'));
        $manager->persist($cityMedellin);
        $manager->flush();

        $this->addReference('city-medellin', $cityMedellin);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
    