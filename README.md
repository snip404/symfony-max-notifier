Max Notifier
=================

Provides [MAX](https://max.ru) integration for Symfony Notifier.

DSN example
-----------

```
MAX_DSN=max://TOKEN@default
```

where:
 - `TOKEN` is your MAX token

Adding Interactions to a Message
--------------------------------

With a Max message, you can use the `MaxOptions` class to add
[message options](https://dev.max.ru/docs-api).

```php
use Symfony\Component\Notifier\Bridge\Max\Markup\Button\KeyboardButton;
use Symfony\Component\Notifier\Bridge\Max\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('');

// Create MAX options
$maxOptions = (new MaxOptions())
    ->chatId('222222')
    ->parseMode('markdown')
    ->keyboard((new InlineKeyboardMarkup())
        ->addButton((new KeyboardButton("Visit symfony.com"))->link("https://symfony.com"))
        )
    );

// Add the custom options to the chat message and send the message
$chatMessage->options($maxOptions);

$chatter->send($chatMessage);
```

Adding files to a Message
-------------------------

With a Max message, you can use the `MaxOptions` class to add
[message options](https://dev.max.ru/docs-api).

> :warning: **WARNING**
In one message you can send only one file

 * You can send files by passing public http url to option:
   * Photo
     ```php
     $maxOptions = (new MaxOptions())
          ->image('https://localhost/photo.mp4');
     ```

* You can send files by passing local path to option, in this case file will be sent via multipart/form-data:
   * Image
     ```php
     $maxOptions = (new MaxOptions())
          ->uploadImage('./photo.mp4');
     ```
     * Images
     ```php
     $maxOptions = (new MaxOptions())
          ->uploadImages(['./photo.mp4']);
     ```
   * Video
     ```php
     $maxOptions = (new MaxOptions())
          ->uploadVideo('./video.mp4');
     ```
     ```
   * Audio
     ```php
     $maxOptions = (new MaxOptions())
          ->uploadAudio('./audio.ogg');
     ```
   * File
     ```php
     $maxOptions = (new MaxOptions())
          ->document('./document.odt');
     ```


Full example:
```php
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('Photo Caption');

// Create MAX options
$maxOptions = (new MaxOptions())
    ->chatId('1111111')
    ->parseMode('html')
    ->image('https://symfony.com/favicons/android-chrome-192x192.png');

// Add the custom options to the chat message and send the message
$chatMessage->options($maxOptions);

$chatter->send($chatMessage);
```

Adding Location to a Message
----------------------------

With a MAX message, you can use the `MaxOptions` class to add
[message options](https://dev.max.ru/docs-api).

```php
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('');

// Create Max options
$maxOptions = (new MaxOptions())
    ->chatId('111111')
    ->parseMode('html')
    ->location(48.8566, 2.3522);

// Add the custom options to the chat message and send the message
$chatMessage->options($maxOptions);

$chatter->send($chatMessage);
```

Adding Contact to a Message
----------------------------

With a Max message, you can use the `MaxOptions` class to add
[message options](https://dev.max.ru/docs-api).

```php
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('');
// Create MAX options
$maxOptions = (new MaxOptions())
    ->chatId('111111')
    ->parseMode('html');

// Via vCard 
$vCard = 'BEGIN:VCARD
VERSION:3.0
N:Doe;John;;;
FN:John Doe
EMAIL;type=INTERNET;type=WORK;type=pref:johnDoe@example.org
TEL;type=WORK;type=pref:+330186657200
END:VCARD';
$maxOptions->contact(null, vcfInfo: $vCard);
    
// Via contactId (userId from MAX) 
$maxOptions->contact(null, contactId: 222222);

// Add the custom options to the chat message and send the message
$chatMessage->options($maxOptions);

$chatter->send($chatMessage);
```

Updating Messages
-----------------
When working with interactive callback buttons, you can use the `MaxOptions`
to reference a previous message to edit.

```php
use Symfony\Component\Notifier\Bridge\Max\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Max\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('Are you really sure?');

$maxOptions = (new MaxOptions())
    ->chatId($chatId)
    ->edit($messageId) // extracted from callback payload or SentMessage
    ->keyboard((new InlineKeyboardMarkup())
        ->addButton((new KeyboardButton("Absolutely"))->callback("yes"))
        ->addRow()
        ->addButton((new KeyboardButton("open symfony"))->link("https://symfony.com"))
    );
```

Answering Callback Queries
--------------------------

The `MaxOptions::answerCallbackQuery()` method was introduced in Symfony 6.3.

When sending message with inline keyboard buttons with callback data, you can use
`MaxOptions` to [answer callback queries](https://dev.max.ru/docs-api/methods/POST/answers).

```php
use Symfony\Component\Notifier\Bridge\Max\MaxOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

$chatMessage = new ChatMessage('Thank you!');
$maxOptions = (new MaxOptions())
    ->chatId($chatId)
    ->answerCallbackQuery(
        callbackQueryId: '12345', // extracted from callback
        notification: true
    );
```