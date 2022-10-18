<?php

namespace Phlockchain\Command;

use Phlockchain\Block;
use Phlockchain\Blockchain;
use Phlockchain\Network\Client;
use Phlockchain\Network\Server;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'bc:network')]
final class P2PCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Phlockchain - Simple Blockhain Implementation');

        // Define the payloads/blocks
        $payloads = ['Holla', 'die', 'Waldfee!'];

        // Init P2P Server (only used for getting to know each other.
        $server = new Server();

        // First Client
        $client1 = new Client('Marco K. (Origin)');
        $server->connect($client1);

        // Clients builds a blockchain
        foreach ($payloads as $id => $payload) {
            $block = new Block($id, $payload);
            $client1->getBlockchain()->add($block);

            if (!$block->isSigned()) {
                $client1->getBlockchain()->mine($block);
            }
        }

        // New Client joins
        $client2 = new Client('Christin');
        $server->connect($client2);

        // New Client joins
        $client3 = new Client('Arthos');
        $server->connect($client3);

        // New Client joins
        $client4 = new Client('Marco S.');
        $server->connect($client4);

        $io->write(PHP_EOL);

        $io->info('Inserting valid block');
        $client2->addBlock(new Block(3, 'New transaction!'));

        $io->info('Inserting invalid block');
        $client3->addBlock(new Block(3, 'New transaction!'), false);

        return Command::SUCCESS;
    }
}
