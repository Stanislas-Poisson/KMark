<?php

declare(strict_types=1);

namespace KMark;

use InvalidArgumentException;

/**
 * The HTML element that writes a text style: its name and its CSS classes.
 *
 *     new Tag('span', ['bold']);   // <span class="bold">…</span>
 */
final readonly class Tag
{
    /**
     * @param list<string> $classes
     *
     * @throws InvalidArgumentException when the name or a class is not valid
     */
    public function __construct(public string $name, public array $classes = [])
    {
        if (1 !== preg_match('/^[a-z][a-z0-9]*$/', $name)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid element name.', $name));
        }

        foreach ($classes as $class) {
            if (1 !== preg_match('/^[\w-]+$/', $class)) {
                throw new InvalidArgumentException(sprintf('"%s" is not a valid class name.', $class));
            }
        }
    }

    /**
     * The replacement of a regular expression: the element around the first group.
     */
    public function wrap(): string
    {
        $classes = [] === $this->classes ? '' : ' class="' . implode(' ', $this->classes) . '"';

        return '<' . $this->name . $classes . '>$1</' . $this->name . '>';
    }
}
