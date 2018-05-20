<?php

namespace App\Exceptions;

use Exception;
use Carbon\Carbon;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        switch (true) {
            case $e instanceof MethodNotAllowedHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'method_not_allowed',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 405
                ], 405);
            break;
            case $e instanceof NotFoundHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'not_found',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 404
                ], 404);
            break;
            case $e instanceof BadRequestHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'bad_request',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 400
                ], 400);
            break;
            case $e instanceof UnprocessableEntityHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'unprocessable_entity',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 422
                ], 422);
            break;
            case $e instanceof AccessDeniedHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'access_denied',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 403
                ], 403);
            break;
            case $e instanceof UnauthorizedHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'unauthorized',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 401
                ], 401);
            break;
            case $e instanceof ServiceUnavailableHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'service_unavailable',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 503
                ], 503);
            break;
            case $e instanceof TooManyRequestsHttpException:
                return response()->json([
                    'endpoint'  => '/'.$request->path(),
                    'message'   => 'too_many_requests',
                    'success'   => false,
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 429
                ], 429);
            break;
        }
        return parent::render($request, $e);
    }
}
