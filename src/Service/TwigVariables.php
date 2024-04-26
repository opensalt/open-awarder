<?php

declare(strict_types=1);

namespace App\Service;

use Twig\Environment;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Node\NodeCaptureInterface;

/*
 * Adapted from https://stackoverflow.com/a/74367172 https://stackoverflow.com/questions/12799094/how-to-retrieve-all-variables-from-a-twig-template
 */
readonly class TwigVariables
{
    public function __construct(private Environment $twig)
    {
    }

    protected function visit(Node $node, array &$variables): void
    {
        // @see https://github.com/twigphp/Twig/issues/2340 for details about NodeCaptureInterface
        if ($node instanceof NodeCaptureInterface) {
            return;
        }

        if ($node instanceof NameExpression
            && false === $node->getAttribute('always_defined') // ignore scoped names as (key, value) in for loop
        ) {
            $variables[$node->getAttribute('name')] = null;

            return;
        }

        foreach ($node as $child) {
            $this->visit($child, $variables);
        }
    }

    public function getVariables(string $twigTemplateCode): array
    {
        $source = $this->twig->createTemplate($twigTemplateCode, 'template')->getSourceContext();
        $tokens = $this->twig->tokenize($source);
        $nodes = $this->twig->parse($tokens);

        $variables = [];
        $this->visit($nodes, $variables);

        return $variables;
    }
}
