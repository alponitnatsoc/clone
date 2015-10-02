<?php

namespace AppBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{	
	public function additionProvider()
	{
	return array(
	array(0, 0, 0),
	array(0, 1, 1),
	array(1, 0, 1),
	array(1, 1, 2)
	);
	}
	/**
	* @dataProvider additionProvider
	*/
    public function testIndex($a,$b,$expected)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/calc/'.$a."/".$b);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("'.$expected.'")')->count());
    }
}
