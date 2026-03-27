<?php

namespace App\Http\Controllers\Web\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Restock product and recalculate average cost and selling price
     */
    public function restock(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'added_qty'      => ['required', 'integer', 'min:1'],
            'new_buy_price'  => ['required', 'numeric', 'min:0'],
            'new_sell_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $addedQty     = $validatedData['added_qty'];
            $newBuyPrice  = $validatedData['new_buy_price'];
            $newSellPrice = $validatedData['new_sell_price'];

            // Calculate total values before and after restock
            $oldTotalValue = $product->stock_qty * $product->buy_price;
            $newTotalValue = $addedQty * $newBuyPrice;
            $totalQty      = $product->stock_qty + $addedQty;

            // Calculate new average buy price
            $averageBuyPrice = ($oldTotalValue + $newTotalValue) / $totalQty;

            // Calculate minimum allowed sell price (25% profit)
            $minSellPrice = round($averageBuyPrice * 1.25, 2);

            // Prevent selling below minimum profit margin
            if ($newSellPrice < $minSellPrice) {
                return $this->errorResponse(
                    "Selling price must be at least {$minSellPrice} to keep 25% profit.",
                    ['suggested_min_price' => $minSellPrice],
                    422
                );
            }

            DB::transaction(function () use ($product, $totalQty, $averageBuyPrice, $newSellPrice, $addedQty, $newBuyPrice) {

                $product->update([
                    'stock_qty'  => $totalQty,
                    'buy_price'  => round($averageBuyPrice, 2),
                    'sell_price' => $newSellPrice,
                ]);

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id'    => Auth::id(),
                    'type'       => 'add',
                    'quantity'   => $addedQty,
                    'reason'     => "Restock at {$newBuyPrice} per unit",
                ]);
            });

            return $this->successResponse($product, 'Stock updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to restock', $e->getMessage(), 500);
        }
    }

    /**
     * Adjust stock manually (increase or decrease)
     */
    public function adjust(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'type'     => ['required', 'in:add,sub'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason'   => ['required', 'string', 'max:255'],
        ]);

        try {
            $type   = $validatedData['type'];
            $qty    = $validatedData['quantity'];
            $reason = $validatedData['reason'];

            // Prevent negative stock
            if ($type === 'sub' && $product->stock_qty < $qty) {
                return $this->errorResponse(
                    "Cannot remove {$qty}. Available stock is {$product->stock_qty}.",
                    null,
                    400
                );
            }

            DB::transaction(function () use ($product, $type, $qty, $reason) {

                if ($type === 'sub') {
                    $product->stock_qty -= $qty;
                } else {
                    $product->stock_qty += $qty;
                }
                $product->save();

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id'    => Auth::id(),
                    'type'       => $type,
                    'quantity'   => $qty,
                    'reason'     => $reason,
                ]);
            });

            return $this->successResponse($product, 'Stock adjusted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to adjust stock', $e->getMessage(), 500);
        }
    }

    /**
     * Get products with low stock
     */
    public function lowStock()
    {
        try {
            $products = Product::lowStock()
                ->active()
                ->with('category:id,name')
                ->orderBy('stock_qty', 'asc')
                ->get();

            $summary = [
                'total_low_stock_items' => $products->count(),
                'out_of_stock_items'    => $products->where('stock_qty', 0)->count(),
            ];

            return $this->successResponse([
                'summary'  => $summary,
                'products' => $products
            ], 'Low stock report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get low stock report', $e->getMessage(), 500);
        }
    }


    /**
     * Get the valuation of the inventory (cost value, potential revenue, and potential profit)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryValuation()
    {
        try {
            $products = Product::active()
                ->select('id', 'name', 'stock_qty', 'buy_price', 'sell_price')
                ->get()
                ->map(function ($product) {
                    return [
                        'product_id'       => $product->id,
                        'product_name'     => $product->name,
                        'stock_qty'        => $product->stock_qty,
                        'buy_price'        => $product->buy_price,
                        'sell_price'       => $product->sell_price,
                        'cost_value'       => $product->stock_qty * $product->buy_price,  // قيمة المخزون بسعر الشراء
                        'potential_revenue' => $product->stock_qty * $product->sell_price, // لو بعت كل حاجة هكسب قد إيه
                        'potential_profit' => $product->stock_qty * ($product->sell_price - $product->buy_price),
                    ];
                });

            $summary = [
                'total_cost_value'        => round($products->sum('cost_value'), 2),
                'total_potential_revenue' => round($products->sum('potential_revenue'), 2),
                'total_potential_profit'  => round($products->sum('potential_profit'), 2),
                'products'                => $products,
            ];

            return $this->successResponse($summary, 'Inventory valuation generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate valuation', $e->getMessage(), 500);
        }
    }
    /**
     * Get profit analysis for completed invoices only in a date range
     * (returned invoices are excluded to reflect actual cash received)
     */
    public function profitAnalysis(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            // Default to current month
            $startDate = $request->start_date
                ? $request->start_date . ' 00:00:00'
                : now()->startOfMonth()->toDateTimeString();

            $endDate = $request->end_date
                ? $request->end_date . ' 23:59:59'
                : now()->endOfMonth()->toDateTimeString();

            // Join with invoices to exclude returned invoices
            // Only completed invoices represent actual cash received
            $analysis = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoices.type', 'sale')
                ->whereBetween('invoice_items.created_at', [$startDate, $endDate])
                ->selectRaw('
                    SUM(total_row) as total_sales,
                    SUM((unit_price - buy_price) * quantity) as total_profit
                ')
                ->first();

            $totalSales  = (float) ($analysis->total_sales ?? 0);
            $totalProfit = (float) ($analysis->total_profit ?? 0);

            $profitMargin = $totalSales > 0
                ? ($totalProfit / $totalSales) * 100
                : 0;

            $report = [
                'period' => [
                    'from' => $startDate,
                    'to'   => $endDate,
                ],
                'total_sales'   => round($totalSales, 2),
                'total_profit'  => round($totalProfit, 2),
                'profit_margin' => round($profitMargin, 2) . '%',
            ];

            return $this->successResponse($report, 'Profit analysis generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate profit analysis', $e->getMessage(), 500);
        }
    }

    /**
     * Get all inventory transactions with product and user details
     */
    public function reportsAdjust()
    {
        try {
            $reports = InventoryTransaction::with([
                'product:id,name',
                'user:id,name',
            ])
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            return $this->successResponse($reports, 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve transactions', $e->getMessage(), 500);
        }
    }

    /**
     * Get all pending stock adjustment requests from cashiers
     */
    public function pendingRequests()
    {
        try {
            $requests = StockAdjustmentRequest::pending()
                ->with([
                    'product:id,name,stock_qty',
                    'cashier:id,name',
                ])
                ->orderBy('created_at', 'asc')
                ->paginate(20);

            return $this->successResponse($requests, 'Pending requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve requests', $e->getMessage(), 500);
        }
    }

    /**
     * Approve or reject a stock adjustment request from a cashier
     */
    /**
     * Approve or reject a stock adjustment request from a cashier
     */
    public function reviewRequest(Request $request, StockAdjustmentRequest $adjustmentRequest)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        try {
            // Prevent reviewing an already-reviewed request
            if ($adjustmentRequest->status !== 'pending') {
                return $this->errorResponse(
                    "Request already {$adjustmentRequest->status}",
                    null,
                    400
                );
            }

            // Rejected — only update status, no stock changes
            if ($request->decision === 'rejected') {
                $adjustmentRequest->update([
                    'status'      => 'rejected',
                    'reviewed_by' => Auth::id(),
                ]);

                return $this->successResponse(null, 'Request rejected successfully');
            }

            // Approved — update stock and log the transaction
            $product = $adjustmentRequest->product;
            $type    = $adjustmentRequest->type;
            $qty     = $adjustmentRequest->quantity;

            // Prevent negative stock
            if ($type === 'sub' && $product->stock_qty < $qty) {
                return $this->errorResponse(
                    "Cannot remove {$qty}. Available stock is {$product->stock_qty}.",
                    null,
                    400
                );
            }

            DB::transaction(function () use ($product, $type, $qty, $adjustmentRequest) {

                // Lock the row to prevent double approval
                $locked = StockAdjustmentRequest::lockForUpdate()
                    ->find($adjustmentRequest->id);

                if ($locked->status !== 'pending') {
                    throw new \Exception("Request already {$locked->status}");
                }

                $product->stock_qty = $type === 'sub'
                    ? $product->stock_qty - $qty
                    : $product->stock_qty + $qty;
                $product->save();

                // Log with both admin (user_id) and cashier (cashier_id)
                InventoryTransaction::create([
                    'product_id'  => $product->id,
                    'user_id'     => Auth::id(),
                    'cashier_id'  => $adjustmentRequest->cashier_id,
                    'type'        => $type,
                    'quantity'    => $qty,
                    'reason'      => $adjustmentRequest->reason,
                ]);

                $adjustmentRequest->update([
                    'status'      => 'approved',
                    'reviewed_by' => Auth::id(),
                ]);
            });

            return $this->successResponse(
                $product,
                'Request approved and stock updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to review request', $e->getMessage(), 500);
        }
    }
}
