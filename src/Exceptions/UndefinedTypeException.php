<?php

namespace Src\Exceptions;

use Exception;
use Src\Interfaces\ContainerExceptionInterface;

/**
 * Class UndefinedTypeException
 * 
 * @package \Src\Exceptions
 */
class UndefinedTypeException extends Exception implements ContainerExceptionInterface
{
    
}