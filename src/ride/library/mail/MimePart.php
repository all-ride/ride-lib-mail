<?php

namespace ride\library\mail;

/**
 * A MIME part of a mail message
 */
class MimePart {

    /**
     * UTF-8 charset
     * @var string
     */
    const CHARSET_UTF8 = 'utf-8';

    /**
     * Base64 encoding
     * @var string
     */
    const ENCODING_BASE64 = 'base64';

    /**
     * Binary encoding
     * @var string
     */
    const ENCODING_BINARY = 'binary';

    /**
     * 7bit encoding
     * @var string
     */
    const ENCODING_7BIT = '7bit';

    /**
     * 8bit encoding
     * @var string
     */
    const ENCODING_8BIT = '8bit';

    /**
     * Quoted printable encoding
     * @var string
     */
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * Maximum line length
     * @var integer
     */
    const LINE_LENGTH = 78;

    /**
     * Line break
     * @var string
     */
    const LINE_BREAK = "\n";

    /**
     * MIME type for HTML text
     * @var string
     */
    const MIME_TEXT_HTML = 'text/html';

    /**
     * MIME type for plain text
     * @var string
     */
    const MIME_TEXT_PLAIN = 'text/plain';

    /**
     * The body of this part
     * @var string
     */
    private $body;

    /**
     * The charset of this part
     * @var string
     */
    private $charset;

    /**
     * The encoding of this part
     * @var string
     */
    private $encoding;

    /**
     * The mime of this part
     * @var string
     */
    private $mime;

    /**
     * Attributes of this part
     * @var array
     */
    private $attributes;

    /**
     * Constructs a new MIME part
     * @param string $body The body of the part
     * @param string $mime The MIME type of the part, defaults to plain text
     * @param string $charset The charset of this part, defaults to UTF-8
     * @param string $encoding The encoding of this part, defaults to 7bit
     * @return null
     */
    public function __construct($body = null, $mime = null, $charset = null, $encoding = null, $wrap = true) {
        $this->attributes = array();

        if ($mime === null) {
            $mime = self::MIME_TEXT_PLAIN;
        }
        if ($charset === null) {
            $charset = self::CHARSET_UTF8;
        }
        if ($encoding === null) {
            $encoding = self::ENCODING_7BIT;
        }

        $this->setBody($body, $wrap);
        $this->setCharset($charset);
        $this->setMimeType($mime);
        $this->setTransferEncoding($encoding);
    }

    /**
     * Sets the body of this parts. The provided body is automatically wrapped to the maximum line length
     * @param string $body The body of this part
     * @return null
     */
    public function setBody($body, $wrap = true) {
        if ($wrap) {
            $body = wordwrap($body, self::LINE_LENGTH, self::LINE_BREAK);
        }

        $this->body = $body;
    }

    /**
     * Gets the body of this part
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Gets the decoded body for display
     * @return string
     */
    public function getDecodedBody() {
        if ($this->encoding == self::ENCODING_BASE64) {
            return base64_decode($this->body);
        } elseif ($this->encoding == self::ENCODING_QUOTED_PRINTABLE) {
            return quoted_printable_decode($this->body);
        }

        return $this->body;
    }

    /**
     * Gets the size of the body
     * @return integer
     */
    public function getSize() {
        return strlen($this->body);
    }

    /**
     * Sets the character set of this part
     * @param string $charset The characterset
     * @return null
     */
    public function setCharset($charset) {
        $this->attributes['charset'] = $charset;
    }

    /**
     * Gets the charset of this part
     * @return string
     */
    public function getCharset() {
        if (!isset($this->attributes['charset'])) {
            return null;
        }

        return $this->attributes['charset'];
    }

    /**
     * Sets a encoding attribute
     * @param string $name
     * @param string $value
     * @return null
     */
    public function setAttribute($name, $value) {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Gets the encoding attributes
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Sets the MIME type of this part
     * @param string $mime The MIME type
     * @return null
     */
    public function setMimeType($mime) {
        $this->mime = $mime;
    }

    /**
     * Gets the MIME type of this part
     * @return string
     */
    public function getMimeType() {
        return $this->mime;
    }

    /**
     * Sets the transfer encoding of this part
     * @param string $encoding The encoding
     * @return null
     */
    public function setTransferEncoding($encoding) {
        $this->encoding = $encoding;
    }

    /**
     * Gets the transfer encoding
     * @return string
     */
    public function getTransferEncoding() {
        return $this->encoding;
    }

}
