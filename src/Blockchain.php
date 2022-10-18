<?php

namespace Phlockchain;

use RuntimeException;

/**
 * Represents the actual Blockchain.
 *
 * @author Marco Kretz <mk@marco-kretz.de>
 */
class Blockchain
{
    /**
     * The starting string needed for a hash to be seen as signed/valid.
     *
     * @var string
     */
    public const SIGNED_PREFIX = '0000';

    /**
     * The hash returned for the previous block if no previous block exists.
     *
     * @var string
     */
    public const NO_PREV_BLOCK_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    /**
     * @var Block[]
     */
    private array $blocks;

    /**
     * Add one or more blocks to the blockchain.
     *
     * @param Block ...$blocks
     *
     * @return $this
     *
     * @throws RuntimeException Thrown, if the previous block is not signed/valid.
     */
    public function add(Block ...$blocks): self
    {
        foreach ($blocks as $block) {
            if (empty($this->blocks)) {
                $block->setPrevious(null);
            } else {
                $previousBlock = end($this->blocks);
                if ($previousBlock->isSigned()) {
                    $block->setPrevious($previousBlock);
                } else {
                    throw new RuntimeException('Last block must be signed before adding new blocks!');
                }
            }

            $this->blocks[] = $block;
        }

        return $this;
    }

    /**
     * Return a specific block.
     *
     * @param int $id
     *
     * @return Block|null
     */
    public function get(int $id): ?Block
    {
        return $this->blocks[$id] ?? null;
    }

    /**
     * Check if the chain is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->blocks);
    }

    /**
     * Check if all block and therefore the whole chain is signed/valid.
     *
     * @return bool
     */
    public function isCompletelySigned(): bool
    {
        foreach ($this->blocks as $block) {
            if (!$block->isSigned()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Start mining the given block for it to be signed.
     *
     * @param Block $block
     *
     * @return Block
     *
     * @throws RuntimeException Throws, if the block could not be signed.
     */
    public function mine(Block $block): Block
    {
        if ($block->isSigned()) {
            return $block;
        }

        $currentNonce = PHP_INT_MIN;
        while (!$block->isSigned() && $currentNonce < PHP_INT_MAX) {
            $block->setNonce($currentNonce++);
        }

        if (!$block->isSigned()) {
            throw new RuntimeException('Unable to find valid nonce for block!');
        }

        return $block;
    }

    /**
     * Check if a block would theoretically be signed/valid when added to the chain.
     *
     * @param Block $block
     *
     * @return bool
     */
    public function validateBlock(Block $block): bool
    {
        if (!empty($this->blocks)) {
            $block->setPrevious(end($this->blocks));
        }

        return $block->isSigned();
    }

    /**
     * Deep clone a blockchain.
     *
     * @return void
     */
    public function __clone()
    {
        $newBlocks = [];
        foreach ($this->blocks as $block) {
            $newBlock = clone $block;
            if ($newBlock->getPrevious() !== null) {
                $newBlock->setPrevious(end($newBlocks));
            }
            $newBlocks[] = $newBlock;
        }

        $this->blocks = $newBlocks;
    }
}