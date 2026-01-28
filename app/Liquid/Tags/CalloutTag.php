<?php

namespace App\Liquid\Tags;

use Liquid\AbstractBlock;
use Liquid\Context;

class CalloutTag extends AbstractBlock
{
    protected $type = 'info';

    // The markup looks like: type="warning"
    public function __construct($markup, $tokens, $fileSystem = null)
    {
        // Simple manual parsing of markup
        if (preg_match('/type=["\'](.*?)["\']/', $markup, $matches)) {
            $this->type = $matches[1];
        } else {
            // Fallback if user just wrote {% callout "warning" %}
            $markup = trim($markup);
            if (!empty($markup)) {
                $this->type = trim($markup, '"\'');
            }
        }

        parent::__construct($markup, $tokens, $fileSystem);
    }

    public function render(Context $context)
    {
        $content = parent::render($context);

        $colors = [
            'info' => 'bg-blue-100 border-blue-500 text-blue-700',
            'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
            'danger' => 'bg-red-100 border-red-500 text-red-700',
            'success' => 'bg-green-100 border-green-500 text-green-700',
        ];

        $class = $colors[$this->type] ?? $colors['info'];

        return "<div class='border-l-4 p-4 mb-4 {$class}' role='alert'>
            <p>{$content}</p>
        </div>";
    }
}
