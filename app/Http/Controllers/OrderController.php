<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Show the create order page
     */
    public function create()
    {
        return view('orders.create');
    }

    /**
     * Show the pending approval page
     */
    public function pending()
    {
        $orders = Order::with(['orderItems.book', 'requester', 'approver', 'fulfiller'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('orders.pending', compact('orders'));
    }

    /**
     * Show the awaiting fulfillment page
     */
    public function fulfillment()
    {
        $orders = Order::with(['orderItems.book', 'requester', 'approver', 'fulfiller'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('orders.fulfillment', compact('orders'));
    }

    /**
     * Show the order history page with filtering
     */
    public function history(Request $request)
    {
        $query = Order::with(['orderItems.book', 'requester', 'approver', 'fulfiller'])
            ->whereIn('status', ['fulfilled', 'rejected']);

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('requester', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return view('orders.history', compact('orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purpose' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'items_count' => count($request->items),
                'purpose' => $request->purpose,
                'requester_id' => Auth::id(),
                'status' => 'pending'
            ]);

            // Create order items
            foreach ($request->items as $index => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_number' => $index + 1,
                    'book_id' => $item['book_id'],
                    'quantity_requested' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully!',
                    'order_number' => $order->order_number
                ]);
            }

            return redirect()->route('orders.create')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to create order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Approve or reject an order
     */
    public function approve(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000'
        ]);

        if (!Auth::user()->canApproveOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve orders.'
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been processed.'
            ], 400);
        }

        try {
            $status = $request->action === 'approve' ? 'approved' : 'rejected';
            
            $order->update([
                'status' => $status,
                'approver_id' => Auth::id(),
                'approval_date' => now(),
                'notes' => $request->notes
            ]);

            $message = $request->action === 'approve' ? 'Order approved successfully!' : 'Order rejected successfully!';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('orders.pending')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process order: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Fulfill an order
     */
    public function fulfill(Request $request, Order $order)
    {
        // Handle JSON data from fulfillment form
        if ($request->has('items') && is_string($request->items)) {
            $items = json_decode($request->items, true);
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:order_items,id',
            'items.*.quantity_fulfilled' => 'required|integer|min:0'
        ]);

        if (!Auth::user()->canFulfillOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to fulfill orders.'
            ], 403);
        }

        if ($order->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved orders can be fulfilled.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update order items and stock levels
            foreach ($request->items as $itemData) {
                $orderItem = OrderItem::find($itemData['id']);
                if ($orderItem && $orderItem->order_id == $order->id) {
                    $quantityFulfilled = (int) $itemData['quantity_fulfilled'];
                    
                    // Update order item
                    $orderItem->update(['quantity_fulfilled' => $quantityFulfilled]);
                    
                    // Update book stock
                    if ($quantityFulfilled > 0) {
                        $book = $orderItem->book;
                        $newStock = max(0, $book->stock - $quantityFulfilled);
                        $book->update(['stock' => $newStock]);
                    }
                }
            }

            // Update order status
            $order->update([
                'status' => 'fulfilled',
                'fulfiller_id' => Auth::id(),
                'fulfillment_date' => now(),
                'notes' => $request->notes
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order fulfilled successfully!'
                ]);
            }

            return redirect()->route('orders.fulfillment')->with('success', 'Order fulfilled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fulfill order: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to fulfill order: ' . $e->getMessage());
        }
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        $order->load(['orderItems.book', 'requester', 'approver', 'fulfiller']);
        
        return response()->json($order);
    }

    /**
     * Get orders by status for tabs
     */
    public function getByStatus($status)
    {
        $orders = Order::with(['orderItems.book', 'requester', 'approver', 'fulfiller'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Get pending counts for badges
     */
    public function getPendingCounts()
    {
        return response()->json([
            'pending_approval' => Order::where('status', 'pending')->count(),
            'awaiting_fulfillment' => Order::where('status', 'approved')->count()
        ]);
    }
}