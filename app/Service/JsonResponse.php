<?php

namespace App\Service;

class JsonResponse
{
    public static function successOrError($errorMessage, $result='success')
    {
        if ($errorMessage) {
            return response()->json([
                'errors' => [$errorMessage]
            ], 500);
        }
        return response()->json([
                'result' => $result
            ]);
    }
}