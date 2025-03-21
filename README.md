# 💬 Headless Chat for Laravel

A headless package designed to integrate chat functionality into Laravel applications.

## Key Features

 - Models and tables can be overridden 
 - Emits Events
 - Uses Actions resolved from Service Container

## Installation

1. Install the package via composer:

    ```
    composer require tmsperera/headless-chat:dev-main
    ```
    The package will automatically register itself.

2. Publish the migration with:

    ```
    php artisan vendor:publish --tag=headless-chat-migrations
    ```

3. Run migrations

    ```
    php artisan migrate
    ```

4. Implement your Use model from [Participant]([Chatable](/package/src/Contracts/Participant.php)) contract:

    ```php
    use TMSPerera\HeadlessChat\Contracts\Participant;
    
    class User extends Model implements Participant
    {
        ...
    }
    ```

5. Use [Chatable](/package/src/Traits/Chatable.php) trait in User model:

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

Having **Chatable** trait inside the User model gives you important abilities. And also this package provides standalone **Actions** to use anywhere your application needs.

🏗️ Feel free to refer following until the documentation gets completed

- [Participant](/package/src/Contracts/Participant.php) Contract
- [Chatable](/package/src/Traits/Chatable.php) Trait
- [Actions](package/src/Actions) Directory
- [Events](package/src/Events) Directory

## Advanced Usage

### Override Models

Some applications may not be able to use the default database tables provided by Headless Chat package. In such cases you can swap the database tables or even models to be used by this package. 

To swap a database table or model used in package follow the below steps:

1. Publish Headless Chat configurations using:

    ```
    php artisan vendor:publish --tag=headless-chat-config
    ```

2. Modify the published migrations in **create_headless_chat_tables** to set custom database table name.

    ```php
    // Schema::create('messages', function (Blueprint $table) {
    Schema::create('custom_messages', function (Blueprint $table) {
        ...
    });
    ```

3. Create new custom model extending from the [models](/package/src/Models) defined in Headless Chat package.

    ```php
    use TMSPerera\HeadlessChat\Models\Message;
    
    class CustomMessage extends Message
    {
        protected $table = 'custom_messages';
    }
    ```

4. Modify Headless Chat configurations to point the new model:

    ```php
    return [
        'models' => [
            'message' => App\Models\CustomMessage::class,
            ...
        ],
        ...
    ];
    ```

### Override Actions

All actions used inside Headless Chat package are resolved from Laravel Service Container. If you ever need to modify the behaviour of any Action used in Headless Chat package, you can add a binding inside your AppServiceProvider.

## References

* [Headless Chat - Packagist](https://packagist.org/packages/tmsperera/headless-chat)