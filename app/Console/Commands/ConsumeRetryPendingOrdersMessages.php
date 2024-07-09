<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Jobs\RetryPendingOrdersJob;
use Illuminate\Support\Facades\Log;

class ConsumeRetryPendingOrdersMessages extends Command
{
    protected $signature = 'rabbitmq:consume-retry-pending-orders';
    protected $description = 'Consume messages from RabbitMQ retry pending orders queue';

    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
    }

    public function handle()
    {
        $this->rabbitMQService->consumeMessages('retry_pending_orders_queue', [$this, 'processMessage']);
    }

    public function processMessage($msg)
    {
        dispatch(new RetryPendingOrdersJob());
    }
}
