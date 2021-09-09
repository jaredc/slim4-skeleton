<?php

namespace App\Middleware;

use App\Handler\DefaultErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\ResponseEmitter;

/**
 * Middleware.
 */
final class ShutdownMiddleware implements MiddlewareInterface
{
    private bool $displayErrorDetails;
    private DefaultErrorHandler $errorHandler;

    /**
     * The constructor.
     *
     * @param DefaultErrorHandler $errorHandler The error handler
     * @param bool $displayErrorDetails Enable error details
     */
    public function __construct(
        DefaultErrorHandler $errorHandler,
        bool $displayErrorDetails = false
    ) {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $shutdown = function () use ($request) {
            $error = error_get_last();

            if (!$error) {
                return;
            }

            // Invoke default error handler
            $response = $this->errorHandler->__invoke(
                $request,
                $this->createExceptionFromError($error),
                $this->displayErrorDetails,
                true,
                true
            );

            (new ResponseEmitter())->emit($response);
        };

        // Disable html output to prevent output buffer issues
        $reporting = error_reporting(0);
        register_shutdown_function($shutdown);

        $response = $handler->handle($request);

        error_reporting($reporting);

        return $response;
    }

    /**
     * Create exception from error.
     *
     * @param array $error The error
     *
     * @return RuntimeException The exception
     */
    private function createExceptionFromError(array $error): RuntimeException
    {
        return new class($error['message'], $error['type'], $error['file'], $error['line']) extends RuntimeException {
            // phpcs:ignore
            public function __construct(string $message, int $code, string $file, int $line)
            {
                parent::__construct($message, $code);
                $this->file = $file;
                $this->line = $line;
            }
        };
    }
}
