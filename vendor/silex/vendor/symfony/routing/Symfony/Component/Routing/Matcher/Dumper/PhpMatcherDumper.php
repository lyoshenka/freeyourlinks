<?php










namespace Symfony\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;






class PhpMatcherDumper extends MatcherDumper
{
    











    public function dump(array $options = array())
    {
        $options = array_merge(array(
            'class'      => 'ProjectUrlMatcher',
            'base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
        ), $options);

        
        $interfaces = class_implements($options['base_class']);
        $supportsRedirections = isset($interfaces['Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface']);

        return
            $this->startClass($options['class'], $options['base_class']).
            $this->addConstructor().
            $this->addMatcher($supportsRedirections).
            $this->endClass()
        ;
    }

    private function addMatcher($supportsRedirections)
    {
        
        $code = implode("\n", $this->compileRoutes(clone $this->getRoutes(), $supportsRedirections));

        return <<<EOF

    public function match(\$pathinfo)
    {
        \$allow = array();
        \$pathinfo = urldecode(\$pathinfo);

$code
        throw 0 < count(\$allow) ? new MethodNotAllowedException(array_unique(\$allow)) : new ResourceNotFoundException();
    }

EOF;
    }

    private function compileRoutes(RouteCollection $routes, $supportsRedirections, $parentPrefix = null)
    {
        $code = array();

        $routeIterator = $routes->getIterator();
        $keys = array_keys($routeIterator->getArrayCopy());
        $keysCount = count($keys);

        $i = 0;
        foreach ($routeIterator as $name => $route) {
            $i++;

            if ($route instanceof RouteCollection) {
                $prefix = $route->getPrefix();
                $optimizable = $prefix && count($route->all()) > 1 && false === strpos($route->getPrefix(), '{');
                $indent = '';
                if ($optimizable) {
                    for ($j = $i; $j < $keysCount; $j++) {
                        if ($keys[$j] === null) {
                            continue;
                        }

                        $testRoute = $routeIterator->offsetGet($keys[$j]);
                        $isCollection = ($testRoute instanceof RouteCollection);

                        $testPrefix = $isCollection ? $testRoute->getPrefix() : $testRoute->getPattern();

                        if (0 === strpos($testPrefix, $prefix)) {
                            $routeIterator->offsetUnset($keys[$j]);

                            if ($isCollection) {
                                $route->addCollection($testRoute);
                            } else {
                                $route->add($keys[$j], $testRoute);
                            }

                            $i++;
                            $keys[$j] = null;
                        }
                    }

                    if ($prefix !== $parentPrefix) {
                        $code[] = sprintf("        if (0 === strpos(\$pathinfo, %s)) {", var_export($prefix, true));
                        $indent = '    ';
                    }
                }

                foreach ($this->compileRoutes($route, $supportsRedirections, $prefix) as $line) {
                    foreach (explode("\n", $line) as $l) {
                        if ($l) {
                            $code[] = $indent.$l;
                        } else {
                            $code[] = $l;
                        }
                    }
                }

                if ($optimizable && $prefix !== $parentPrefix) {
                    $code[] = "        }\n";
                }
            } else {
                foreach ($this->compileRoute($route, $name, $supportsRedirections, $parentPrefix) as $line) {
                    $code[] = $line;
                }
            }
        }

        return $code;
    }

