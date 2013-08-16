<?php
/**
 * This file is part of the NotifierSwiftMailer package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Notifier\Handler;

use Notifier\Message\Message;
use Notifier\Notifier;
use Notifier\Recipient\Recipient;
use Prowl\Response;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Notifier
     */
    protected $notifier;

    protected $apiKey;

    public function setUp()
    {
        if (!getenv('PROWLAPP_APIKEY')) {
            $this->markTestSkipped('No prowlapp api key found.');
        }

        $this->notifier = new Notifier();
        $this->apiKey = getenv('PROWLAPP_APIKEY');
    }

    public function tearDown()
    {
        unset($this->notifier);
    }

    public function testHandler()
    {
        $handler = new ProwlAppHandler();
        $this->assertInstanceOf('Notifier\Handler\ProwlAppHandler', $handler);
        $this->assertInstanceOf('Notifier\Handler\HandlerInterface', $handler);
    }

    public function testRecipientFilterSuccess()
    {
		$stub = $this->getMock('Notifier\Handler\ProwlAppHandler', array('push'));
		$stub->expects($this->once())
			->method('push')
			->will($this->returnValue(Response::fromResponseXml('')));

		$this->notifier->pushHandler($stub);

		$recipient = new Recipient('Me');
        $recipient->setInfo('prowl_app.api_key', $this->apiKey);
		$recipient->addType('test', 'prowl_app');

		$message = new Message('test');
		$message->setContent('content');
		$message->addRecipient($recipient);

		$this->notifier->sendMessage($message);
    }
}
