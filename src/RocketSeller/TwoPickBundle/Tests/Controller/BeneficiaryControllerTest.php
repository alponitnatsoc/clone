<?php

namespace RocketSeller\TwoPickBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BeneficiaryControllerTest extends WebTestCase
{
    public function testAddbeneficiary()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/beneficiary/add');
    }

}
