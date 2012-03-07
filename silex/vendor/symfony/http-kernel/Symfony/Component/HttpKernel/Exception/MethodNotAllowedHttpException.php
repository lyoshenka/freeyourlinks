<?php










namespace Symfony\Component\HttpKernel\Exception;






class MethodNotAllowedHttpException extends HttpException
{
    







    public function __construct(array $allow, $message = null, \Exception $previous = null, $code = 0)
    {
        $headers = array('Allow' => strtoupper(implode(', ', $allow)));

        parent::__construct(405, $message, $previous, $headers, $code);
    }
}
