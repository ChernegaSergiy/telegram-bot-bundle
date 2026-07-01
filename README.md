[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct.svg)](https://stand-with-ukraine.pp.ua)

# Telegram Bot Bundle

[![Latest Stable Version](https://img.shields.io/packagist/v/morfeditorial/telegram-bot-bundle.svg?label=Packagist&logo=packagist)](https://packagist.org/packages/morfeditorial/telegram-bot-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/morfeditorial/telegram-bot-bundle.svg?label=Downloads&logo=packagist)](https://packagist.org/packages/morfeditorial/telegram-bot-bundle)
[![License](https://img.shields.io/packagist/l/morfeditorial/telegram-bot-bundle.svg?label=Licence&logo=open-source-initiative)](https://packagist.org/packages/morfeditorial/telegram-bot-bundle)

A powerful, object-oriented, and highly extensible Telegram Bot integration bundle for Symfony applications. This bundle provides a robust architecture for building complex Telegram bots using state machines, modular screens, and dependency injection, moving away from monolithic switch-case handlers to clean, maintainable classes.

## Features

- **Object-Oriented Screen Architecture**: Build bots using individual Screen classes that handle specific states and actions.
- **State Management**: Built-in `UserStateService` to track user progression through complex multi-step flows (e.g., forms, wizards).
- **Standardized Payload Routing**: Automatically route `callback_query` data using a structured payload format (`domain:action:args`).
- **Seamless Symfony Integration**: Full support for Symfony Dependency Injection, autowiring, and event dispatching.
- **Support for Long Polling & Webhooks**: Flexible transport layer adapting to your deployment environment.
- **Asynchronous Ready**: Built with non-blocking architectures in mind (compatible with AsyncHttp and ReactPHP).

## Installation

To install the Telegram Bot Bundle, run the following command in your terminal:

```bash
composer require morfeditorial/telegram-bot-bundle
```

## Configuration

Add your Telegram Bot credentials to your `.env` file:

```dotenv
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=your_bot_username
```

If the bundle is not automatically registered, add it to `config/bundles.php`:

```php
return [
    // ...
    Morfeditorial\TelegramBotBundle\TelegramBotBundle::class => ['all' => true],
];
```

## Core Concepts

### 1. The Screen System (`AbstractScreen`)
Instead of one massive loop handling all updates, this bundle uses "Screens". A Screen is a dedicated class that answers two questions:
- **Can I handle this update?** &mdash; Implemented via `supports()`
- **How do I handle this update?** &mdash; Implemented via `handle()`

Screens are automatically discovered and registered by the bundle.

### 2. Payload Routing
The bundle encourages using a standard payload structure for inline keyboards: `domain:action:args`.
For example, `project:delete:42`. This allows your screens to precisely intercept events meant for them.

### 3. State Management
When asking a user for input (e.g., "Enter project name"), you assign a state to their ID. The corresponding screen will intercept their next text message based on this state.

## Usage

### Creating a Screen

Create a new class extending `AbstractScreen` (or your project's BaseScreen).

```php
namespace App\Screens\Project;

use Morfeditorial\TelegramBotBundle\Screens\AbstractScreen;

class ProjectViewScreen extends AbstractScreen
{
    public function supports(array $update): bool
    {
        $data = $update['callback_query']['data'] ?? '';
        
        // This screen intercepts any payload starting with 'project:view:'
        return str_starts_with($data, 'project:view:');
    }

    public function handle(array $update): void
    {
        $chatId = $update['callback_query']['message']['chat']['id'] ?? $update['message']['chat']['id'] ?? 0;
        $data = $update['callback_query']['data'] ?? '';
        
        // Extract arguments
        $parts = explode(':', $data);
        $projectId = $parts[2] ?? null;

        // Use the injected client to send a message
        $this->client->sendMessage([
            'chat_id' => $chatId,
            'text' => "Viewing project #" . $projectId,
        ]);
    }
}
```

### Handling Multi-Step Forms (State Machine)

To handle user input sequentially, use the `UserStateService`:

```php
namespace App\Screens\Project;

use Morfeditorial\TelegramBotBundle\Screens\AbstractScreen;
use Morfeditorial\TelegramBotBundle\Services\UserStateService;

class ProjectCreateScreen extends AbstractScreen
{
    private UserStateService $stateService;

    public function __construct(UserStateService $stateService)
    {
        $this->stateService = $stateService;
    }

    public function supports(array $update): bool
    {
        $userId = $update['message']['from']['id'] ?? 0;
        
        return ($update['callback_query']['data'] ?? '') === 'project:create' ||
               $this->stateService->getState($userId) === 'awaiting_project_title';
    }

    public function handle(array $update): void
    {
        $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? 0;
        $userId = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? 0;
        
        if (isset($update['callback_query'])) {
            // Step 1: Start creation
            $this->stateService->setState($userId, 'awaiting_project_title');
            $this->client->sendMessage(['chat_id' => $chatId, 'text' => "Please enter the project title:"]);
            return;
        }

        // Step 2: Receive input
        if ($this->stateService->getState($userId) === 'awaiting_project_title') {
            $title = $update['message']['text'];
            // Save to DB...
            
            $this->stateService->clearState($userId);
            $this->client->sendMessage(['chat_id' => $chatId, 'text' => "Project '$title' created!"]);
        }
    }
}
```

## Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## License

This library is licensed under the CSSM Unlimited License v2.0 (CSSM-ULv2). See the [LICENSE](LICENSE) file for details.
