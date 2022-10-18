<?php

namespace Phlockchain;

use Exception;
use RuntimeException;

class Blockchain
{
    public const SIGNED_PREFIX = '0000';
    public const NO_PREV_BLOCK_HASH = '0';

    /**
     * @var Block[]
     */
    private array $blocks;

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

    public function get(int $id): ?Block
    {
        return $this->blocks[$id] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->blocks);
    }

    public function isCompletelySigned(): bool
    {
        foreach ($this->blocks as $block) {
            if (!$block->isSigned()) {
                return false;
            }
        }

        return true;
    }

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

    public function validateBlock(Block $block): bool
    {
        if (!empty($this->blocks)) {
            $block->setPrevious(end($this->blocks));
        }

        return $block->isSigned();
    }

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