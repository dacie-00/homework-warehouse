<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;

class Product
{
    private int $id;
    private string $name;
    private int $quantity;
    private ?Carbon $createdAt;
    private ?Carbon $updatedAt;

    public function __construct(int $id, string $name, int $quantity, ?string $createdAt = null, ?string $updatedAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now("UTC");
        $this->createdAt = $updatedAt ? Carbon::parse($updatedAt) : Carbon::now("UTC");
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
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

}