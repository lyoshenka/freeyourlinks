<?php










namespace Symfony\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\RouteCollection;






abstract class MatcherDumper implements MatcherDumperInterface
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
