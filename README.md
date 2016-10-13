# Ride: Mail Library

Mail library of the PHP Ride framework.

## What's In This Library

### MailAddress

The _MailAddress_ class is used to deal with email addresses.
All recipients of a _MailMessage_ are set with this class.
It allows straight email addresses but also addresses in the format of _name <email>_.

### MailMessage

The _MailMessage_ class is a data container of the mail to send.
You can set the recipients through the To, CC and BCC field.
You can flag a message as an HTML message, add attachments and more.

### MimePart

The _MimePart_ class is internally used to add attachments to a _MailMessage_.
You can use it manually to create a custom multipart message.

### Transport

The _Transport_ interface offers a way to implement the actual sending of a mail.
It's also the starting point when you want to send a mail with this library as it also acts as a factory for messages.

A generic implentation is provided is this library through the _SimpleTransport_ class.
This uses the PHP functions to send a mail.

### MessageParser

The _MessageParser_ class is a helper for the _Transport_ implementations.
It takes a _MailMessage_ and extracts all information into a structure which can be used by the transport.

## Code Sample

Check the following code sample to some of the possibilities of this library.

```php
<?php

use ride\library\log\Log;
use ride\library\mail\transport\SimpleTransport;
use ride\library\mail\transport\Transport;
use ride\library\system\file\FileSystem;

function createTransport(Log $log) {
    // simple, create an instance
    $transport = new SimpleTransport($log);
    
    // you set some defaults to you don't have to set this to each message
    $transport->setDefaultFrom('from@domain.com');
    $transport->setDefaultReplyTo('from@domain.com');
    
    // you can set a debug address 
    // no recipients in the To, CC and BCC will receive the message, only this debug to address
    $transport->setDebugTo('me@domain.com');
    
    return $transport;
}

function sendMail(MandrillTransport $transport, FileSystem $fileSystem) {
    $message = $transport->createMessage();
    $message->setSubject('My subject');
    $message->setRecipient('to@domain.com');
    $message->addCc('To 2 <to2@domain.com>');
    $message->addBcc(array('to3@domain.com', 'To 3 <to3@domain.com>'));
    $message->setIsHtmlMessage(true);
    $message->setMessage('<html><body><p>...</p></body></html>');
    
    $file = $fileSystem->getFile('/path/to/image.png');
    
    $message->addAttachement($file, 'image/png');
    
    try {
        $transport->send($message);
    } catch (MailException $exception) {
        
    }
}
```

## Related Modules

- [ride/app-mail](https://github.com/all-ride/ride-app-mail)
- [ride/app-mail-mandrill](https://github.com/all-ride/ride-app-mail-mandrill)
- [ride/lib-log](https://github.com/all-ride/ride-lib-log)
- [ride/lib-mail-mandrill](https://github.com/all-ride/ride-lib-mail-mandrill)
- [ride/lib-system](https://github.com/all-ride/ride-lib-system)
- [ride/lib-validation](https://github.com/all-ride/ride-lib-validation)

## Installation

You can use [Composer](http://getcomposer.org) to install this library.

```
composer require ride/lib-mail
```
