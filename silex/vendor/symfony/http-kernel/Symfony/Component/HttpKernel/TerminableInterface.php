<?php










namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;










interface TerminableInterface
{
    









    function terminate(Request $request, Response $response);
}
