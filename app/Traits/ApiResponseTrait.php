<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Standard Success Response Contract
     */
    public function successResponse($data = null, $message = 'Request successful', $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * Standard Error Response Contract
     */
    public function errorResponse($message = 'An error occurred', $errors = null, $code = 400)
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        // بنضيف الـ errors array بس لو موجودة (عشان الفاليديشن)
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
