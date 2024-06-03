<?php
declare(strict_types=1);

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Product implements JsonSerializable
{
    private string $id;
    private string $name;
    private int $quantity;
    private ?Carbon $createdAt;
    private ?Carbon $updatedAt;

    public function __construct(string $name, int $quantity, ?string $id = null, ?string $createdAt = null, ?string $updatedAt = null)
    {
        $this->id = $id ?: Uuid::uuid4()->toString();
        $this->name = $name;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now("UTC");
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : Carbon::now("UTC");
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->updatedAt = Carbon::now("UTC");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?Carbon $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "quantity" => $this->quantity,
            "createdAt" => $this->createdAt->format(DateTimeInterface::ATOM),
            "updatedAt" => $this->updatedAt->format(DateTimeInterface::ATOM),
        ];
    }
}