    private function compileRoute(Route $route, $name, $supportsRedirections, $parentPrefix = null)
    {
        $code = array();
        $compiledRoute = $route->compile();
        $conditions = array();
        $hasTrailingSlash = false;
        $matches = false;
        $methods = array();
        if ($req = $route->getRequirement('_method')) {
            $methods = explode('|', strtoupper($req));
            
            if (in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                $methods[] = 'HEAD';
            }
        }
        $supportsTrailingSlash = $supportsRedirections && (!$methods || in_array('HEAD', $methods));

        if (!count($compiledRoute->getVariables()) && false !== preg_match('#^(.)\^(?P<url>.*?)\$\1#', str_replace(array("\n", ' '), '', $compiledRoute->getRegex()), $m)) {
            if ($supportsTrailingSlash && substr($m['url'], -1) === '/') {
                $conditions[] = sprintf("rtrim(\$pathinfo, '/') === %s", var_export(rtrim(str_replace('\\', '', $m['url']), '/'), true));
                $hasTrailingSlash = true;
            } else {
                $conditions[] = sprintf("\$pathinfo === %s", var_export(str_replace('\\', '', $m['url']), true));
            }
        } else {
            if ($compiledRoute->getStaticPrefix() && $compiledRoute->getStaticPrefix() != $parentPrefix) {
                $conditions[] = sprintf("0 === strpos(\$pathinfo, %s)", var_export($compiledRoute->getStaticPrefix(), true));
            }

            $regex = str_replace(array("\n", ' '), '', $compiledRoute->getRegex());
            if ($supportsTrailingSlash && $pos = strpos($regex, '/$')) {
                $regex = substr($regex, 0, $pos).'/?$'.substr($regex, $pos + 2);
                $hasTrailingSlash = true;
            }
            $conditions[] = sprintf("preg_match(%s, \$pathinfo, \$matches)", var_export($regex, true));

            $matches = true;
        }

        $conditions = implode(' && ', $conditions);

        $gotoname = 'not_'.preg_replace('/[^A-Za-z0-9_]/', '', $name);

        $code[] = <<<EOF
        // $name
        if ($conditions) {
EOF;

        if ($methods) {
            if (1 === count($methods)) {
                $code[] = <<<EOF
            if (\$this->context->getMethod() != '$methods[0]') {
                \$allow[] = '$methods[0]';
                goto $gotoname;
            }
EOF;
            } else {
                $methods = implode('\', \'', $methods);
                $code[] = <<<EOF
            if (!in_array(\$this->context->getMethod(), array('$methods'))) {
                \$allow = array_merge(\$allow, array('$methods'));
                goto $gotoname;
            }
EOF;
            }
        }

        if ($hasTrailingSlash) {
            $code[] = sprintf(<<<EOF
            if (substr(\$pathinfo, -1) !== '/') {
                return \$this->redirect(\$pathinfo.'/', '%s');
            }
EOF
            , $name);
        }

        if ($scheme = $route->getRequirement('_scheme')) {
            if (!$supportsRedirections) {
                throw new \LogicException('The "_scheme" requirement is only supported for URL matchers that implement RedirectableUrlMatcherInterface.');
            }

            $code[] = sprintf(<<<EOF
            if (\$this->context->getScheme() !== '$scheme') {
                return \$this->redirect(\$pathinfo, '%s', '$scheme');
            }
EOF
            , $name);
        }

        
        if (true === $matches && $compiledRoute->getDefaults()) {
            $code[] = sprintf("            return array_merge(\$this->mergeDefaults(\$matches, %s), array('_route' => '%s'));"
                , str_replace("\n", '', var_export($compiledRoute->getDefaults(), true)), $name);
        } elseif (true === $matches) {
            $code[] = sprintf("            \$matches['_route'] = '%s';", $name);
            $code[] = sprintf("            return \$matches;", $name);
        } elseif ($compiledRoute->getDefaults()) {
            $code[] = sprintf('            return %s;', str_replace("\n", '', var_export(array_merge($compiledRoute->getDefaults(), array('_route' => $name)), true)));
        } else {
            $code[] = sprintf("            return array('_route' => '%s');", $name);
        }
        $code[] = "        }";

        if ($methods) {
            $code[] = "        $gotoname:";
        }

        $code[] = '';

        return $code;
    }

    private function startClass($class, $baseClass)
    {
        return <<<EOF
<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * $class
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class $class extends $baseClass
{

EOF;
    }

    private function addConstructor()
    {
        return <<<EOF
    /**
     * Constructor.
     */
    public function __construct(RequestContext \$context)
    {
        \$this->context = \$context;
    }

EOF;
    }

    private function endClass()
    {
        return <<<EOF
}

EOF;
    }
}
