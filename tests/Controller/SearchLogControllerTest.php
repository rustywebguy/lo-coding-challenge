<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Entity\ServiceLog;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchLogControllerTest extends WebTestCase
{
    public function testItCanCountAll(): void
    {
        $client = static::createClient();
        $client->request('GET', '/count');

        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();

        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertEquals(0, $jsonResponse['counter']);
    }

    public function testItCanValidateRequests()
    {
        $client = static::createClient();

        $client->request('GET', '/count?serviceName=USER-SERVICE');
        $this->assertResponseStatusCodeSame(200);

        $client->request('GET', '/count?startDate=123123');
        $this->assertResponseStatusCodeSame(400);

        $client->request('GET', '/count?endDate=123123');
        $this->assertResponseStatusCodeSame(400);

        $client->request('GET', '/count?statusCode=asdasd');
        $this->assertResponseStatusCodeSame(400);
    }
}
