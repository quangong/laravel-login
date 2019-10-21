<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Mi\AIBot\Exceptions\InvalidSignatureExpception;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        JWTException::class,
        TokenExpiredException::class,
        TokenBlacklistedException::class,
        ValidationException::class,
        AuthorizationException::class,
        AuthenticationException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class
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
        $statusCode  = 400;
        $errors      = [];
        $message     = __('messages.errors.unhandled_exception');
        $messageCode = '';
        switch (true) {
            case $e instanceof ValidationException:
                $message    = __('messages.errors.validation_error');
                $errors     = $e->errors();
                $statusCode = 422;
                break;
            case $e instanceof NotFoundHttpException:
            case $e instanceof MethodNotAllowedHttpException:
            case $e instanceof AccessDeniedHttpException:
            case $e instanceof AuthorizationException:
                $message    = __('messages.errors.route_not_found');
                $statusCode = 404;
                break;
            case $e instanceof ModelNotFoundException:
                $message = __('messages.errors.record_not_found');
                $statusCode = 404;
                break;
            case $e instanceof JWTException:
            case $e instanceof TokenInvalidException:
            case $e instanceof TokenBlacklistedException:
            case $e instanceof AuthenticationException:
                $message = __('messages.errors.session_not_found');
                $statusCode = 401;
                break;
            case $e instanceof ThrottleRequestsException:
                $message = __('messages.errors.throttle_request');
                break;
            case $e instanceof BaseException:
            case $e instanceof VoipException:
            case $e instanceof PaymentException:
            case $e instanceof CallingTalkException:
            case $e instanceof InvalidSignatureExpception:
                $message     = $e->getMessage();
                $messageCode = method_exists($e, 'getMessageCode') ? $e->getMessageCode() : null;
                $statusCode  = $e->getCode();
                break;
            default:
                break;
        }

        return $request->is('api/*')
            ? response()->json([
                'message' => $message,
                'errors'  => $errors
            ], $statusCode) : response($message, 400);
    }
}
