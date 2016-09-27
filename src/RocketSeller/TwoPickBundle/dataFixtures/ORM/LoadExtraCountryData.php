<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Country;
use RocketSeller\TwoPickBundle\Entity\City;
use RocketSeller\TwoPickBundle\Entity\Department;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadExtraCountryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
    	  //Change the paths to the respective files...
	      //Keep in mind that based on how the CSV is generated, the separator can be , or ;
	      //Double check the structure of the file in order to extract the proper values, in general terms is very flexible this fixture
	    
        $pathCountry = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/paises_usa.csv";

        if (file_exists($pathCountry)) {
            $data1 = file($pathCountry);
            foreach ($data1 as $key1 => $country) {

                if ($key1 != 0) {
                    $datos1 = explode("," , $country);

                    $continente = trim($datos1[0]);
                    $countryCode = trim($datos1[1]);
                    $countryName = ucwords(strtolower(trim($datos1[2])));

                    $countryEntity = new Country();
                    $countryEntity->setName($countryName);
                    $countryEntity->setCountryCode($countryCode);
                    $countryEntity->setContinente($continente);

                    $manager->persist($countryEntity);
                }
            }
        }
	
	      $manager->flush();
	
	      //Change the paths to the respective files...
		    $pathDepartment = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/usa_departamentos.csv";
	      $deptArray = array();
	    
		    if (file_exists($pathDepartment)) {
			    $data2 = file($pathDepartment);
			    foreach ($data2 as $key2 => $depto) {
				
				    if ($key2 != 0) {
					    $datos2 = explode("," , $depto);
					
					    $deptoCode = trim($datos2[0]);
					    $deptoName = trim($datos2[1]);
					
					    $departament = new Department();
					    $departament->setCountryCountry($countryEntity);
					    $departament->setDepartmentCode($deptoCode);
					    $departament->setName($deptoName);
					    $manager->persist($departament);
					    array_push($deptArray,$departament);
				    }
			    }
		    }
		    
		    $manager->flush();
	    
		    //To each department, we need to add the cities that belongs to it
		    $pathCity = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/usa_ciudades.csv";
		
		    if (file_exists($pathCity)) {
			    $data3 = file($pathCity);
			    foreach ($data3 as $key3 => $city) {
				
				    if ($key3 != 0) {
					    $datos3 = explode(";" , $city);
					    
					    foreach ($deptArray as $sDept){
					    	if($sDept->getName() == $datos3[0]){
					    		$departament = $sDept;
							    break;
						    }
					    }
					    
					    $cityCode = trim($datos3[1]);
					    $cityName = ucwords(mb_strtolower(trim($datos3[3]), "UTF8"));
					
					    $cityEntity = new City();
					    $cityEntity->setCityCode($cityCode);
					    $cityEntity->setDepartmentDepartment($departament);
					    $cityEntity->setName($cityName);
					
					    $manager->persist($cityEntity);
				    }
				
				    if($key3 % 100 == 0){
					    $manager->flush();
				    }
			    }
			    $manager->flush();
		    }
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}