<?php

namespace ride\library\mail;

use ride\library\mail\exception\MailException;
use ride\library\system\file\File;

/**
 * A e-mail message: container of all the data to send a message
 */
class MailMessage {

    /**
     * Empty subject constant
     * @var string
     */
    const NO_SUBJECT = 'no subject';

    /**
     * Name of the alternate part of a multipart message
     * @var string
     */
    const PART_ALTERNATIVE = 'alternative';

    /**
     * Name of the body part of the message
     * @var string
     */
    const PART_BODY = 'body';

    /**
     * Auto submitted flag for the no value
     * @var string
     */
    const AUTO_SUBMITTED_NO = 'no';

    /**
     * Auto submitted flag for the auto-generated value
     * @var string
     */
    const AUTO_SUBMITTED_GENERATED = 'auto-generated';

    /**
     * Auto submitted flag for the auto-replied value
     * @var string
     */
    const AUTO_SUBMITTED_REPLIED = 'auto-replied';

    /**
     * The sender of this message
     * @var MailAddress
     */
    private $from;

    /**
     * The recipient(s) of this message
     * @var array
     */
    private $to;

    /**
     * The recipient(s) of this message (Carbon Copy)
     * @var array
     */
    private $cc;

    /**
     * The recipient(s) of this message (Blind Carbon Copy)
     * @var array
     */
    private $bcc;

    /**
     * The address to reply to when answering this message
     * @var Address
     */
    private $replyTo;

    /**
     * The return path of this message. The address where bounces should be sent to
     * @var Address
     */
    private $returnPath;

    /**
     * The subject of this message
     * @var string
     */
    private $subject;

    /**
     * Flag to see if this is a HTML message
     * @var boolean
     */
    private $isHtmlMessage;

    /**
     * Array containing the MIME parts of this message (attachments, alternate version, ...)
     * @var array
     */
    private $parts;

    /**
     * The id of this message
     * @var string
     */
    private $messageId;

    /**
     * Array with message ids to define previous correspondence which this message answers
     * @var array
     */
    private $inReplyTo;

    /**
     * Array with message ids to define other correspondence which this message references
     * @var array
     */
    private $references;

    /**
     * Auto generated/replied flag
     * @var string
     */
    private $autoSubmitted;

    /**
     * Constructs a new mail message
     * @return null
     */
    public function __construct() {
        $this->subject = self::NO_SUBJECT;
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->parts = array(
            self::PART_BODY => new MimePart('', MimePart::MIME_TEXT_PLAIN),
        );
        $this->references = array();
        $this->inReplyTo = array();
        $this->isHtmlMessage = false;
    }

    /**
     * Sets the sender
     * @param string|MailAddress $from The address of the sender
     * @return null
     */
    public function setFrom($from) {
        $this->from = $this->getAddress($from);
    }

    /**
     * Gets the sender
     * @return MailAddress The address of the sender
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * Sets the recipients
     * @param string|MailAddress|array $to The address(es) of the recipient(s)
     * @return null
     */
    public function setTo($to) {
        $this->to = $this->getAddresses($to);
    }

    /**
     * Gets the addresses of the recipients
     * @return array Array with Address objects
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * Adds recipients to Carbon Copy
     * @param string|MailAddress|array $bcc The address(es) of the recipient(s)
     * @return null
     */
    public function addCc($cc) {
        $cc = $this->getAddresses($cc);
        foreach ($cc as $address) {
            $this->cc[$address->getEmailAddress()] = $address;
        }
    }

    /**
     * Sets the recipients in Carbon Copy
     * @param string|MailAddress|array $cc The address(es) of the recipient(s)
     * @return null
     */
    public function setCc($cc) {
        $this->cc = $this->getAddresses($cc);
    }

    /**
     * Gets the addresses of the recipients in Carbon Copy
     * @return array Array with Address objects
     */
    public function getCc() {
        return $this->cc;
    }

    /**
     * Adds recipients to Blind Carbon Copy
     * @param string|MailAddress|array $bcc The address(es) of the recipient(s)
     * @return null
     */
    public function addBcc($bcc) {
        $bcc = $this->getAddresses($bcc);
        foreach ($bcc as $address) {
            $this->bcc[$address->getEmailAddress()] = $address;
        }
    }

