<?php

namespace App\Exceptions;

use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Server\ServerException;
use App\Exceptions\Payment\PaymentException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Render Pterodactyl exceptions as JSON for API requests and with the
        // matching error page for web requests.
        $this->renderable(function (PterodactylException $e, $request) {
            return $this->renderTypedWithErrorPage($e, $request);
        });

        // Render server exceptions with their status code.
        $this->renderable(function (ServerException $e, $request) {
            return $this->renderTypedWithErrorPage($e, $request);
        });

        // Render payment exceptions as JSON for API requests and with the
        // matching error page for web requests.
        $this->renderable(function (PaymentException $e, $request) {
            return $this->renderTypedWithErrorPage($e, $request);
        });
    }

    /**
     * Shared renderer for typed domain exceptions.
     *
     * JSON with the safe public message for API requests, otherwise the
     * matching error page (falling back to the 500 page). One place to
     * change so the three exception families can never drift apart.
     */
    private function renderTypedWithErrorPage(PterodactylException|ServerException|PaymentException $e, $request)
    {
        $status = $e->getStatusCode() ?? 500;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $e->getPublicMessage(),
            ], $status);
        }

        $view = view()->exists('errors.' . $status) ? 'errors.' . $status : 'errors.500';

        return response()->view($view, [
            'exception' => $e,
            'errorCode' => $status,
            'title' => 'Error',
            'message' => $e->getPublicMessage(),
            'homeLink' => true,
        ], $status);
    }


    /**
     * @param $request
     * @param Throwable $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception)) {
            if (view()->exists('errors.' . $exception->getStatusCode())) {
                return response()->view(
                    'errors.' . $exception->getStatusCode(),
                    ['exception' => $exception],
                    $exception->getStatusCode()
                );
            }
        }

        // Fallback to default behavior for non-HTTP exceptions
        return parent::render($request, $exception);
    }
}
