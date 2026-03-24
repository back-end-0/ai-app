<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class DescriptionGenerator implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a product copywriter. Generate a short marketing description (1-2 sentences) for the given product. '
            .'Return ONLY the description text, nothing else. No quotes, no prefix.';
    }
}
