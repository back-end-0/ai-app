<?php

namespace App\Ai\Agents;

use App\Models\Product;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

class ChatBot implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $products = Product::query()
            ->select('id', 'name', 'description', 'price', 'quantity')
            ->get()
            ->toJson();

        return 'You are a helpful AI shopping assistant. You have access to the following product catalog: '
            .$products
            ."\n\nUse this data to answer questions about products including price, availability, and comparisons. "
            .'If a product has no description (null), you can suggest a marketing description when asked. '
            .'Always present product information in a clear and organized way.';
    }
}
