<?php

namespace App;

class ProductCollection
{
    /**
     * @var Product[]
     */
    private array $products;

    public function __construct(array $products = null)
    {
        $this->products = $products ?? [];
    }

    public function add(Product $product): void
    {
        $this->products[$product->getId()] = $product;
    }

    public function delete(Product $product): void
    {
        unset($this->products[$product->getId()]);
    }

    public function get(): array
    {
        return $this->products;
    }

    public function setQuantity(Product $product, int $quantity): void
    {
        $product->setQuantity($quantity);
    }
}
