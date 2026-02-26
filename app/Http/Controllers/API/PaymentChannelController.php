<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentChannelController extends Controller
{
    /**
     * GET: api/payment-channels
     * List all with parent method (The "Join")
     */
    public function index(): JsonResponse
    {
        $channels = PaymentChannel::with('paymentMethod')->get();
        
        return response()->json([
            'success' => true,
            'data' => $channels
        ], 200);
    }

    /**
     * POST: api/payment-channels
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'channel_name'      => 'required|string|max:255',
            'status'            => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $channel = PaymentChannel::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Channel created successfully',
            'data'    => $channel->load('paymentMethod')
        ], 201);
    }

    /**
     * GET: api/payment-channels/{id}
     */
    public function show(PaymentChannel $paymentChannel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paymentChannel->load('paymentMethod')
        ], 200);
    }

    /**
     * PUT: api/payment-channels/{id}
     */
    public function update(Request $request, PaymentChannel $paymentChannel): JsonResponse
    {
        $paymentChannel->update($request->only(['payment_method_id', 'channel_name', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Channel updated successfully',
            'data' => $paymentChannel->load('paymentMethod')
        ], 200);
    }

    /**
     * DELETE: api/payment-channels/{id}
     */
    public function destroy(PaymentChannel $paymentChannel): JsonResponse
    {
        $paymentChannel->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully'], 200);
    }
}