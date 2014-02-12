<?php

namespace pallo\library\mail\transport;

use pallo\library\mail\MailMessage;

/**
 * Interface for the mail transport
 */
interface Transport {

    /**
     * Creates a mail message
     * @return pallo\library\mail\MailMessage
     */
    public function createMessage();

    /**
     * Delivers a mail message
     * @param pallo\library\mail\MailMessage $message
     * @return null
     */
    public function send(MailMessage $message);

}