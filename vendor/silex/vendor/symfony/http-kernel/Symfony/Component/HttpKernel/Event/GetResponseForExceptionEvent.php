<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
















class GetResponseForExceptionEvent extends GetResponseEvent
{
    



    private $exception;

    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, \Exception $e)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->setException($e);
    }

    






    public function getException()
    {
        return $this->exception;
    }

    








    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
}
