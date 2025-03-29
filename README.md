# 💬 Headless Chat for Laravel

A flexible, customizable and headless package designed to integrate chat functionality into Laravel applications.

## Key Features

 - Emits Events
 - Models and database tables can be overridden 
 - Uses Actions resolved from Service Container

## Installation

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

## Usage

Having [Chatable](/package/src/Traits/Chatable.php) trait inside the User model gives you important abilities. And also this package provides standalone [Actions](package/src/Actions) to use anywhere your application needs.

🏗️ Feel free to refer the following until the documentation gets completed

- [Participant](/package/src/Contracts/Participant.php) Contract
- [Chatable](/package/src/Traits/Chatable.php) Trait
- [HeadlessChat](/package/src/HeadlessChat.php) Class
- [Actions/](package/src/Actions) Directory
- [Events/](package/src/Events) Directory

### Send Direct Message

```php
    $sender = User::query()->find(1);
    $recipient = User::query()->find(2);
    
    $sender->sendDirectMessage(recipient: $recipient, message: 'Hello World!');
```

> More details are coming soon...

## Advanced Usage

### Override Models

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

### Override Actions

All actions used inside Headless Chat package are resolved from Laravel Service Container. If you ever need to modify the behaviour of any Action used in Headless Chat package, you can add a binding inside `register` method of your `AppServiceProvider`.

```php
namespace App\Providers;

use App\Actions\CustomSendDirectMessageAction;
use Illuminate\Support\ServiceProvider;
use TMSPerera\HeadlessChat\Actions\SendDirectMessageAction;

class AppServiceProvider extends ServiceProvider
{
      public function register(): void
      {
          $this->app->bind(SendDirectMessageAction::class, function ($app) {
              return $app->make(CustomSendDirectMessageAction::class);
          });
      }
}
```

## References

* [Headless Chat - Packagist](https://packagist.org/packages/tmsperera/headless-chat)