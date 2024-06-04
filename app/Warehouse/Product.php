<?php
declare(strict_types=1);

namespace App\Warehouse;

use Carbon\Carbon;
use DateTimeInterface;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Product implements JsonSerializable
{
    private string $id;
    private string $name;
    private int $quantity;
    private Carbon $createdAt;
    private Carbon $updatedAt;

    public function __construct(
        string $name,
        int $quantity,
        ?string $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->id = $id ?: Uuid::uuid4()->toString();
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
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updatedAt;
    }

    public function updateUpdatedAt(): void
    {
        $this->updatedAt = Carbon::now("UTC");
    }

    public function jsonSerialize(): array
    {
        return [
            "name" => $this->name,
            "quantity" => $this->quantity,
            "id" => $this->id,
            "createdAt" => $this->createdAt->timezone("UTC")->format(DateTimeInterface::ATOM),
            "updatedAt" => $this->updatedAt->timezone("UTC")->format(DateTimeInterface::ATOM),
        ];
    }
}