# 💬 Headless Chat for Laravel

A flexible, customizable and headless package designed to integrate chat functionality into Laravel applications.

# Why Headless Chat?

 - Models and database tables can be overridden 
 - Uses Actions resolved from Service Container

# Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Send a direct message](#send-a-direct-message)
  - [Reply to a message](#reply-to-a-message)
  - [Mark message as read](#mark-message-as-read)
  - [Delete a sent message](#delete-a-sent-message)
  - [Get conversations](#get-conversations)
  - [Get conversations with metrics](#get-conversations-with-metrics)
  - [Get unread conversation count](#get-unread-conversation-count)
  - [Group Messages](#group-messages) 
- [Advanced Usage](#advanced-usage)
  - [Override Models](#override-models)
  - [Override Actions](#override-actions)

# Installation

1. Install the package via composer.

    ```
    composer require tmsperera/headless-chat
    ```
   > ℹ️ Package will automatically register itself.

2. Publish migrations and config using:

    ```
    php artisan vendor:publish --tag=headless-chat
    ```
   
   > ℹ️ To publish only migrations:
   > ```
   > php artisan vendor:publish --tag=headless-chat-migrations
   > ```

   > ℹ️ To publish only configurations:
   > ```
   > php artisan vendor:publish --tag=headless-chat-config
   > ```

4. Run migrations.

    ```
    php artisan migrate
    ```

5. Implement your "User" Model from [Participant]([Chatable](/package/src/Contracts/Participant.php)) contract.

    ```php
    use TMSPerera\HeadlessChat\Contracts\Participant;
    
    class User extends Model implements Participant
    {
        ...
    }
    ```
   > ℹ️ Any Eloquent Model can be used as a Participant

5. Use [Chatable](/package/src/Traits/Chatable.php) trait in User Model.

    ```php
    use TMSPerera\HeadlessChat\Contracts\Participant;
    use TMSPerera\HeadlessChat\Traits\Chatable;
    
    class User extends Model implements Participant
    {
        use Chatable;
        ...
    }
    ```

# Usage

Having [Chatable](/package/src/Traits/Chatable.php) trait inside the User model gives you important abilities. And also this package provides standalone [Actions](package/src/Actions) to use anywhere your application needs.

🏗️ Feel free to refer the following component to have a better understanding

- [Participant](/package/src/Contracts/Participant.php) Contract
- [Chatable](/package/src/Traits/Chatable.php) Trait
- [HeadlessChat](/package/src/HeadlessChat.php) Class
- [Actions/](package/src/Actions) Directory

## Send a direct message

Sends a message using the chat package.

### Example:

```php
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;

$sender = User::query()->find(1);
$recipient = User::query()->find(2);

$message = $sender->createDirectMessage(
    recipient: $recipient, 
    messageDto: new MessageDto(
        type: 'text',
        content: 'Hello!',
        metadata: [ 'foo' => 'bar' ],
    ), 
);
```

### Signature:

```php
public function createDirectMessage(
    Participant $recipient,
    MessageDto $messageDto,
): Message;
```

## Reply to a message

Headless Chat also supports message replies.

### Example:

```php
use TMSPerera\HeadlessChat\DataTransferObjects\MessageDto;

$sender = User::query()->find(1);
$message = $sender->conversations->messages->first();

$sender->createReplyMessage(
    parentMessage: $message,
    messageDto: new MessageDto(
        type: 'text',
        content: 'Reply Message',
        metadata: [ 'foo' => 'bar' ],
    ), 
);
```

### Signature:

```php
public function createReplyMessage(
    Message $parentMessage,
    MessageDto $messageDto,
): Message;
```

## Mark message as read

Marks a message as read.

### Example:

```php
$sender = User::query()->find(1);
$message = $sender->conversations->messages->first();
$recipient = User::query()->find(2);

$recipient->readMessage($message);
```

### Signature:

```php
public function readMessage(Message $message): ReadReceipt;
```

## Delete a sent message

Delete a message sent by a Participant.

### Example:

```php
$sender = User::query()->find(1);
$message = $sender->conversations->messages->first();

$sender->deleteSentMessage($message);
```

### Signature:

```php
public function deleteSentMessage(Message $message): void;
```

## Get conversations

Conversations for a particular Participant can be accessed by an Eloquent Relationship.

### Example:

```php
$sender = User::query()->find(1);

$sender->conversations;
```

### Signature:

```php
public function conversations(): BelongsToMany;
```

## Get conversations with metrics

Just as conversations, you also can retrieve conversations with useful metrics using `conversationsWithMetrics` relation. 

### Example:

```php
$sender = User::query()->find(1);

$sender->conversationsWithMetrics;
```

### Signature:

```php
public function conversationsWithMetrics(): BelongsToMany;
```

### Returns

`conversationsWithMetrics` will return a collection of Conversations with each Conversation including special attributes which are...

 - `total_message_count`: Total messages for Conversation
 - `read_message_count`: Total **read** messages for Conversation
 - `unread_message_count`: Total **unread** messages for Conversation
 - `latest_message_at`: Created at of the latest message for Conversation

## Get unread conversation count

Returns count of unread conversations for a Participant.

### Example:

```php
$sender = User::query()->find(1);

$sender->getUnreadConversationCount();
```

### Signature:

```php
public function getUnreadConversationCount(): int;
```

## Group Messages

Headless Chat package is designed to support group chats. Here is how to create group chat...

1. Create a group conversation

```php
use TMSPerera\HeadlessChat\HeadlessChatActions;

$user1 = User::query()->find(1);
$user2 = User::query()->find(1);

$conversation = HeadlessChatActions::make()->createConversationAction->handle(
    participants: [$user1, $user2],
    conversationDto: new ConversationDTO(
        conversationType: ConversationType::GROUP,
    ),
);
```

2. Join a group conversation

```php
use TMSPerera\HeadlessChat\HeadlessChatActions;

$user3 = User::query()->find(3);
$conversation = Conversation::query()->find(1);

$participation = $user3->joinConversation($conversation);
```

# Advanced Usage

## Override Models

Some applications may not be able to use the default database tables provided by Headless Chat package. In such cases you can swap the database tables or even models to be used by this package. 

To swap a database table or model used in package follow the below steps:

1. Publish Headless Chat configurations using.

    ```
    php artisan vendor:publish --tag=headless-chat-config
    ```

2. Modify the published migrations in `create_headless_chat_tables.php` to set custom database table name and foreign key constrains.

    ```php
    // Schema::create('messages', function (Blueprint $table) {
    Schema::create('custom_messages', function (Blueprint $table) {
        ...
    });
   
    Schema::create('read_receipts', function (Blueprint $table) {
       ...
       $table->foreignId('message_id')
          // ->constrained(table: 'messages', column: 'id');
          ->constrained(table: 'custom_messages', column: 'id');
       ...
    });
    ```

3. Create new custom Model extending from the [Models](/package/src/Models) defined in Headless Chat package.

    ```php
    use TMSPerera\HeadlessChat\Models\Message;
    
    class CustomMessage extends Message
    {
        protected $table = 'custom_messages';
    }
    ```

   > ⚠️ You need to keep relationship names same as in Headless Chat Models in order to function the package. However, the referenced column names of relationships can be modified as per your custom database table columns.

4. Modify `config/headless-chat.php` to point the new model.

    ```php
    return [
        'models' => [
            'message' => App\Models\CustomMessage::class,
            ...
        ],
        ...
    ];
    ```

💡 Ultimately, the models are resolved from Laravel Service Container, so you can also override the Model class inside the `register` method of your `AppServiceProvider` instead of modifying or even publishing the `headless-chat` config just as below.

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\CustomMessage;
use TMSPerera\HeadlessChat\Models\Message;

class AppServiceProvider extends ServiceProvider
{
      public function register(): void
      {
          $this->app->bind(Message::class, function ($app) {
              return $app->make(CustomMessage::class);
          });
      }
}
```

## Override Actions

All actions used inside Headless Chat package are resolved from Laravel Service Container. If you ever need to modify the behaviour of any Action used in Headless Chat package, you can add a binding inside `register` method of your `AppServiceProvider`.

```php
namespace App\Providers;

use App\Actions\CustomSendDirectMessageAction;
use Illuminate\Support\ServiceProvider;
use TMSPerera\HeadlessChat\Actions\CreateDirectMessageAction;

class AppServiceProvider extends ServiceProvider
{
      public function register(): void
      {
          $this->app->bind(CreateDirectMessageAction::class, function ($app) {
              return $app->make(CustomSendDirectMessageAction::class);
          });
      }
}
```

# References

* [Headless Chat - Packagist](https://packagist.org/packages/tmsperera/headless-chat)