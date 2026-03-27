<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product)
    {
        if (
            $product->is_active &&
            $product->wasChanged('stock_qty') &&
            $product->stock_qty <= $product->alert_stock &&
            $product->getOriginal('stock_qty') > $product->alert_stock // Old stock was safe, new stock crossed the danger line
        ) {

            User::where('role', User::ROLE_ADMIN)
                ->chunk(50, function ($admins) use ($product) {
                    Notification::send(
                        $admins,
                        (new LowStockNotification($product))->onQueue('notifications')
                    );
                });
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
