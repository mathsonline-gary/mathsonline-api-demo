<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a JSON response with the given message and data.
     *
     * @param mixed       $data
     * @param string|null $message
     * @param bool|null   $success
     * @param int         $status
     * @param array       $headers
     * @param int         $options
     *
     * @return JsonResponse
     */
    private function responseAsJson(mixed $data = null, string $message = null, bool $success = null, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        $response = [];

        if (isset($success)) {
            $response['success'] = $success;
        }

        if (!is_null($message)) {
            $response['message'] = $message;
        }

        if (!is_null($data)) {
            $response['data'] = $data;

            // Add pagination links and meta if the data is a resource collection.
            if ($data instanceof ResourceCollection) {
                $responseData = $data->response()->getData(true);

                if (isset($responseData['links'])) {
                    $response['links'] = $responseData['links'];
                }

                if (isset($responseData['meta'])) {
                    $response['meta'] = $responseData['meta'];
                }
            }
        }

        if ($status === 204) {
            return response()->json(null, $status, $headers, $options);
        }

        return response()->json($response, $status, $headers, $options);
    }

    /**
     * Return a successful JSON response with the given message and data.
     * This is a standard response for successful API requests.
     * This method should be used for all successful responses in API controllers.
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $status
     * @param array  $headers
     * @param int    $options
     *
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, string $message = 'Success response.', int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return $this->responseAsJson($data, $message, true, $status, $headers, $options);
    }

    /**
     * Return an error JSON response with the given message and data.
     * This is a standard response for unsuccessful API requests.
     * This method should be used for all unsuccessful responses in API controllers.
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $status
     * @param array  $headers
     * @param int    $options
     *
     * @return JsonResponse
     */
    protected function errorResponse(mixed $data = null, string $message = 'Error response.', int $status = 400, array $headers = [], int $options = 0): JsonResponse
    {
        return $this->responseAsJson($data, $message, false, $status, $headers, $options);
    }
}
