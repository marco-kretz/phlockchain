<?php

namespace Phlockchain\Network;

/**
 * Adds event capabilities.
 *
 * @author Marco Kretz <mk@marco-kretz.de>
 */
abstract class AbstractListener
{
    /**
     * Client address.
     *
     * @var string
     */
    protected string $address;

    /**
     * List of known clients in the network.
     *
     * @var array
     */
    protected array $clientNodes = [];

    /**
     * Registered events listeners.
     *
     * @var array
     */
    private array $eventListeners = [];

    /**
     * Attach a callback to an event.
     *
     * @param string $event
     * @param callable $callback
     *
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        $event = strtolower(trim($event));
        if (!isset($this->eventListeners[$event])) {
            $this->eventListeners[$event] = [];
        }

        $this->eventListeners[$event][] = $callback;
    }

    /**
     * Trigger an event resulting in all attached callbacks to get called.
     *
     * @param string $event
     * @param array|null $payload
     *
     * @return void
     */
    public function trigger(string $event, ?array $payload = null): void
    {
        $event = strtolower(trim($event));
        if (isset($this->eventListeners[$event])) {
            foreach ($this->eventListeners[$event] as $callback) {
                if ($payload === null) {
                    $callback();
                } else {
                    $callback($payload);
                }
            }
        }
    }

    /**
     * Propagate an event to all other client in the network.
     * Injects the current client as 'sender' into the payload.
     *
     * Yeye I know, insecure, but it's just for demonstration :)
     *
     * @param string $event
     * @param array|null $payload
     *
     * @return void
     */
    public function propagate(string $event,? array $payload = null): void
    {
        $event = strtolower(trim($event));
        foreach ($this->clientNodes as $node) {
            if ($node instanceof self) {
                $node->trigger($event, [
                    ...$payload,
                    'sender' => $this,
                ]);
            }
        }
    }
}