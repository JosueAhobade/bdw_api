<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
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
     * Messages par défaut (en français) selon le code HTTP.
     */
    protected const HTTP_MESSAGES = [
        400 => 'Requête invalide.',
        401 => 'Non authentifié. Veuillez vous connecter.',
        403 => 'Action non autorisée.',
        404 => 'Ressource introuvable.',
        405 => 'Méthode non autorisée pour cette adresse.',
        413 => 'Les données envoyées sont trop volumineuses.',
        419 => 'Session expirée. Veuillez recharger la page.',
        429 => 'Trop de requêtes. Réessayez dans quelques instants.',
        500 => 'Erreur interne du serveur.',
        503 => 'Service momentanément indisponible.',
    ];

    /**
     * Messages anglais du framework à remplacer (les messages personnalisés sont conservés).
     */
    protected const FRAMEWORK_MESSAGES = [
        'This action is unauthorized.',
        'Too Many Attempts.',
        'Unauthenticated.',
        'Not Found',
        'Server Error',
        'Service Unavailable',
        'CSRF token mismatch.',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // API : toujours des erreurs en JSON (401, 403, 422...)
        $this->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->shouldReturnJson($request, $exception)
            ? response()->json(['message' => self::HTTP_MESSAGES[401]], 401)
            : parent::unauthenticated($request, $exception);
    }

    /**
     * Corps JSON des erreurs : message traduit en français.
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        $data = parent::convertExceptionToArray($e);
        $data['message'] = $this->frenchMessage($e, (string) ($data['message'] ?? ''));

        return $data;
    }

    protected function frenchMessage(Throwable $e, string $message): string
    {
        if (! $e instanceof HttpExceptionInterface) {
            return self::HTTP_MESSAGES[500];
        }

        $status = $e->getStatusCode();

        if ($status === 404 && $e->getPrevious() instanceof ModelNotFoundException) {
            return 'Élément introuvable.';
        }

        if ($status === 429) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

            return $retryAfter
                ? "Trop de tentatives. Réessayez dans $retryAfter secondes."
                : self::HTTP_MESSAGES[429];
        }

        $isFrameworkMessage = $message === ''
            || in_array($message, self::FRAMEWORK_MESSAGES, true)
            || str_starts_with($message, 'The route ')
            || str_contains($message, 'method is not supported');

        if ($isFrameworkMessage) {
            return self::HTTP_MESSAGES[$status] ?? 'Une erreur est survenue.';
        }

        return $message;
    }
}
