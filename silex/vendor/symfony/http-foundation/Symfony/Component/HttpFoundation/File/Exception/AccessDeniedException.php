<?php










namespace Symfony\Component\HttpFoundation\File\Exception;






class AccessDeniedException extends FileException
{
    




    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}
