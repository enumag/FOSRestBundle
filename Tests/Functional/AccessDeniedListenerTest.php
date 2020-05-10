<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;

class AccessDeniedListenerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'AccessDeniedListener']);
    }

    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('AccessDeniedListener');
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            $this->markTestSkipped();
        }
    }

    public function testNoCredentialsGives400()
    {
        static::$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testWrongLoginGives401()
    {
        $credentials = '{
            "_username": "restapi",
            "_password": ""
        }';

        static::$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], $credentials);
        $response = static::$client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testSuccessfulLogin()
    {
        $credentials = '{
            "_username": "restapi",
            "_password": "secretpw"
        }';

        static::$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], $credentials);
        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testAccessDeniedExceptionGives403()
    {
        static::$client->request('GET', '/api/comments', [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
