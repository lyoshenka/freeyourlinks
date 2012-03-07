<?php










namespace Symfony\Component\Routing\Generator\Dumper;

use Symfony\Component\Routing\RouteCollection;






abstract class GeneratorDumper implements GeneratorDumperInterface
{
    private $routes;

    




    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
