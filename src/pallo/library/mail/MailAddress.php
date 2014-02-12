<?php

namespace pallo\library\mail;

use pallo\library\mail\exception\MailException;
use pallo\library\validation\validator\EmailValidator;

/**
 * Partial implementation of the address specification of {@link http://www.rfc-editor.org/rfc/rfc2822.txt
 * RFC 2822: Internet Message Format}.
 *
 * Supported formats are:
 * <ul>
 *     <li>display-name <addr-spec>, for example: User <user@example.com></li>
 *     <li>addr-spec, for example: user@example.com</li>
 * </ul>
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
     * @param string $address The email address in one of the supported formats
     * @return null
     * @throws pallo\library\mail\exception\MailException when the provided address is empty or invalid
     */
    public function __construct($address) {
        if (!is_string($address) || $address == '') {
            throw new MailException('Invalid address provided', 0);
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