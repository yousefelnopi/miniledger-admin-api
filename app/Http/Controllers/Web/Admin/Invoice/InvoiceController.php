<?php

namespace App\Http\Controllers\Web\Admin\Invoice;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\InvoiceReturnedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InvoiceController extends Controller
{
    // ==============================
    // إنشاء فاتورة جديدة
    // ==============================
    public function store(Request $request)
    {
        $request->validate([
            'uuid'               => 'nullable|string',
            'type'               => 'required|in:sale,return',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'customer_id'        => 'nullable|exists:customers,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $existing = Invoice::where('uuid', $request->uuid)->first();
        if ($existing) {
            return $this->successResponse(
                $existing->load('items'),
                'Invoice already exists'
            );
        }

        $type = $request->type ?? 'sale';

        DB::beginTransaction();
        try {
            $items         = $request->items;
            $discount      = $request->discount ?? 0;
            $tax           = $request->tax ?? 0;
            $totalAmount   = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                // Lock the product row to prevent race condition
                $product  = Product::lockForUpdate()->findOrFail($item['product_id']);
                $totalRow = $item['quantity'] * $item['unit_price'];

                $preparedItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'buy_price'    => $product->buy_price,
                    'unit_price'   => $item['unit_price'],
                    'total_row'    => $totalRow,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $totalAmount += $totalRow;

                // Update stock inside the same loop while lock is still active
                if ($type === 'sale') {
                    $product->decrement('stock_qty', $item['quantity']);
                } else {
                    $product->increment('stock_qty', $item['quantity']);

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'user_id'    => Auth::id(),
                        'type'       => 'add',
                        'quantity'   => $item['quantity'],
                        'reason'     => "Returned from invoice #{$request->uuid}",
                    ]);
                }
            }

            $totalAmount = $totalAmount - $discount + $tax;

            $invoice = Invoice::create([
                'uuid'         => $request->uuid,
                'user_id'      => Auth::id(),
                'customer_id'  => $request->customer_id ?? null,
                'total_amount' => $totalAmount,
                'discount'     => $discount,
                'tax'          => $tax,
                'type'         => $type,
                'is_synced'    => true,
            ]);

            foreach ($preparedItems as &$item) {
                $item['invoice_id'] = $invoice->id;
            }
            InvoiceItem::insert($preparedItems);

            if ($type === 'return') {
                $admins = User::where('role', User::ROLE_ADMIN)->get();
                Notification::send($admins, new InvoiceReturnedNotification($invoice));
            }

            DB::commit();

            return $this->successResponse(
                $invoice->load('items'),
                $type === 'sale'
                    ? 'Invoice created successfully'
                    : 'Return invoice created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create invoice', $e->getMessage(), 500);
        }
    }

    // ==============================
    // قائمة الفواتير (للأدمن)
    // ==============================
    public function index(Request $request)
    {
        try {
            $invoices = Invoice::with(['user:id,name', 'customer:id,name'])
                ->when(
                    $request->type,
                    fn($q) =>
                    $q->where('type', $request->type)
                )
                ->when(
                    $request->is_synced !== null,
                    fn($q) =>
                    $q->where('is_synced', $request->boolean('is_synced'))
                )
                ->when(
                    $request->cashier_id,
                    fn($q) =>
                    $q->where('user_id', $request->cashier_id)
                )
                ->when(
                    $request->date,
                    fn($q) =>
                    $q->whereDate('created_at', $request->date)
                )
                ->when(
                    $request->from && $request->to,
                    fn($q) =>
                    $q->whereBetween('created_at', [
                        $request->from . ' 00:00:00',
                        $request->to   . ' 23:59:59',
                    ])
                )
                ->latest()
                ->paginate(20);

            return $this->successResponse($invoices, 'Invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve invoices', $e->getMessage(), 500);
        }
    }

    // ==============================
    // تفاصيل فاتورة واحدة
    // ==============================
    public function show(Invoice $invoice)
    {
        try {
            $invoice->load([
                'items',
                'user:id,name',
                'customer:id,name',
            ]);

            return $this->successResponse($invoice, 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve invoice', $e->getMessage(), 500);
        }
    }
}
