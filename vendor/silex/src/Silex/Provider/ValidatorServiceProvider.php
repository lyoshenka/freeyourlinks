<?php










namespace Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\ConstraintValidatorFactory;






class ValidatorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['validator'] = $app->share(function () use ($app) {
            return new Validator(
                $app['validator.mapping.class_metadata_factory'],
                $app['validator.validator_factory']
            );
        });

        $app['validator.mapping.class_metadata_factory'] = $app->share(function () use ($app) {
            return new ClassMetadataFactory(new StaticMethodLoader());
        });

        $app['validator.validator_factory'] = $app->share(function () {
            return new ConstraintValidatorFactory();
        });

        if (isset($app['validator.class_path'])) {
            $app['autoloader']->registerNamespace('Symfony\\Component\\Validator', $app['validator.class_path']);
        }
    }
}
