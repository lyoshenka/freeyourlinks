<?php










namespace Symfony\Component\HttpFoundation\File\Exception;






class FileNotFoundException extends FileException
{
    




    public function __construct($path)
    {
        parent::__construct(sprintf('The file "%s" does not exist', $path));
    }
}
