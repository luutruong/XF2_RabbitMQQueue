# XF2_RabbitMQQueue
RabbitMQ and XenForo together. Whenever new job queued it's push to RabbitMQ queue.

You need put these config to your `config.php`

```php

$config['RabbitMQQueue'] = [
    'user' => 'guest',
    'password' => 'guest',
    'apiBase' => 'http://localhost:15672/api/exchanges',
    'exchange' => 'example_exchange',
    'routingKey' => 'example_routing',
    'deferredUrl' => 'http://yourdomain.com/job.php',
];

```

## Requirements
- XenForo 2.2.x
