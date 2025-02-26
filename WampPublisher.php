<?php

/**
 * This file is part of bdk/wamp-publisher
 *
 * @package   bdk\PubSub
 * @author    Brad Kent <bkfake-github@yahoo.com>
 * @license   http://opensource.org/licenses/MIT MIT
 * @copyright 2014-2022 Brad Kent
 * @version   v1.1
 * @link      http://www.github.com/bkdotcom/WampPublisher
 */

namespace bdk;

use WebSocket\Client;

/**
 * Publish messages to a WAMP router
 */
class WampPublisher
{
    const CODE_HELLO = 1;
    const CODE_PUBLISH = 16;

    public $connected = false;

    protected $cfg;
    protected $client;

    /**
     * Constructor
     *
     * @param array $cfg           config
     * @param array $clientOptions Textalk options
     */
    public function __construct($cfg = array(), $clientOptions = array())
    {
        $this->cfg = \array_merge(array(
            'clientOptions' => \array_merge(array(
                'headers' => array(
                    'origin' => 'localhost',
                    'Sec-WebSocket-Protocol' => 'wamp.2.json',
                ),
            ), $clientOptions),
            'realm' => 'myRealm',
            'url' => 'ws://127.0.0.1:9090/',
        ), $cfg);
        $this->initClient();
    }

    /**
     * Initialize WAMP client
     *
     * @return void
     */
    public function initClient()
    {
        try {
            $this->client = new Client($this->cfg['url'], $this->cfg['clientOptions']);
            /*
                Perform WAMP handshake
            */
            $msg = array(self::CODE_HELLO, $this->cfg['realm'], array());
            $this->client->send(\json_encode($msg));
            $this->client->receive();
            $this->connected = true;
        } catch (\Exception $e) {
            $this->client = null;
        }
    }

    /**
     * Publish to topic
     *
     * @param string $topic   topic
     * @param array  $args    arguments
     * @param array  $options options
     *
     * @return void
     */
    public function publish($topic, $args = array(), $options = array())
    {
        if (!$this->client) {
            return;
        }
        $msg = array(
            self::CODE_PUBLISH,
            $this->getUniqueId(),
            $options,
            $topic,
            $args,
        );
        $json = \json_encode($msg); // JSON_INVALID_UTF8_SUBSTITUTE
        if ($json === false) {
            \trigger_error(\json_last_error() . ': ' . \json_last_error_msg());
        }
        // remove \u0000 from the beginning of any obj keys
        // avoid JSON_ERROR_INVALID_PROPERTY_NAME when decoding
        $json = \preg_replace('/"\\\\u0000([^"]*)":/', '"$1":', $json);
        $this->client->send($json);
    }

    /**
     * Generate a unique id
     *
     * @return mixed
     */
    private function getUniqueId()
    {
        $filter      = 0x1fffffffffffff; // 53 bits
        $randomBytes = \openssl_random_pseudo_bytes(8);
        list($high, $low) = \array_values(\unpack('N2', $randomBytes));
        return \abs(($high << 32 | $low) & $filter);
    }
}
