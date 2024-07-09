<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Jobs\HandleOrderCompletionJob;
use App\Jobs\RetryPendingOrdersJob;
use Illuminate\Support\Facades\Log;

class ConsumeOrderCompletionMessages extends Command
{
    protected $signature = 'rabbitmq:consume-order-completion';
    protected $description = 'Consume messages from RabbitMQ order completion queue';

    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
    }

    public function handle()
    {
        $this->rabbitMQService->consumeMessages('order_completion_queue', [$this, 'processMessage']);
    }

    public function processMessage($msg)
    {
        $data = json_decode($msg->body, true);

        if (isset($data['order_id'])) {
            dispatch(new HandleOrderCompletionJob($data['order_id']));
        } else {
            Log::error('Order ID not found in message: ' . $msg->body);
        }
    }

}
