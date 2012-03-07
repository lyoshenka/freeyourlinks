<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;






class ProfilerListener implements EventSubscriberInterface
{
    protected $profiler;
    protected $matcher;
    protected $onlyException;
    protected $onlyMasterRequests;
    protected $exception;
    protected $children;
    protected $requests;

    







    public function __construct(Profiler $profiler, RequestMatcherInterface $matcher = null, $onlyException = false, $onlyMasterRequests = false)
    {
        $this->profiler = $profiler;
        $this->matcher = $matcher;
        $this->onlyException = (Boolean) $onlyException;
        $this->onlyMasterRequests = (Boolean) $onlyMasterRequests;
        $this->children = new \SplObjectStorage();
    }

    




    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->onlyMasterRequests && HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $this->exception = $event->getException();
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->requests[] = $event->getRequest();
    }

    




    public function onKernelResponse(FilterResponseEvent $event)
    {
        $master = HttpKernelInterface::MASTER_REQUEST === $event->getRequestType();
        if ($this->onlyMasterRequests && !$master) {
            return;
        }

        if ($this->onlyException && null === $this->exception) {
            return;
        }

        $request = $event->getRequest();
        $exception = $this->exception;
        $this->exception = null;

        if (null !== $this->matcher && !$this->matcher->matches($request)) {
            return;
        }

        if (!$profile = $this->profiler->collect($request, $event->getResponse(), $exception)) {
            return;
        }

        
        if (!$master) {
            array_pop($this->requests);

            $parent = end($this->requests);
            $profiles = isset($this->children[$parent]) ? $this->children[$parent] : array();
            $profiles[] = $profile;
            $this->children[$parent] = $profiles;
        }

        
        if (isset($this->children[$request])) {
            foreach ($this->children[$request] as $child) {
                $child->setParent($profile);
                $profile->addChild($child);
                $this->profiler->saveProfile($child);
            }
            $this->children[$request] = array();
        }

        $this->profiler->saveProfile($profile);
    }

    static public function getSubscribedEvents()
    {
        return array(
            
            
            KernelEvents::REQUEST => array('onKernelRequest', 1024),

            KernelEvents::RESPONSE => array('onKernelResponse', -100),
            KernelEvents::EXCEPTION => 'onKernelException',
        );
    }
}
