<?php










namespace Symfony\Component\HttpKernel\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;






class Profile
{
    private $token;
    private $collectors;
    private $ip;
    private $method;
    private $url;
    private $time;
    private $parent;
    private $children;

    




    public function __construct($token)
    {
        $this->token = $token;
        $this->collectors = array();
        $this->children = array();
    }

    




    public function setToken($token)
    {
        $this->token = $token;
    }

    




    public function getToken()
    {
        return $this->token;
    }

    




    public function setParent(Profile $parent)
    {
        $this->parent = $parent;
    }

    




    public function getParent()
    {
        return $this->parent;
    }

    




    public function getParentToken()
    {
        return $this->parent ? $this->parent->getToken() : null;
    }

    




    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    




    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    




    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    




    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    




    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren(array $children)
    {
        $this->children = array();
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    




    public function addChild(Profile $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    public function getCollector($name)
    {
        if (!isset($this->collectors[$name])) {
            throw new \InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
        }

        return $this->collectors[$name];
    }

    public function getCollectors()
    {
        return $this->collectors;
    }

    public function setCollectors(array $collectors)
    {
        $this->collectors = array();
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }
    }

    public function addCollector(DataCollectorInterface $collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    public function hasCollector($name)
    {
        return isset($this->collectors[$name]);
    }

    public function __sleep()
    {
        return array('token', 'parent', 'children', 'collectors', 'ip', 'method', 'url', 'time');
    }
}
