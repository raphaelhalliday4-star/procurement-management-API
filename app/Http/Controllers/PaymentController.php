<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Models\payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    #[OA\Post(
        path: '/api/v1/payments',
        operationId: 'createPayment',
        tags: ['Payments'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['invoice_id', 'payment_method'],
                properties: [
                    new OA\Property(property: 'invoice_id', type: 'integer', example: 1),
                    new OA\Property(property: 'payment_method', type: 'string', enum: ['bank_transfer', 'card', 'cheque'], example: 'bank_transfer'),
                    new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Payment for approved invoice'),
                ]
            )
        ),
         responses: [
            new OA\Response(
                response: 201,
                description: "user created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user created successfully"),
                        new OA\Property(property: "member", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The name field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]
    public function store(PaymentRequest $request){
        Gate::authorize('create', payment::class);
        $user = Auth::user();
        $invoice = Invoice::find($request->invoice_id);

        if($invoice->doesntExist()){
            return response()->json([
                'message'=>'The invoice iD does not exist'
            ]);
        }

        if($invoice->status !== 'approved'){
            return response()->json([
                'message'=> 'You can only make payment to an approved invoice'
            ]);
        }

        if($invoice->status === 'paid'){
            return response()->json([
                'message'=> 'Payment hasalready been made for this invoice'
            ]);
        } 

        $paymentReference = 'PAY-' . date('Y') . '-' . str_pad( payment::count() + 1, 5, '0', STR_PAD_LEFT );


        $payment = payment::create([
            'invoice_id'=> $request->invoice_id,
            'payment_reference'=> $paymentReference,
            'payment_date'=> now()->toDateTime(),
            'amount'=>$invoice->total_amount,
            'payment_method'=> $request->payment_method,
            'note'=>$request->note,
            'paid_by'=> $user->id
        ]);

        $invoice->update([
            'status'=> 'paid'
        ]);

        return response()->json([
            'message'=> 'Payment successful',
            'Payment'=>$payment,
            'invoice' => $invoice->fresh(),
        ]);
    }
}
