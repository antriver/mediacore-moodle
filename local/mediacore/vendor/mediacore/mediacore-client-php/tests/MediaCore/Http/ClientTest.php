<?php
namespace MediaCore\Http;

use MediaCore\Auth\Lti;

/**
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $client;

    /**
     */
    protected function setUp()
    {
        $this->url = 'http://training.mediacore.tv';
        $this->client = new Client($this->url);
    }

    /**
     */
    protected function tearDown()
    {
        $this->url = null;
        $this->client = null;
    }

    public function testGetUrl()
    {
        $url = $this->client->getUrl('api2', 'media');
        $expectedValue = 'http://training.mediacore.tv/api2/media';
        $this->assertEquals($expectedValue, $url);
    }

    public function testSetAndGetAuth()
    {
        $auth = new Lti('key', 'secret');
        $this->client->setAuth($auth);
        $this->assertInstanceOf('Requests_Auth', $this->client->getAuth());
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidSetAuth()
    {
        $auth = new \stdClass();
        $this->client->setAuth($auth);
    }

    /**
     * @covers MediaCore\Http\Client::get
     */
    public function testGet()
    {
        $url = $this->client->getUrl('api2', 'media', '2751068');
        $response = $this->client->get($url);
        $contentType = $response->getHeader('content-type');
        $this->assertEquals('application/json; charset=utf-8', $contentType);
        $this->assertObjectHasAttribute('id', $response->json);
        $this->assertEquals('2751068', $response->json->id);
    }

    /**
     */
    public function testLtiAuthRequest()
    {
        $url = 'http://127.0.0.1:8080';
        $key = 'key';
        $secret = 'secret';
        $ltiParams = array(
            'context_id' => '0001',
            'context_label' => 'Context Label',
            'context_title' => 'Context Title',
            'ext_lms' => 'moodle-2',
            'lis_person_name_family' => 'Family',
            'lis_person_name_full' => 'Given Family',
            'lis_person_name_given' => 'Given',
            'lis_person_contact_email_primary' => 'test@email.com',
            'lti_message_type' => 'basic-lti-launch-request',
            'roles' => 'Instructor',
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => '1.0',
            'user_id' => 101,
        );

        $auth = new Lti($key, $secret);
        $client = new Client($url, $auth);
        $postUrl = $client->getUrl('api2', 'lti', 'authtkt');

        $count = 20;
        $successes = array();
        for ($i=0; $i<$count; $i++) {
            $response = $client->post($postUrl, $ltiParams);
            if ($response->statusCode == 200) {
                array_push($successes, 1);
            }
        }
        $this->assertCount($count, $successes);
    }
}
