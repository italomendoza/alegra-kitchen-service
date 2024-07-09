<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;


class HandleOrderCompletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {

        try {

            // Resolver el servicio del contenedor
            $orderService = App::make(OrderService::class);

            $orderService->completeOrder($this->orderId);
        } catch (\Exception $e) {
            Log::error('Error handling order completion for order ' . $this->orderId . ': ' . $e->getMessage());
            throw $e;
        }
    }

}