    /**
     * Sets the recipients in Blind Carbon Copy
     * @param string|MailAddress|array $cc The address(es) of the recipient(s)
     * @return null
     */
    public function setBcc($bcc) {
        $this->bcc = $this->getAddresses($bcc);
    }

    /**
     * Gets the addresses of the recipients in Blind Carbon Copy
     * @return array Array with Address objects
     */
    public function getBcc() {
        return $this->bcc;
    }

    /**
     * Sets the address to reply to when answering this message
     * @param string|MailAddress $replyTo Address to reply to
     * @return null
     */
    public function setReplyTo($replyTo) {
        $this->replyTo = $this->getAddress($replyTo);
    }

    /**
     * Gets the address to reply to when answering this message
     * @return null|MailAddress Address to reply to
     */
    public function getReplyTo() {
        return $this->replyTo;
    }

    /**
     * Sets the return path of this message. This is the addess where bounces should be sent to.
     * @param string|MailAddress $returnPath The return path address
     * @return null
     */
    public function setReturnPath($returnPath) {
        $this->returnPath = $this->getAddress($returnPath);
    }

    /**
     * Gets the return path of this message. This is the addess where bounces should be sent to. If not set, the sender of the message will be returned.
     * @return null|MailAddress The return path of this message
     */
    public function getReturnPath() {
        if ($this->returnPath) {
            return $this->returnPath;
        }

        return $this->from;
    }

    /**
     * Sets whether this message is a HTML message
     *
     * When setting this message as a HTML message, a alternate body part will be created from the current body.
     * @param boolean $isHtmlMessage True for a HTML message, false otherwise
     * @return null
     */
    public function setIsHtmlMessage($isHtmlMessage) {
        $this->isHtmlMessage = $isHtmlMessage;

        if ($isHtmlMessage && !isset($this->parts[self::PART_ALTERNATIVE])) {
            $body = $this->parts[self::PART_BODY];

            $message = $body->getBody();

            $body->setBody(strip_tags($message));

            $this->parts[self::PART_ALTERNATIVE] = $body;
            $this->parts[self::PART_BODY] = new MimePart($message, MimePart::MIME_TEXT_HTML);
        } elseif (!$isHtmlMessage && isset($this->parts[self::PART_ALTERNATIVE])) {
            $this->parts[self::PART_BODY] = $this->parts[self::PART_ALTERNATIVE];

            unset($this->parts[self::PART_ALTERNATIVE]);
        }
    }

    /**
     * Gets whether this message is a HTML message
     * @return boolean
     */
    public function isHtmlMessage() {
        return $this->isHtmlMessage;
    }

    /**
     * Sets the contents of the body part.
     *
     * If this is a HTML message, the alternate part will be set with a stripped version of the provided message
     * @param string $message The contents of the body
     * @return null
     */
    public function setMessage($message) {
        $this->parts[self::PART_BODY]->setBody($message);

        if ($this->isHtmlMessage() && !isset($this->parts[self::PART_ALTERNATIVE])) {
            $this->parts[self::PART_ALTERNATIVE]->setBody(strip_tags($message));
        }
    }

    /**
     * Gets the contents of the body part
     * @return string
     */
    public function getMessage() {
        return $this->parts[self::PART_BODY]->getBody();
    }

    /**
     * Sets the subject of this message
     * @param string $subject The subject of the message
     * @return string
     */
    public function setSubject($subject) {
        if (!is_string($subject)) {
            throw new MailException('Provided subject is not a string');
        }

        $subject = trim($subject);
        if (empty($subject)) {
            $this->subject = self::NO_SUBJECT;
        } else {
            $subject = str_replace(array("\n", "\r"), '', $subject);
            $this->subject = $subject;
        }
    }

    /**
     * Gets the subject of this message
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Adds a file attachment to this message
     * @param \ride\library\system\file\File $attachment The file forthe attachment
     * @return string The name of the attachment MIME part
     */
    public function addAttachment(File $attachment, $mime = 'application/octet-stream') {
        $content = $attachment->read();
        $content = chunk_split(base64_encode($content));

        $part = new MimePart($content, $mime);
        $part->setTransferEncoding(MimePart::ENCODING_BASE64);

        return $this->addPart($part, $attachment->getName());
    }

