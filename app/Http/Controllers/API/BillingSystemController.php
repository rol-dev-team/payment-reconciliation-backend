<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BillingSystem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BillingSystemController extends Controller
{
    /**
     * GET: api/billing-systems
     * List all billing systems.
     */
    public function index(): JsonResponse
    {
        // If you need to see related comparisons later, add ->with('comparisons')
        $systems = BillingSystem::all();
        
        return response()->json([
            'success' => true,
            'data' => $systems
        ], 200);
    }

    /**
     * POST: api/billing-systems
     * Validates and creates a new billing system.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'billing_name' => 'required|string|max:255|unique:billing_systems,billing_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $billingSystem = BillingSystem::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Billing System created successfully',
            'data'    => $billingSystem
        ], 201);
    }

    /**
     * GET: api/billing-systems/{billingSystem}
     * Fetch a specific billing system by ID.
     */
    public function show(BillingSystem $billingSystem): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $billingSystem
        ], 200);
    }

    /**
     * PUT/PATCH: api/billing-systems/{billingSystem}
     * Update an existing billing system.
     */
    public function update(Request $request, BillingSystem $billingSystem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'billing_name' => 'sometimes|string|max:255|unique:billing_systems,billing_name,' . $billingSystem->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $billingSystem->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Billing System updated successfully',
            'data'    => $billingSystem
        ], 200);
    }

    /**
     * DELETE: api/billing-systems/{billingSystem}
     * Remove a billing system.
     */
    public function destroy(BillingSystem $billingSystem): JsonResponse
    {
        $billingSystem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Billing System deleted successfully'
        ], 200);
    }
}