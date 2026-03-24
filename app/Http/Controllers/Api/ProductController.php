<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\DescriptionGenerator;
use App\Ai\Agents\ProductSearcher;
use App\Ai\Agents\ProductTranslator;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with pagination and Arabic translations.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->select('id', 'name', 'description', 'price', 'quantity')
            ->with('arabicTranslation:id,product_id,locale,name,description')
            ->paginate($request->integer('per_page', 9));

        return response()->json($products);
    }

    /**
     * AI-powered product search — the agent already knows all products.
     * One AI call to get IDs → one whereIn query to fetch full data.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'max:500']]);

        $query = (string) $request->string('q')->trim();

        try {
            $agent = new ProductSearcher;
            $ids = $agent->prompt($query)['ids'] ?? [];

            if (empty($ids)) {
                return response()->json(['data' => [], 'query' => $query]);
            }

            $products = Product::query()
                ->select('id', 'name', 'description', 'price', 'quantity')
                ->with('arabicTranslation:id,product_id,locale,name,description')
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn ($p) => array_search($p->id, $ids))
                ->values();

            return response()->json(['data' => $products, 'query' => $query]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Search error: '.$e->getMessage()], 503);
        }
    }

    /**
     * Generate and save AI description for a product.
     */
    public function generateDescription(Product $product): JsonResponse
    {
        if ($product->description) {
            return response()->json([
                'data' => [
                    'product_id' => $product->id,
                    'description' => $product->description,
                    'generated' => false,
                    'message' => 'Product already has a description.',
                ],
            ]);
        }

        try {
            $agent = new DescriptionGenerator;
            $response = $agent->prompt("Product: {$product->name}, Price: \${$product->price}");
            $description = trim((string) $response);

            $product->update(['description' => $description]);

            return response()->json([
                'data' => [
                    'product_id' => $product->id,
                    'description' => $description,
                    'generated' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'AI error: '.$e->getMessage()], 503);
        }
    }

    /**
     * Translate a product's name and description to Arabic using AI.
     */
    public function translate(Product $product): JsonResponse
    {
        $existing = $product->arabicTranslation()->first();

        if ($existing) {
            return response()->json([
                'data' => [
                    'product_id' => $product->id,
                    'name' => $existing->name,
                    'description' => $existing->description,
                    'generated' => false,
                    'message' => 'Translation already exists.',
                ],
            ]);
        }

        $descriptionText = $product->description ?? 'No description available';

        try {
            $agent = new ProductTranslator;
            $response = $agent->prompt(
                "Product name: {$product->name}\nProduct description: {$descriptionText}"
            );

            $translatedName = $response['name'];
            $translatedDescription = $response['description'];

            $product->translations()->create([
                'locale' => 'ar',
                'name' => $translatedName,
                'description' => $translatedDescription,
            ]);

            return response()->json([
                'data' => [
                    'product_id' => $product->id,
                    'name' => $translatedName,
                    'description' => $translatedDescription,
                    'generated' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'AI error: '.$e->getMessage()], 503);
        }
    }
}
