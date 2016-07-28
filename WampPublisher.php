<?php

namespace bdk;

use \WebSocket\Client;

/**
 * Publish messages to a WAMP router
 */
class WampPublisher
{

	protected $cfg;
	protected $client;

	/**
	 * Constructor
	 *
	 * @param array $cfg config
	 */
	public function __construct($cfg = array())
	{
		$this->cfg = array_merge(array(
			'url' => 'ws://127.0.0.1:9090/',
			'realm' => 'myRealm',
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
		$this->client = new Client($this->cfg['url']);
		/*
			Perform WAMP handshake
		*/
		$msg = array(1, $this->cfg['realm'], array());	// HELLO
		$this->client->send(json_encode($msg));
		$this->client->receive();
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
		$msg = array(
			16,		// PUBLISH
			$this->getUniqueId(),
			$options,
			$topic,
			$args
		);
		$this->client->send(json_encode($msg));
	}

    /**
     * Generate a unique id
     *
     * @return mixed
     */
    private function getUniqueId()
    {
        $filter      = 0x1fffffffffffff; // 53 bits
        $randomBytes = openssl_random_pseudo_bytes(8);
        list($high, $low) = array_values(unpack("N2", $randomBytes));
        return abs(($high << 32 | $low) & $filter);
    }
}
