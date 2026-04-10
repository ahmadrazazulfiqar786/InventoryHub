<?php

namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    public function getAllProducts(): Collection
    {
        try {
            return $this->productRepository->getAll();
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products: ' . $e->getMessage());
        }
    }

    public function getProductById(int $id): ?Product
    {
        try {
            return $this->productRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch product: ' . $e->getMessage());
        }
    }

    public function createProduct(array $data): Product
    {
        try {
            $product = $this->productRepository->create($data);

            if (! $product) {
                throw new Exception('Product creation failed.');
            }

            return $product;
        } catch (Exception $e) {
            throw new Exception('Failed to create product: ' . $e->getMessage());
        }
    }

    public function updateProduct(int $id, array $data): ?Product
    {
        try {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                return null;
            }

            return $this->productRepository->update($id, $data);
        } catch (Exception $e) {
            throw new Exception('Failed to update product: ' . $e->getMessage());
        }
    }

    public function deleteProduct(int $id): bool
    {
        try {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                return false;
            }

            return $this->productRepository->delete($id);
        } catch (Exception $e) {
            throw new Exception('Failed to delete product: ' . $e->getMessage());
        }
    }
}
