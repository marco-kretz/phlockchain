<?php

namespace Phlockchain\Network;

class Events
{
    public const CONNECTION_ESTABLISHED = 'connection_established';
    public const NEW_CLIENT_CONNECTED = 'new_client_connected';
    public const BLOCK_ADDED = 'block_received';
    public const BLOCKCHAIN_RECEIVED = 'blockchain_received';
}