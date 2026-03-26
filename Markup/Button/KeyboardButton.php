<?php

namespace Symfony\Component\Notifier\Bridge\Max\Markup\Button;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 *
 * @see https://dev.max.ru/docs-api
 */
final class KeyboardButton extends AbstractKeyboardButton
{
    public function __construct(string $text)
    {
        $this->options['text'] = $text;
        $this->options['type'] = 'message';
    }

    private function overrideOptions(string $type, array $options = []): void
    {
        $this->options = [
            'type' => $type,
            'text' => $this->options['text'],
            ...$options,
        ];
    }

    /**
     * @return $this
     */
    public function callback(string $payload): static
    {
        $this->overrideOptions('callback', ['payload' => $payload]);

        return $this;
    }

    /**
     * @return $this
     */
    public function link(string $url): static
    {
        $this->overrideOptions('link', ['url' => $url]);

        return $this;
    }

    /**
     * @return $this
     */
    public function requestGeoLocation(bool $quick = false): static
    {
        $this->overrideOptions('request_geo_location', ['quick' => $quick]);

        return $this;
    }

    /**
     * @return $this
     */
    public function requestContact(): static
    {
        $this->overrideOptions('request_contact');

        return $this;
    }

    /**
     * @return $this
     */
    public function openApp(?string $webApp = null, ?string $contactId = null, ?string $payload = null): static
    {
        $options = [];
        if ($webApp !== null) { $options['web_app'] = $webApp; }
        if ($contactId !== null) { $options['contact_id'] = $contactId; }
        if ($payload !== null) { $options['payload'] = $payload; }
        $this->overrideOptions('open_app', $options);

        return $this;
    }

    /**
     * @return $this
     */
    public function message(): static
    {
        $options = [];
        $this->overrideOptions('message', $options);

        return $this;
    }

    /**
     * @return $this
     */
    public function setText(string $text): static
    {
        $this->options['text'] = $text;

        return $this;
    }
}
