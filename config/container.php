<?php

use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Odan\Twig\TwigAssetsExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

$container = new Container();

$container->delegate(new ReflectionContainer());

// Core
$container->share(ContainerInterface::class, static function (Container $container) {
    return $container;
})->addArgument($container);

// Application settings
$container->share('settings', static function () {
    return require __DIR__ . '/settings.php';
});

// Slim App
$container->share(App::class, static function (Container $container) {
    AppFactory::setContainer($container);

    return AppFactory::create();
})->addArgument($container);

// For the HtmlResponder
$container->share(ResponseFactoryInterface::class, static function (Container $container) {
    return $container->get(Psr17Factory::class);
})->addArgument($container);

// Global middleware
$container->share('middleware', static function (Container $container) {
    $settings = $container->get('settings');
    $app = $container->get(App::class);

    // Add global middleware to app
    $app->addRoutingMiddleware();

    $displayErrorDetails = (bool)$settings['error_handler_middeware']['display_error_details'];
    $logErrors = (bool)$settings['error_handler_middeware']['log_errors'];
    $logErrorDetails = (bool)$settings['error_handler_middeware']['log_error_details'];

    $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

    return true;
})->addArgument($container);

// The logger
$container->share(LoggerInterface::class, static function (Container $container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Logger($settings['name']);

    $level = isset($settings['level']) ?: Logger::ERROR;
    $filename = sprintf('%s/%s', $settings['path'], $settings['filename']);

    $handler = new RotatingFileHandler($filename, 0, $level, true, 0775);
    $logger->pushHandler($handler);

    return $logger;
})->addArgument($container);

// Twig templates
$container->share(Twig::class, static function (Container $container) {
    $settings = $container->get('settings');
    $viewPath = $settings['twig']['path'];

    $loader = new FilesystemLoader($viewPath);

    $twig = new Twig($loader, [
        'cache' => $settings['twig']['cache_enabled'] ? $settings['twig']['cache_path'] : false,
    ]);

    if ($loader instanceof FilesystemLoader) {
        $loader->addPath($settings['public'], 'public');
    }

    // Add CSRF token as global template variable
    //$csrfToken = $container->get(CsrfMiddleware::class)->getToken();
    //$twig->addGlobal('csrf_token', $csrfToken);

    //$twig->addGlobal('base_url', $container->get(RouterUrl::class)->pathFor('root'));
    //$twig->addGlobal('globalText', $container->get('globalText'));

    // Add Twig extensions
    $twig->addExtension(new TwigAssetsExtension($twig, (array)$settings['assets']));
    //$twig->addExtension(new TwigTranslationExtension());

    return $twig;
})->addArgument($container);

return $container;
