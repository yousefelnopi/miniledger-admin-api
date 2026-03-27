<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\Invoice\InvoiceController;
use App\Models\Product;
use App\Models\StockAdjustmentRequest;
use App\Models\User;
use App\Notifications\StockAdjustmentRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SyncController extends Controller
{
    protected InvoiceController $invoiceController;

    public function __construct(InvoiceController $invoiceController)
    {
        $this->invoiceController = $invoiceController;
    }

    // ==============================
    // استقبال المبيعات (أونلاين أو أوفلاين)
    // ==============================
    public function receiveSale(Request $request)
    {
        $invoices = $request->input('invoices')
            ? $request->input('invoices') // If Cashier app Send A lto of Invoices [Offline]
            : [$request->all()];    // if Cashier app Send One Invoice [Online]

        $saved  = [];  // All Saved Invoices
        $failed = [];  // All Failed Invoices

        /**
         * @ Try To Save Each Invoice
         * @ Catch Any Exception and Push To Failed
         */
        foreach ($invoices as $invoiceData) {
            try {
                $this->invoiceController->store(new Request($invoiceData));
                $saved[] = $invoiceData['uuid'] ?? null;
            } catch (\Exception $e) {
                $failed[] = [
                    'uuid'  => $invoiceData['uuid'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }


        // If All Invoices Saved Successfully
        if (empty($failed)) {
            return $this->successResponse(
                ['saved' => $saved],
                'All invoices synced successfully'
            );
        }

        // If Some Invoices Saved Successfully & Some Failed
        if (!empty($saved) && !empty($failed)) {
            return $this->successResponse(
                ['saved' => $saved, 'failed' => $failed],
                'Partially synced — some invoices failed',
                207
            );
        }

        // If All Invoices Failed
        return $this->errorResponse(
            'All invoices failed to sync',
            $failed,
            422
        );
    }

    // ==============================
    // التحقق من الكمية قبل البيع
    // ==============================
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $results = [];

        $ids = collect($request->items)->pluck('product_id');

        // جيبهم كلهم في query واحدة
        $products = Product::whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id'); // عشان توصل لأي product بالـ id مباشرة

        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                $results[] = [
                    'product_id'    => $item['product_id'],
                    'product_name'  => null,
                    'requested_qty' => $item['quantity'],
                    'current_stock' => 0,
                    'available'     => false,
                    'reason'        => 'Product is inactive or not found',
                ];
                continue;
            }

            $results[] = [
                'product_id'    => $product->id,
                'product_name'  => $product->name,
                'requested_qty' => $item['quantity'],
                'current_stock' => $product->stock_qty,
                'available'     => $product->stock_qty >= $item['quantity'],
            ];
        }

        $allAvailable = collect($results)->every(fn($r) => $r['available']); // All Products Available

        return $this->successResponse(
            $results,
            $allAvailable
                ? 'All products are available'
                : 'Some products are not available'
        );
    }

    // ==============================
    // طلب تعديل المخزون (من الكاشير)
    // ==============================
    public function requestAdjust(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:add,sub'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'reason'     => ['required', 'string', 'max:255'],
        ]);

        try {
            $adjustRequest = StockAdjustmentRequest::create([
                'product_id' => $request->product_id,
                'cashier_id' => auth()->id(),
                'type'       => $request->type,
                'quantity'   => $request->quantity,
                'reason'     => $request->reason,
                'status'     => 'pending',
            ]);

            $admins = User::where('role', User::ROLE_ADMIN)->get();
            Notification::send($admins, new StockAdjustmentRequestNotification($adjustRequest));

            return $this->successResponse(
                $adjustRequest,
                'Adjustment request submitted successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit request', $e->getMessage(), 500);
        }
    }
}
