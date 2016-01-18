<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Department;

class LoadDepartmentData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $departmentAntioquia = new Department();
        $departmentAntioquia->setName('Antioquia');        
        $departmentAntioquia->setCountryCountry($this->getReference('country-colombia'));
        $manager->persist($departmentAntioquia);
        $manager->flush();

        $this->addReference('department-antioquia', $departmentAntioquia);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 4;
    }
}
    