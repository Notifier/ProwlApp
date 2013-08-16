<?php
/**
 * This file is part of the NotifierMail package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Notifier\Handler;

use Notifier\Formatter\ProwlAppFormatter;
use Notifier\Handler\AbstractHandler;
use Notifier\Notifier;
use Notifier\Recipient\RecipientInterface;
use Notifier\Message\MessageInterface;
use Prowl\Connector;
use Prowl\Message;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class ProwlAppHandler extends AbstractHandler
{
    /**
     * {@inheritDoc}
     */
    protected $deliveryType = 'prowl_app';

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var Connector
     */
    protected $prowl;

    public function __construct($types = Notifier::TYPE_ALL, $appName = 'Notifier', $bubble = true)
    {
        $this->appName = $appName;
        parent::__construct($types, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function sendOne(MessageInterface $message, RecipientInterface $recipient)
    {
        try {
            $message = $this->buildMessage($message, $recipient);
            $response = $this->push($message);

            if ($response->isError()) {
                $this->errors[] = $response->getErrorAsString();
            }
        } catch (\InvalidArgumentException $oIAE) {
            $this->errors[] = $oIAE->getMessage();
        } catch (\OutOfRangeException $oOORE) {
            $this->errors[] = $oOORE->getMessage();
        }
    }

    /**
     * Get the prowl app connector instance.
     *
     * @return Connector
     */
    protected function getProwl()
    {
        if (!$this->prowl) {
            $this->prowl = new Connector();

            $this->prowl->setIsPostRequest(true);
            $this->prowl->setFilterCallback(
                function ($sText) {
                    return $sText;
                }
            );
        }

        return $this->prowl;
    }

    /**
     * Build a prowl message.
     *
     * @param  MessageInterface   $message
     * @param  RecipientInterface $recipient
     * @return Message
     */
    protected function buildMessage(MessageInterface $message, RecipientInterface $recipient)
    {
        $formatted = $message->getFormatted('prowl_app');

        $prowlMessage = new Message();
        $prowlMessage->addApiKey($recipient->getInfo('prowl_app.api_key'));
        $prowlMessage->setApplication($this->appName);

        $prowlMessage->setEvent($formatted['subject']);
        $prowlMessage->setDescription($formatted['content']);
        $prowlMessage->setPriority($formatted['priority']);

        return $prowlMessage;
    }

    /**
     * Send the message.
     *
     * @param  Message         $message
     * @return \Prowl\Response
     */
    protected function push(Message $message)
    {
        return $this->getProwl()->push($message);
    }

    /**
     * Gets the formatter.
     *
     * @return ProwlAppFormatter
     */
    public function getDefaultFormatter()
    {
        return new ProwlAppFormatter();
    }

    /**
     * Get the formatter. This will use the default as a fallback.
     *
     * @return ProwlAppFormatter
     */
    public function getFormatter()
    {
        return parent::getFormatter();
    }
}
