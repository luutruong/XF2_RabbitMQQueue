<?php

namespace Truonglv\RabbitMQQueue\XF\Job;

class Manager extends XFCP_Manager
{
    /**
     * @inheritDoc
     */
    public function updateNextRunTime()
    {
        $runTime = $this->getFirstAutomaticTime();
        $this->_publishRabbitMQ($runTime);

        return parent::updateNextRunTime();
    }

    protected function _publishRabbitMQ(int $runTime): void
    {
        if ($runTime <= 0) {
            return;
        }

        $config = array_replace_recursive([
            'user' => '',
            'password' => '',
            'apiBase' => '',
            'vhost' => '%2F',
            'exchange' => '',
            'routingKey' => '',
            'deferredUrl' => '',
        ], (array) \XF::app()->config('RabbitMQQueue'));

        foreach ($config as $value) {
            if (trim($value) === '') {
                return;
            }
        }

        $apiUrl = sprintf(
            '%s/%s/%s/publish',
            $config['apiBase'],
            $config['vhost'],
            $config['exchange']
        );

        $client = \XF::app()->http()->client();

        try {
            $client->post($apiUrl, [
                'auth' => [
                    $config['user'],
                    $config['password'],
                ],
                'json' => [
                    'routing_key' => $config['routingKey'],
                    'payload' => $config['deferredUrl'],
                    'payload_encoding' => 'string',
                    'properties' => [
                        'headers' => [
                            'x-delay' => max(0, ($runTime - time()) * 1000)
                        ]
                    ]
                ],
                'connect_timeout' => 3,
                'timeout' => 3,
            ]);
        } catch (\Throwable $e) {
            if (\XF::$debugMode) {
                throw $e;
            }
            \XF::logException($e, false, '[tl] RabbitMQ Queue: ');
        }
    }
}
