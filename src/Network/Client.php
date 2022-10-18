<?php

namespace Phlockchain\Network;

use Phlockchain\Block;
use Phlockchain\Blockchain;

class Client extends AbstractListener
{
    private string $name;

    private Blockchain $blockchain;

    public function __construct(string $name)
    {
        $this->address = uniqid('Client-', true); // Unique enough for our use-case
        $this->name = $name;
        $this->blockchain = new Blockchain();

        // Fired when first connected
        $this->on(Events::CONNECTION_ESTABLISHED, function (array $payload) {
            print("$this->name: I'm connected! =)" . PHP_EOL);
            if (!empty($payload['clients']) && is_array($payload['clients'])) {
                $this->clientNodes = $payload['clients'];

                // Propagate own blockchain
                if (!$this->blockchain->isEmpty()) {
                    $this->propagate(Events::BLOCKCHAIN_RECEIVED, ['blockchain' => clone $this->blockchain]);
                }
            }
        });

        // Fired when already connected and a new client joins the network
        $this->on(Events::NEW_CLIENT_CONNECTED, function (array $payload) {
            if (
                isset($payload['client'])
                && $payload['client'] instanceof Client
                && $payload['client']->getAddress() !== $this->address
                && !array_key_exists($payload['client']->getAddress(), $this->clientNodes)
            ) {
                print("$this->name: Hello {$payload['client']->getName()}!" . PHP_EOL);
                $this->clientNodes[$payload['client']->getAddress()] = $payload['client'];
                if (!$this->blockchain->isEmpty()) {
                    $this->propagate(Events::BLOCKCHAIN_RECEIVED, ['blockchain' => clone $this->blockchain]);
                }
            }
        });

        // Fired, when a complete blockchain is received by another client.
        // We only set it as our own, if our blockchain is empty.
        $this->on(Events::BLOCKCHAIN_RECEIVED, function (array $payload) {
            if (
                isset($payload['blockchain'])
                && $payload['blockchain'] instanceof Blockchain
                && $this->blockchain->isEmpty()
                && $payload['blockchain']->isCompletelySigned()
            ) {
                print("$this->name: Thanks for the chain!" . PHP_EOL);
                $this->blockchain = $payload['blockchain'];
            }
        });

        $this->on(Events::BLOCK_ADDED, function (array $payload) {
            if (isset($payload['block']) && $payload['block'] instanceof Block) {
                print("$this->name: Received a new block, checking it mom..." . PHP_EOL);
                $block = $payload['block'];

                if (!$this->blockchain->validateBlock($block)) {
                    print("$this->name: Hooly, are you trying to fool me {$payload['sender']->getName()}?? Votekick!" . PHP_EOL);
                } else {
                    print("$this->name: Nice block, adding it!" . PHP_EOL);
                    $this->blockchain->add($block);
                }
            }
        });
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the client's blockchain as a copy, so you can't modify another client's blockchain directly.
     *
     * @return Blockchain
     */
    public function getBlockchain(): Blockchain
    {
        return $this->blockchain;
    }

    public function addBlock(Block $block, bool $signIt = true): void
    {
        try {
            $this->blockchain->add($block);
            if ($signIt) {
                $this->blockchain->mine($block);
            }

            print("$this->name: Here, new block for ya guys :>" . PHP_EOL);
            $this->propagate(Events::BLOCK_ADDED, ['block' => $block]);
        } catch (\RuntimeException $e) {
        }
    }
}