<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderRequest;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PurchaseOrderController extends Controller
{
    #[OA\Post(
        path: "/api/v1/purchase-orders",
        summary: "Create a purchase order from an approved quotation",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["quotation_id"],
                properties: [
                    new OA\Property(property: "quotation_id", type: "integer", example: 1),
                    new OA\Property(property: "expected_delivery_date", type: "string", format: "date", example: "2026-09-20"),
                    new OA\Property(property: "delivery_address", type: "string", example: "Plot 315 Woji"),
                    new OA\Property(property: "payment_terms", type: "string", example: "Payment on delivery"),
                    new OA\Property(property: "tax", type: "number", example: 50),
                    new OA\Property(property: "discount", type: "number", example: 200),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase order created successfully"),
            new OA\Response(response: 400, description: "Quotation is not approved or a purchase order already exists"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(PurchaseOrderRequest $request){
        $user = Auth::user();
        Gate::authorize('create', PurchaseOrder::class );
        $quotation = Quotation::findOrFail($request->quotation_id);
        if($quotation->status !== 'approved'){
            return response()->json([
                'message' => 'Quotation is not approved.'
            ], 400);
        }

        $existspo = PurchaseOrder::where('quotation_id', $request->quotation_id)->exists();
        if($existspo){
            return response()->json([
                'message' => 'Purchase Order already exists for this quotation.'
            ], 400);
        }

        $poNumber = 'PO-' . date('Y') . '-' . str_pad( PurchaseOrder::count() + 1, 5, '0', STR_PAD_LEFT );

        $purchaseOrder = PurchaseOrder::create([
            'quotation_id' => $quotation->id, 
            'vendor_id' => $quotation->vendor_id, 
            'po_number' => $poNumber, 
            'order_date' => now()->toDateString(), 
            'expected_delivery_date' => $request->expected_delivery_date, 
            'delivery_address' => $request->delivery_address, 
            'payment_terms' => $request->payment_terms, 
            'tax' => $request->tax ?? 0, 
            'discount' => $request->discount ?? 0, 
            'total_amount' => 0, 
            'status' => 'draft', 
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Purchase Order created successfully.',
            'purchase_order' => $purchaseOrder
        ], 201);    
    }

    #[OA\Patch(
        path: "/api/v1/purchase-orders/{purchaseOrder}/submit",
        summary: "Submit a purchase order for approval",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "purchaseOrder", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Purchase order submitted successfully"),
            new OA\Response(response: 400, description: "Purchase order cannot be submitted"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Purchase order not found"),
        ]
    )]
    public function submitForApproval(PurchaseOrder $purchaseOrder){
        $user = Auth::user();
        Gate::authorize('update', $purchaseOrder);
        if($purchaseOrder->status !== 'draft'){
            return response()->json([
                'message' => 'Only draft purchase orders can be submitted for approval.'
            ], 400);
        }

        if($purchaseOrder->items()->count() === 0){
            return response()->json([
                'message' => 'Cannot submit a purchase order without items.'
            ], 400);
        }

         $purchaseOrder->update([
            'status' => 'pending_approval',
            'submitted_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Purchase Order submitted for approval successfully.',
            'purchase_order' => $purchaseOrder
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/purchase-orders/{purchaseOrder}/approve",
        summary: "Approve a purchase order",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "purchaseOrder", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Purchase order approved successfully"),
            new OA\Response(response: 400, description: "Purchase order is not pending approval"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Purchase order not found"),
        ]
    )]
    public function approve(PurchaseOrder $purchaseOrder){
        $user = Auth::user();
        Gate::authorize('approve', $purchaseOrder);
        if($purchaseOrder->status !== 'pending_approval'){
            return response()->json([
                'message' => 'Only purchase orders pending approval can be approved.'
            ], 400);
        }

       $purchaseOrder->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Purchase Order approved successfully.',
            'purchase_order' => $purchaseOrder
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/purchase-orders/{purchaseOrder}/reject",
        summary: "Reject a purchase order",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "purchaseOrder", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Purchase order rejected successfully"),
            new OA\Response(response: 400, description: "Purchase order is not pending approval"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Purchase order not found"),
        ]
    )]
    public function reject(PurchaseOrder $purchaseOrder){
        $user = Auth::user();
        Gate::authorize('approve', $purchaseOrder);
        if($purchaseOrder->status !== 'pending_approval'){
            return response()->json([
                'message' => 'Only purchase orders pending approval can be rejected.'
            ], 400);
        }

       $purchaseOrder->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Purchase Order rejected successfully.',
            'purchase_order' => $purchaseOrder
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/purchase-orders/{purchaseOrder}/send",
        summary: "Send an approved purchase order to the vendor",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "purchaseOrder", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Purchase order sent successfully"),
            new OA\Response(response: 400, description: "Purchase order is not approved"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Purchase order not found"),
        ]
    )]
    public function send(PurchaseOrder $purchaseOrder){
        $user = Auth::user();
        Gate::authorize('approve', $purchaseOrder);
        if($purchaseOrder->status !== 'approved'){
            return response()->json([
                'message' => 'Only approved purchase orders can be sent to vendors.'
            ], 400);
        }

       $purchaseOrder->update([
            'status' => 'sent',
        ]);

        return response()->json([
            'message' => 'Purchase Order sent to vendor successfully.',
            'purchase_order' => $purchaseOrder
        ], 200);
    }
    
    #[OA\Patch(
        path: "/api/v1/purchase-orders/{purchaseOrder}/receiving-status",
        summary: "Update a purchase order receiving status",
        tags: ["Purchase Orders"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "purchaseOrder", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Receiving status updated successfully"),
            new OA\Response(response: 400, description: "Purchase order cannot have its receiving status updated"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Purchase order not found"),
        ]
    )]
    public function receivingStatus(PurchaseOrder $purchaseOrder){
        $user = Auth::user();
        Gate::authorize('approve', $purchaseOrder);
        if(!in_array($purchaseOrder->status, ['sent', 'partially_sent'])){
                return response()->json([ 'message' => 'Only sent or partially received purchase orders can have their receiving status updated.' ], 400);
        }

        $purchaseOrderitems = $purchaseOrder->items;

        if ($purchaseOrderitems->count() ===0){
            return response()->json([ 'message' => 'This purchase order has no items.' ], 400);
        }

        $allReceived = true;
        $anyReceived = false;

        foreach($purchaseOrderitems as $item){
            $recievedQuantity = GoodsReceiptItem::where('purchase_order_item_id', $item->id)->sum('quantity_recieved');

            if($recievedQuantity > 0){
                $anyReceived = true;
            }

            if($recievedQuantity < $item->quantity){
                $allReceived = false;
            }
        }

        if ($allReceived) {
            $purchaseOrder->update(['status' => 'received']);
        } elseif ($anyReceived) {
            $purchaseOrder->update(['status' => 'partially_received']);
        }

        return response()->json([
            'message' => 'Purchase order receiving status updated successfully',
            'purchase_order' => $purchaseOrder->fresh(),
        ], 200);
    }
}
