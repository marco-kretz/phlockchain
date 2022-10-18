<?php

namespace Phlockchain\Network;

class Server
{
    /*
     * @var Client[] $clients
     */
    private array $clients = [];

    public function connect(Client $client): void
    {
        if (array_key_exists($client->getAddress(), $this->clients)) {
            return;
        }

        $client->trigger(Events::CONNECTION_ESTABLISHED, ['clients' => $this->clients]);
        foreach ($this->clients as $existingClient) {
            $existingClient->trigger(Events::NEW_CLIENT_CONNECTED, ['client' => $client]);
        }

        $this->clients[$client->getAddress()] = $client;
    }
}