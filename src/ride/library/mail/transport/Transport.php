<?php

namespace ride\library\mail\transport;

use ride\library\mail\MailMessage;

/**
 * Interface for the mail transport
 */
interface Transport {

    /**
     * Creates a mail message
     * @return \ride\library\mail\MailMessage
     */
    public function createMessage();

    /**
     * Delivers a mail message
     * @param \ride\library\mail\MailMessage $message
     * @return null
     */
    public function send(MailMessage $message);

}