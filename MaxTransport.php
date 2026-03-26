<?php

namespace Symfony\Component\Notifier\Bridge\Max;

use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MaxTransport extends AbstractTransport
{
    protected const HOST = 'platform-api.max.ru';

    public function __construct(
        #[\SensitiveParameter] private string $token,
        private ?string                       $chatChannel = null,
        ?HttpClientInterface                  $client = null,
        ?EventDispatcherInterface             $dispatcher = null,
    )
    {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        if (null === $this->chatChannel) {
            return \sprintf('max://%s', $this->getEndpoint());
        }

        return \sprintf('max://%s?channel=%s', $this->getEndpoint(), $this->chatChannel);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage && (null === $message->getOptions() || $message->getOptions() instanceof MaxOptions);
    }

    /**
     * @see https://dev.max.ru/docs-api
     */
    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $options = $message->getOptions()?->toArray() ?? [];
        $options['chat_id'] ??= $message->getRecipientId() ?: $this->chatChannel;
        $text = $message->getSubject();
        if (!isset($options['format']) || MaxOptions::PARSE_MODE_MARKDOWN === $options['format']) {
            $options['format'] = MaxOptions::PARSE_MODE_MARKDOWN;
            $text = preg_replace('/([.!#>+-=|{}~])/', '\\\\$1', $text);
        }

        $options['text'] = $text;
//        $this->ensureExclusiveOptionsNotDuplicated($options);
//        $options = $this->expandOptions($options, 'contact', 'location', 'venue');

        [$method, $url, $options] = $this->prepareRequest($options);

        $response = $this->client->request($method, $url, [
            'json' => $options,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->token,
            ]
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote MAX server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            $result = $response->toArray(false);

            throw new TransportException('Unable to send the MAX message: ' . $result['description'] . \sprintf(' (code %d).', $result['error_code']), $response);
        }

        $success = $response->toArray(false);

        $sentMessage = new SentMessage($message, (string)$this);
        if (isset($success['message']['body']['mid'])) {
            $sentMessage->setMessageId($success['message']['body']['mid']);
        }

        return $sentMessage;
    }

    private function prepareRequest(array $options): array
    {
        $method = 'POST';

        if (isset($options['callback_id'])) {
            // callback answer https://dev.max.ru/docs-api/methods/POST/answers
            $path = "answers?callback_id={$options['callback_id']}";
            unset($options['callback_id']);

        } elseif (isset($options['message_id'])) {
            // edit message https://dev.max.ru/docs-api/methods/PUT/messages
            $method = 'PUT';
            $path = "messages?message_id={$options['message_id']}";
            unset($options['message_id']);
        } else {
            // send message https://dev.max.ru/docs-api/methods/POST/messages
            $path = "messages?{$options['recipient']}={$options['chat_id']}";
        }

        $url = \sprintf('https://%s/%s', $this->getEndpoint(), $path);

        $this->prepareAttachments($options);
        $this->prepareKeyboard($options);

        return [$method, $url, $options];
    }

    private function prepareAttachments(array &$options): void
    {
        $options['attachments'] = [];
        if (isset($options['location'])) {
            $options['attachments'][] = [
                'type' => 'location',
                ...$options['location']
            ];
            unset($options['location']);
        }

        if (isset($options['image'])) {
            $options['attachments'][] = [
                'type' => 'image',
                'payload' => [
                    'url' => $options['image']
                ]
            ];
            unset($options['image']);
        }

        if (isset($options['contact'])) {
            $options['attachments'][] = [
                'type' => 'contact',
                'payload' => $options['contact']
            ];
            unset($options['contact']);
        }

        if (isset($options['upload'])) {
            foreach ($options['upload'] as $type => $uploads) {
                if (!\is_array($uploads)) {
                    $uploads = [$uploads];
                }
                foreach ($uploads as $upload) {
                    $response = $this->uploadFile($type, $upload);
                    $options['attachments'][] = [
                        'type' => $type,
                        'payload' => $response
                    ];
                }
            }
            unset($options['upload']);
        }
    }

    private function prepareKeyboard(array &$options): void
    {
        if (!isset($options['attachments'])) {
            $options['attachments'] = [];
        }

        if (isset($options['keyboard'])) {
            $options['attachments'][] = [
                'type' => 'inline_keyboard',
                'payload' => [
                    'buttons' => $options['keyboard']->toArray()
                ]
            ];
            unset($options['keyboard']);
        }
    }

    private function uploadFile(string $type, string $path): mixed
    {
        $formFields = [
            'data' => DataPart::fromPath($path)
        ];
        $formData = new FormDataPart($formFields);
        $url = \sprintf('https://%s/uploads?type=%s', $this->getEndpoint(), $type);
        $headers = ["Authorization: $this->token"];
        $response = $this->client->request('POST', $url, [
            'headers' => $headers,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->toArray(false);
        if (200 !== $statusCode || !isset($content['url'])) {
            throw new TransportException('Unable to get MAX upload url: '. \sprintf(' (code %d).', $statusCode), $response);
        }

        $uploadUrl = $content['url'];


        $headers = $formData->getPreparedHeaders()->toArray();
        $headers[] = "Authorization: $this->token";

        $response = $this->client->request('POST', $uploadUrl, [
            'headers' => $headers,
            'body' => $formData->bodyToIterable(), // Get the body as an iterable stream
        ]);
        $statusCode = $response->getStatusCode();
        $content = $response->toArray(false);
        if (200 !== $statusCode) {
            throw new TransportException('Unable to upload MAX attachments: ' . \sprintf(' (code %d).', $statusCode), $response);
        }

        return $content;
    }
}
