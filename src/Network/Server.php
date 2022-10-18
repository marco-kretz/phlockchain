<?php

namespace Phlockchain\Network;

/**
 * Client registrar to help clients find each other initially.
 * Not really P2P I know, but good enough for demonstration purposes :)
 *
 * @author Marco Kretz <mk@marco-kretz.de>
 */
class Server
{
    /*
     * Client registrar.
     *
     * @var Client[] $clients
     */
    private array $clients = [];

    /**
     * Connect a client to the virtual P2P-Network.
     *
     * @param Client $client
     *
     * @return void
     */
    public function connect(Client $client): void
    {
        // Client already connected?
        if (array_key_exists($client->getAddress(), $this->clients)) {
            return;
        }

        // Tell the new client about all other currently connected clients
        $client->trigger(Events::CONNECTION_ESTABLISHED, ['clients' => $this->clients]);

        // Tell all other currently connected clients about the new client
        foreach ($this->clients as $existingClient) {
            $existingClient->trigger(Events::NEW_CLIENT_CONNECTED, ['client' => $client]);
        }

        // Add client to the registrar
        $this->clients[$client->getAddress()] = $client;
    }
}