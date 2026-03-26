<?php

namespace Symfony\Component\Notifier\Bridge\Max\Markup;

use Symfony\Component\Notifier\Bridge\Max\Markup\Button\AbstractKeyboardButton;
use Symfony\Component\Notifier\Bridge\Max\Markup\Button\KeyboardButton;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 *
 * @see https://dev.max.ru/docs-api/methods/POST/messages (payload.type=inline_keyboard)
 */
final class InlineKeyboardMarkup extends AbstractMaxMarkup
{
    private ?int $index = null;

    public function __construct()
    {
        $this->options['inline_keyboard'] = [];
    }

    /**
     * @return $this
     */
    public function addRow(): static
    {
        if ($this->index === null) {
            $this->index = 0;
        } else {
            $this->index++;
        }

        $this->options['inline_keyboard'][] = [];


        return $this;
    }

    /**
     * @param int $i
     * @return $this
     */
    public function setIndex(int $i): static
    {
        $this->index = $i;

        return $this;
    }

    public function addButton(AbstractKeyboardButton $button): static
    {
        if ($this->index === null) {
            $this->addRow();
        }

        $this->options['inline_keyboard'][$this->index][] = $button;

        return $this;
    }

    public function toArray(): array
    {
        $arr = [];
        foreach ($this->options['inline_keyboard'] as $r) {
            $ra = [];
            foreach ($r as $button) {
                $ra[] = $button->toArray();
            }
            $arr[] = $ra;
        }

        return $arr;
    }
}
