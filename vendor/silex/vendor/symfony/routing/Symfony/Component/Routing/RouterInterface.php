<?php










namespace Symfony\Component\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;








interface RouterInterface extends UrlMatcherInterface, UrlGeneratorInterface
{
    




    function getRouteCollection();
}
