<?php
declare(strict_types=1);

namespace App\Warehouse;

use JsonSerializable;
use OutOfBoundsException;

class ProductList implements JsonSerializable
{
    /**
     * @var Product[]
     */
    private array $products;

    public function __construct(?array $products = null)
    {
        if ($products) {
            foreach ($products as $product) {
                $this->products[] = new Product(
                    $product->name,
                    $product->quantity,
                    $product->id,
                    $product->createdAt,
                    $product->updatedAt
                );
            }
            return;
        }
        $this->products = [];
    }

    public function add(Product $product): void
    {
        $this->products[] = $product;
    }

    public function delete(Product $product): void
    {
        foreach ($this->products as $index => $otherProduct) {
            if ($product === $otherProduct) {
                unset($this->products[$index]);
                $this->products = array_values($this->products);
            }
        }
    }

    public function get(string $id): Product
    {
        foreach ($this->products as $product) {
            if ($product->getId() === $id) {
                return $product;
            }
        }
        throw new OutOfBoundsException("Product with id $id not found");
    }

    public function getAll(): array
    {
        return $this->products;
    }

    public function jsonSerialize(): array
    {
        return $this->products;
    }
}
