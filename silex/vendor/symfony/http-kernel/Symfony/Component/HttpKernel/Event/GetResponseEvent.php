<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Response;












class GetResponseEvent extends KernelEvent
{
    



    private $response;

    






    public function getResponse()
    {
        return $this->response;
    }

    






    public function setResponse(Response $response)
    {
        $this->response = $response;

        $this->stopPropagation();
    }

    






    public function hasResponse()
    {
        return null !== $this->response;
    }
}
