<?php

use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/push-subscriptions', function (Request $request) {
    $user = Auth::guard('web')->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated',
        ], 401);
    }

    $facility = TenantDatabaseService::getCurrentFacility() ?? getCurrentFacility();
    $facilityId = $facility?->id;

    if (! $facilityId) {
        Log::warning('Saving push subscription without facility context', [
            'user_id' => $user->getKey(),
            'endpoint' => $request->input('endpoint'),
        ]);
    }

    try {
        $user->updatePushSubscription(
            endpoint: $request->input('endpoint'),
            key: $request->input('keys.p256dh'),
            token: $request->input('keys.auth'),
            contentEncoding: $request->input('keys.contentEncoding'),
            facilityId: $facilityId
        );

        return response()->json([
            'success' => true,
            'message' => 'Push subscription saved successfully',
        ]);
    } catch (\Throwable $exception) {
        Log::error('Failed to save push subscription', [
            'user_id' => $user->getKey(),
            'error' => $exception->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to save push subscription',
        ], 500);
    }
})->middleware('web');
