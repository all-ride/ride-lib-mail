<?php

namespace ride\library\mail;

use ride\library\mail\exception\MailException;
use ride\library\validation\validator\EmailValidator;

/**
 * Partial implementation of the address specification of RFC 2822: Internet
 * Message Format
 * @link http://www.rfc-editor.org/rfc/rfc2822.txt
 */
class MailAddress {

    /**
     * Regular expression of display-name <addr-spec> format
     * @var string
     */
    const REGEX_ADDRESS = '/^((.)*) <((.)*@(.)*)>$/';

    /**
     * The display name
     * @var string
     */
    private $displayName;

    /**
     * The email address
     * @var string
     */
    private $emailAddress;

    /**
     * Constructs a new address
     * @param string|MailAddress $address Email address in one of the supported
     * formats
     *
     * <p>Supported formats:</p>
     * <ul>
     * <li>name@domain.com</li>
     * <li>Name &lt;name@domain.com></li>
     * <li>Instance of MailAddress</li>
     * </ul>
     * @return null
     * @throws ride\library\mail\exception\MailException when the provided
     * address is empty or invalid
     */
    public function __construct($address) {
        if ($address instanceof self) {
            $this->displayName = $address->getDisplayName();
            $this->emailAddress = $address->getEmailAddress();

            return;
        }

        if (!is_string($address) || $address == '') {
            throw new MailException('Could not create new mail address: address is empty or not a string');
        }

        $address = trim($address);
        $address = str_replace(array("\n", "\r"), '', $address);

        $matches = array();
        if (preg_match(self::REGEX_ADDRESS, $address, $matches)) {
            $this->displayName = trim($matches[1]);
            $address = $matches[3];
        }

        $validator = new EmailValidator();
        if ($validator->isValid($address)) {
            $this->emailAddress = $address;
            if (empty($this->displayName) && strpos($address, '@') !== false) {
                list($this->displayName, $null) =  explode('@', $address);
            }

            return;
        }

        throw new MailException('Provided address ' . $address . ' is invalid');
    }

    /**
     * Gets a string representation of this address
     * @return string
     */
    public function __toString() {
        return $this->getDisplayName() . ' <' . $this->getEmailAddress() . '>';
    }

    /**
     * Gets the display name
     * @return string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * Gets the email address
     * @return string
     */
    public function getEmailAddress() {
        return $this->emailAddress;
    }

}
