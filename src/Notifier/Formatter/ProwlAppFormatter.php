<?php
/**
 * This file is part of the NotifierMail package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Notifier\Formatter;

use Notifier\Message\MessageInterface;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class ProwlAppFormatter implements FormatterInterface
{
    public function format(MessageInterface $message)
    {
        $message->setFormatted(
            'prowl_app',
            array(
                'subject' => $message->getSubject(),
                'content' => $message->getContent(),
				'priority' => 0,
            )
        );

        return $message;
    }

    public function formatBatch(array $messages)
    {
        foreach ($messages as &$message) {
            $message = $this->format($message);
        }

        return $messages;
    }
}
