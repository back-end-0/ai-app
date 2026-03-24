<?php

namespace App\Ai\Agents;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ProductSearcher implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $products = Product::query()
            ->select('id', 'name', 'price', 'quantity')
            ->with('arabicTranslation:id,product_id,name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'name_ar' => $p->arabicTranslation?->name,
                'price' => $p->price,
                'quantity' => $p->quantity,
            ])
            ->toJson();

        return 'You are a product search assistant. Given a user search query (in English or Arabic), return the IDs of matching products from this catalog: '
            .$products
            ."\n\nMatch products by name (English or Arabic), category, price range, or any semantic meaning. "
            .'For example: "cheap" means low price, "headphones" matches audio products, "out of stock" means quantity=0, "محول" matches Arabic names. '
            .'Return up to 10 matching product IDs, ordered by relevance. If nothing matches, return an empty array.';
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array(
                $schema->integer()
            )->description('Array of matching product IDs, ordered by relevance')->required(),
        ];
    }
}
