<?php

namespace App\Jobs;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductQuantityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private OrderItem $orderItem;
    private string $transactionType;

    /**
     * Create a new job instance.
     */
    public function __construct(OrderItem $orderItem, string $transactionType)
    {
        $this->orderItem = $orderItem;
        $this->transactionType = $transactionType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $product = $this->orderItem->product;
        $processing  = $product->processing + $this->orderItem->quantity_product;

        if ($this->transactionType === 'completed') {
            $product->update(['processing' => $processing]);
        } elseif ($this->transactionType === 'canceled') {
            $newQuantity = $product->stock + $this->orderItem->quantity_product;
            $product->update(['stock' => $newQuantity, 'processing' => $processing]);
        }
    }
}
