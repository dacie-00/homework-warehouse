<?php
declare(strict_types=1);

namespace App\Warehouse;

use JsonSerializable;

class ProductList implements JsonSerializable
{
    /**
     * @var Product[]
     */
    private array $products;

    public function __construct(\stdClass $products = null)
    {
        if ($products) {
            foreach ($products as $product) {
                $this->products[$product->id] = new Product(
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
        $this->products[$product->getId()] = $product;
    }

    public function delete(Product $product): void
    {
        unset($this->products[$product->getId()]);
    }

    public function get(string $id): Product
    {
        return $this->products[$id];
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
