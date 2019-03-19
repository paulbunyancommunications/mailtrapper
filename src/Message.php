<?php

namespace CrazyInventor;

/**
 * Class Message
 * @package CrazyInventor
 */
class Message extends Mailtrapper
{
	/**
	 * Get a message body from the API by type
	 * @param string $inboxId
	 * @param string $messageId
	 * @param string $type
	 * @return string
	 */
	public function getMessageBody($inboxId, $messageId, $type='html')
	{
		$path = 'inboxes/' . $inboxId . '/messages/'. $messageId .'/body.' . $type;
		$url = $this->buildUrl($path);
		return $this->process($url);
	}
}
