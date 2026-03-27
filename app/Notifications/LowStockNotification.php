<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $product;

    /**
     * Create a new notification instance.
     * We pass the product that has low stock here.
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     * We only want to save it in the database, not send an email.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * This is the exact data that will be saved in the database
     * and sent to the frontend Admin panel.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'message'      => "Warning: Product '{$this->product->name}' is running low! Current stock: {$this->product->stock_qty}",
        ];
    }
}
