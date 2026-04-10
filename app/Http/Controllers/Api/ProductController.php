<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Throwable;
use Exception;

#[OA\Tag(name: 'Products')]
class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    #[OA\Get(
        path: '/api/products',
        summary: 'Get all products',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        responses: [
            new OA\Response(response: 200, description: 'Product list'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function index(): JsonResponse
    {
        try {
            $products = $this->productRepository->getAll();

            return response()->json([
                'status' => true,
                'message' => 'Products fetched successfully',
                'data' => $products,
            ], 200);
        } catch (Exception|Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching products.',
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Create product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Laptop'),
                    new OA\Property(property: 'description', type: 'string', example: 'Core i7 laptop'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 1200),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product created'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productRepository->create($request->validated());

            if (! $product) {
                throw new Exception('Product creation failed.');
            }

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (Exception|Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while creating the product.',
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Show product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product details'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Product fetched successfully',
                'data' => $product,
            ], 200);
        } catch (Exception|Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching the product.',
            ], 500);
        }
    }

    #[OA\Put(
        path: '/api/products/{id}',
        summary: 'Update product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Laptop'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 1400),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function update(ProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $updatedProduct = $this->productRepository->update($id, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct,
            ], 200);
        } catch (Exception|Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while updating the product.',
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Delete product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted'),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->findById($id);

            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $deleted = $this->productRepository->delete($id);

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (Exception|Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while deleting the product.',
            ], 500);
        }
    }
}
