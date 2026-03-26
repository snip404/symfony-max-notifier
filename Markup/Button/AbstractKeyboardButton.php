<?php

namespace Symfony\Component\Notifier\Bridge\Max\Markup\Button;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 */
abstract class AbstractKeyboardButton
{
    protected array $options = [];

    public function toArray(): array
    {
        return $this->options;
    }
}
