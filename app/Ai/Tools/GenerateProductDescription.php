<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GenerateProductDescription implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Generate and save a description for a product that has no description. Takes the product ID and generates a marketing description based on the product name and price.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $product = Product::find($request['product_id']);

        if (! $product) {
            return 'Product not found with ID: '.$request['product_id'];
        }

        if ($product->description) {
            return "Product '{$product->name}' already has a description: {$product->description}";
        }

        $description = $request['description'];
        $product->update(['description' => $description]);

        return "Description saved for '{$product->name}': {$description}";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->integer()->description('The ID of the product to generate a description for')->required(),
            'description' => $schema->string()->description('The generated marketing description for the product')->required(),
        ];
    }
}
