<?php

namespace Phlockchain;

use DateTimeImmutable;

/**
 * Represents a single block in the blockchain.
 *
 * @author Marco Kretz <mk@marco-kretz.de>
 */
final class Block
{
    /**
     * The id
     *
     * @var int
     */
    private int $id;

    /**
     * The nonce
     *
     * @var int
     */
    private int $nonce;

    /**
     * The payload
     *
     * @var string
     */
    private string $payload;

    /**
     * The creation date
     *
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $createdAt;

    /**
     * The previous block in the chain
     *
     * @var Block|null
     */
    private ?Block $previous;

    public function __construct(int $id, string $payload, ?Block $previous = null)
    {
        $this->id = $id;
        $this->nonce = 0;
        $this->setPayload($payload);
        $this->createdAt = new DateTimeImmutable();
        $this->previous = $previous;
    }

    public function setNonce(int $nonce): self
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function getNonce(): int
    {
        return $this->nonce;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPrevious(): ?Block
    {
        return $this->previous;
    }

    public function setPrevious(?Block $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * The block's hash.
     * Always calculate the hash on the fly.
     *
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->id . $this->nonce . $this->getPreviousHash() . $this->payload . $this->createdAt->format('U'));
    }

    /**
     * The previous block's hash.
     *
     * @return string
     */
    public function getPreviousHash(): string
    {
        if ($this->previous === null) {
            return Blockchain::NO_PREV_BLOCK_HASH;
        }

        return $this->previous->getHash();
    }

    /**
     * Check if the block is signed/valid.
     *
     * @return bool
     */
    public function isSigned(): bool
    {
        return str_starts_with($this->getHash(), Blockchain::SIGNED_PREFIX);
    }

    public function __toString(): string
    {
        return "{ id: $this->id, nonce: $this->nonce, payload: $this->payload, hash: {$this->getHash()}, createdAt: {$this->createdAt->format('c') }";
    }
}
