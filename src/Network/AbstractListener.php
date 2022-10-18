<?php

namespace Phlockchain\Network;

abstract class AbstractListener
{
    /**
     * @var string 24 characters long client address.
     */
    protected string $address;

    protected array $clientNodes = [];

    private array $eventListeners = [];

    public function on(string $event, callable $callback): void
    {
        $event = strtolower(trim($event));
        if (!isset($this->eventListeners[$event])) {
            $this->eventListeners[$event] = [];
        }

        $this->eventListeners[$event][] = $callback;
    }

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