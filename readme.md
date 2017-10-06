Wamp&#xfeff;Publisher
===============

Push messages to a [WAMP (Web Application Messaging Protocol)](http://wamp-proto.org/) router

Wamp Publisher only implements the publish portion of the WAMP protocol.  It does NOT implement Remote Procedure Calls or Subscribe.

Use it in your script/application to implement logging, notifications, or perform live website updates.
(see some WAMP examples on [crossbar.io](https://demo.crossbar.io/))

> WampPublisher is built on the websocket client [textalk/websocket-php](https://github.com/textalk/websocket-php)

### Installation

Download Composer (if not already installed) [more info](https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable)

    $ curl -sS https://getcomposer.org/installer | php

Require WampPublisher (and it's dependency) in your project

    $ php composer.phar require bdk/wamp-publisher

Everything is now good to go.

### Usage Example

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use bdk\WampPublisher;

$wamp = new \bdk\WampPublisher(array(
	'url' => 'ws://127.0.0.1:9090/',
	'realm' => 'myRealm',
));
$wamp->publish('my.topic', array("I'm published to the my.topic topic"));
$sum = 1 + 1;  // WAMP Publisher doesn't block you from doing other tasks
$wamp->publish('my.topic', array("Another message"));
```
