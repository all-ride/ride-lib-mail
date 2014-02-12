<?php

namespace pallo\library\mail\transport;

use pallo\library\mail\exception\MailException;
use pallo\library\mail\MailMessage;

/**
 * Simple message transport using PHP's mail function
 */
class SimpleTransport extends AbstractTransport {

    /**
     * Deliver a mail message to the server mail transport using PHP's mail
     * @param pallo\library\mail\MailMessage $message The message to send
     * @return null
     * @throws pallo\library\mail\exception\MailException when the message is not
     * accepted for delivery. Check the installation of the mail tools on the
     * server.
     */
    public function send(MailMessage $message) {
        $parser = new MessageParser($message, $this->defaultFrom, $this->debugTo);

        $subject = $parser->getSubject();
        $body = $parser->getBody();
        $headers = $parser->getHeaders();

        $headersString = $this->implodeHeaders($headers);

        $additionalParameters = null;

        $returnPath = $message->getReturnPath();
        if ($returnPath) {
            $additionalParameters = '-f ' . $returnPath->getEmailAddress();
        }

        $isSuccess = mail(null, $subject, $body, $headersString, $additionalParameters);

        $this->logMail($subject, $headersString, !$isSuccess);

        if (!$isSuccess) {
            throw new MailException('The message is not accepted for delivery. Check your mail configuration on the server.');
        }
    }

}