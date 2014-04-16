<?php

namespace ride\library\mail\transport;

use ride\library\log\Log;
use ride\library\mail\MailMessage;

/**
 * Abstract mail transport with common methods
 */
abstract class AbstractTransport implements Transport {

    /**
     * Configuration key for the line break between the headers
     * @var string
     */
    const PARAM_HEADER_LINE_BREAK = 'mail.header.line.break';

    /**
     * Default value for the line break between the headers
     * @var string
     */
    const DEFAULT_HEADER_LINE_BREAK = "\n";

    /**
     * Name of the source for the mail logs
     * @var string
     */
    const LOG_SOURCE = 'mail';

    /**
     * Instance of the Log
     * @var \ride\library\log\Log
     */
    protected $log;

    /**
     * Character for line breaks in the headers
     * @var string
     */
    protected $lineBreak;

    /**
     * The default from address, default sender
     * @var string
     */
    protected $defaultFrom;

    /**
     * The debug to address, debug recipient
     * @var string
     */
    protected $debugTo;

    /**
     * Constructs a new message transport
     * @return null
     */
    public function __construct(Log $log = null, $lineBreak = null) {
        if (!$lineBreak) {
            $lineBreak = self::DEFAULT_HEADER_LINE_BREAK;
        }

        $this->log = $log;
        $this->lineBreak = $lineBreak;

        $this->defaultFrom = null;
        $this->debugTo = null;
    }

    /**
     * Sets the default sender
     * @param string $from The email address of the default sender. This will be
     * used when a mail has no sender set
     * @return string
     */
    public function setDefaultFrom($from) {
        $this->defaultFrom = $from;
    }

    /**
     * Gets the default sender
     * @return string
     */
    public function getDefaultFrom() {
        return $this->defaultFrom;
    }

    /**
     * Sets the debug recipient. When this address is set, all forms of
     * recipients (to, cc, bcc) will be removed and replaced by this set
     * address in the To header.
     * @param string $to The email address of the debug recipient.
     * @return string
     */
    public function setDebugTo($to) {
        $this->debugTo = $to;
    }

    /**
     * Gets the debug recipient
     * @return string
     */
    public function getDebugTo() {
        return $this->debugTo;
    }

    /**
     * Creates a mail message
     * @return \ride\library\mail\MailMessage
     */
    public function createMessage() {
        return new MailMessage();
    }

    /**
     * Implode an array of headers with the line break character from the configuration
     * @param array $headers Array with header strings
     * @return string String with the headers
     */
    protected function implodeHeaders(array $headers) {
        return implode($this->lineBreak, $headers);
    }

    /**
     * Log the sending of a message
     * @param string $subject Subject of the message
     * @param string $headers String with the headers
     * @param boolean $isError Flag to see if the message is accepted for sending
     * @return null
     */
    protected function logMail($subject, $headers, $isError) {
        $title = ($isError ? 'Could not send' : 'Send') . ' mail with subject \'' . $subject . '\'';
        $description = "Headers:\n" . $headers;

        $this->log($title, $description, $isError);
    }

    /**
     * Log an event in the mail log
     * @param string $title Title of the log message
     * @param string $description Description of the log message
     * @param boolean $isError
     * @return null
     */
    protected function log($title, $description = '', $isError = false) {
        if (!$this->log) {
            return;
        }

        if ($isError) {
            $this->log->logError($title, $description, self::LOG_SOURCE);
        } else {
            $this->log->logInformation($title, $description, self::LOG_SOURCE);
        }
    }

}
