<?php

namespace App\Notifications;

use App\Models\StockAdjustmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StockAdjustmentRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected StockAdjustmentRequest $adjustRequest
    ) {}

    public function via($notifiable): array
    {
        return ['database']; // نفس الجدول الموجود ✅
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'stock_adjustment_request',
            'message'     => "Cashier {$this->adjustRequest->cashier->name} requested stock adjustment",
            'product_id'  => $this->adjustRequest->product_id,
            'product_name'=> $this->adjustRequest->product->name,
            'request_id'  => $this->adjustRequest->id,
            'type_action' => $this->adjustRequest->type,
            'quantity'    => $this->adjustRequest->quantity,
            'reason'      => $this->adjustRequest->reason,
        ];
    }
}