    /**
     * Adds a MIME part to this message
     * @param MimePart $part The part to add
     * @param string $name Name for this part, if not provided, a name will be generated
     * @return string The name of the new part
     */
    public function addPart(MimePart $part, $name = null) {
        if (empty($name)) {
            $name = md5(microtime());
        }

        $this->parts[$name] = $part;

        return $name;
    }

    /**
     * Removes a MIME part from this message
     * @param string $name Name of the MIME part
     * @return null
     * @throws \ride\library\mail\exception\MailException when the part could not be found
     */
    public function removePart($name) {
        if (!$this->hasPart($name)) {
            throw new MailException('Could not delete part: no part with name ' . $name);
        }

        unset($this->parts[$name]);
    }

    /**
     * Gets a MIME part of this message
     * @param string $name Name of the MIME part
     * @return MimePart
     * @throws \ride\library\mail\exception\MailException when the part could not be found
     */
    public function getPart($name) {
        if (!$this->hasPart($name)) {
            throw new MailException('Could not get part: no part with name ' . $name);
        }

        return $this->parts[$name];
    }

    /**
     * Gets all the parts of this message
     * @return array Array with MimePart objects
     */
    public function getParts() {
        return $this->parts;
    }

    /**
     * Checks if this message has a MIME part with the provided name
     * @param string $name Name of the MIME part
     * @return boolean True if this message contains a part with the provided name, false otherwise
     */
    public function hasPart($name) {
        return isset($this->parts[$name]);
    }

    /**
     * Sets the id of this message
     * @param string $messageId The id of this message
     * @return null
     */
    public function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    /**
     * Gets the id of this message
     * @return string
     */
    public function getMessageId() {
        return $this->messageId;
    }

    /**
     * Sets the parents of this message
     * @param array $inReplyTo Array with the message id's of previous correspondence
     * @return null
     */
    public function setInReplyTo(array $inReplyTo) {
        $this->inReplyTo = $inReplyTo;
    }

    /**
     * Gets the parents of this message
     * @return array Array with the message id's of previous correspondence
     */
    public function getInReplyTo() {
        return $this->inReplyTo;
    }

    /**
     * Sets the references of this message
     * @param array $references Array with message id's of related messages
     * @return null
     */
    public function setReferences(array $references) {
        $this->references = $references;
    }

    /**
     * Gets the references of this message
     * @return array Array with the message id's of other correspondence
     */
    public function getReferences() {
        return $this->references;
    }

    /**
     * Gets the automatically generated/replied flag
     * @return string
     */
    public function getAutoSubmitted() {
        return $this->autoSubmitted;
    }

    /**
     * Mark this message as being automatically generated/replied
     *
     * For more details see RFC3834 - Recommendations for Automatic Responses to Electronic Mail
     * @param string $autoSubmitted Auto submit value (no, auto-generated or auto-replied
     * @return null
     * @throws \ride\library\mail\exception\MailException When the provided value is invalid
     */
    public function setAutoSubmitted($autoSubmitted) {
        if (!in_array($autoSubmitted, array(self::AUTO_SUBMITTED_NO, self::AUTO_SUBMITTED_GENERATED, self::AUTO_SUBMITTED_REPLIED))) {
            throw new MailException('Could not set the auto submitted flag: Invalid value, use either no, auto-generated or auto-replied');
        }

        $this->autoSubmitted = $autoSubmitted;
    }

    /**
     * Gets an array of Address objects from the provided addresses
     * @param string|MailAddress|array $addresses The addresses in a string or a object
     * @return array Array of MailAddress objects
     */
    private function getAddresses($addresses) {
        if (!$addresses) {
            return array();
        } elseif (!is_array($addresses)) {
            $addresses = array($addresses);
        }

        $result = array();
        foreach ($addresses as $address) {
            if (!$address) {
                continue;
            }

            $address = $this->getAddress($address);

            $result[$address->getEmailAddress()] = $address;
        }

        return $result;
    }

    /**
     * Gets a Address object from the provided address
     * @param string|MailAddress $address The address in a string or a object
     * @return MailAddress The provided address as a object
     */
    private function getAddress($address) {
        if (!($address instanceof MailAddress)) {
            $address = new MailAddress($address);
        }

        return $address;
    }

}
