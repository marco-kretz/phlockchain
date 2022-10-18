<?php

namespace Phlockchain;

use DateTimeImmutable;

final class Block
{
    private const NO_PREV_BLOCK_HASH = '0';

    private int $id;

    private int $nonce;

    private string $payload;

    private DateTimeImmutable $createdAt;

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

    public function getHash(): string
    {
        return hash('sha256', $this->id . $this->nonce . $this->getPreviousHash() . $this->payload . $this->createdAt->format('U'));
    }

    public function getPreviousHash(): string
    {
        if ($this->previous === null) {
            return Blockchain::NO_PREV_BLOCK_HASH;
        }

        return $this->previous->getHash();
    }

    public function isSigned(): bool
    {
        return str_starts_with($this->getHash(), Blockchain::SIGNED_PREFIX);
    }

    public function __toString(): string
    {
        return "{ id: $this->id, nonce: $this->nonce, payload: $this->payload, hash: {$this->getHash()}, createdAt: {$this->createdAt->format('c') }";
    }
}
