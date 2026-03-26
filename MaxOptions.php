<?php

namespace Symfony\Component\Notifier\Bridge\Max;

use Symfony\Component\Notifier\Bridge\Max\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

/**
 * @author Alexei Cernelevski <snip404@gmail.com>
 */
final class MaxOptions implements MessageOptionsInterface
{
    public const PARSE_MODE_HTML = 'html';
    public const PARSE_MODE_MARKDOWN = 'markdown';

    public function __construct(
        private array $options = [
            'recipient' => 'chat_id',
        ],
    ) {
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function getRecipientId(): ?string
    {
        return $this->options[$this->options['recipient']] ?? null;
    }

    public function toChat(): static
    {
        $this->options['recipient'] = 'chat_id';

        return $this;
    }

    public function toUser(): static
    {
        $this->options['recipient'] = 'user_id';

        return $this;
    }

    /**
     * @return $this
     */
    public function chatId(string $id): static
    {
        $this->options['chat_id'] = $id;

        return $this;
    }

    /**
     * @return $this
     */
    public function userId(string $id): static
    {
        $this->options['user_id'] = $id;

        return $this;
    }

    /**
     * @return $this
     */
    public function parseMode(string $mode): static
    {
        $this->options['format'] = $mode;

        return $this;
    }


    /**
     * @return $this
     */
    public function notify(bool $bool): static
    {
        $this->options['notify'] = $bool;

        return $this;
    }

    /**
     * @return $this
     */
    public function image(string $url): static
    {
        $this->options['image'] = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function uploadImages(array $paths): static
    {
        $this->options['upload']['image'] = $paths;

        return $this;
    }

    /**
     * @return $this
     */
    public function uploadImage(string $path): static
    {
        $this->options['upload']['image'] = [$path];

        return $this;
    }

    /**
     * @return $this
     */
    public function edit(string $messageId): static
    {
        $this->options['message_id'] = $messageId;

        return $this;
    }

    /**
     * @return $this
     */
    public function answerCallbackQuery(string $callbackId, ?string $notification = null, int $cacheTime = 0): static
    {
        $this->options['callback_id'] = $callbackId;
        $this->options['notification'] = $notification;

        if ($cacheTime > 0) {
            $this->options['cache_time'] = $cacheTime;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function location(float $latitude, float $longitude): static
    {
        $this->options['location'] = ['latitude' => $latitude, 'longitude' => $longitude];

        return $this;
    }

    /**
     * @return $this
     */
    public function uploadFile(string $path): static
    {
        $this->options['upload']['file'] = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function uploadVideo(string $path): static
    {
        $this->options['upload']['video'] = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function uploadAudio(string $path): static
    {
        $this->options['upload']['audio'] = $path;

        return $this;
    }

    /**
     * @return $this
     */
    public function sticker(string $code): static
    {
        $this->options['sticker'] = $code;

        return $this;
    }

    /**
     * @return $this
     */
    public function link(string $type, string $mid): static
    {
        $this->options['link'] = [
            'type' => $type,
            'mid' => $mid,
        ];

        return $this;
    }



    /**
     * @return $this
     */
    public function contact(?string $name, ?int $contactId = null, ?string $vcfInfo = null, ?string $vcfPhone = null): static
    {
        $this->options['contact'] = [
            'name' => $name,
        ];

        if (null !== $contactId) {
            $this->options['contact']['contact_id'] = $contactId;
        }

        if (null !== $vcfInfo) {
            $this->options['contact']['vcf_info'] = $vcfInfo;
        }

        if (null !== $vcfPhone) {
            $this->options['contact']['vcf_phone'] = $vcfPhone;
        }

        return $this;
    }

    public function keyboard(InlineKeyboardMarkup $keyboard): static
    {
        $this->options['keyboard'] = $keyboard;

        return $this;
    }
}
