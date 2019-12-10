<?php

/**
 * Jield copyright message placeholder.
 *
 * @category   Application
 *
 * @author     Johan van der Heide <info@jield.nl>
 * @copyright  Copyright (c) 2004-2015 Jield (http://jield.nl)
 * @license    http://jield.nl/license.txt proprietary
 *
 * @link       http://jield.nl
 */

declare(strict_types=1);

namespace Calendar\ValueObject;

use function implode;
use function sprintf;

final class Link
{
    private string $href;
    private string $title;
    private array $classes;
    private array $linkContent;
    private ? string $javascript;

    public function __construct(string $href, string $title, array $classes, array $linkContent, ?string $javascript)
    {
        $this->href = $href;
        $this->title = $title;
        $this->classes = $classes;
        $this->javascript = $javascript;
        $this->linkContent = $linkContent;
    }

    public function __toString() : string
    {
        $uri = '<a href="%s" title="%s" class="%s" %s>%s</a>';

        return sprintf(
            $uri,
            $this->href,
            $this->title,
            implode(' ', $this->classes),
            $this->javascript,
            implode('', $this->linkContent)
        );
    }
}
