<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InvoiceReturnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Invoice $invoice
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'         => 'invoice_returned',
            'message'      => "Invoice #{$this->invoice->uuid} has been returned",
            'invoice_id'   => $this->invoice->id,
            'total_amount' => $this->invoice->total_amount,
            'returned_by'  => Auth::user()->name,
            'items_count'  => $this->invoice->items->count(),
        ];
    }
}
