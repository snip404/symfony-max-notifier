<?php

namespace Symfony\Component\Notifier\Bridge\Max\Markup;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 */
abstract class AbstractMaxMarkup
{
    protected array $options = [];

    public function toArray(): array
    {
        return $this->options;
    }
}
