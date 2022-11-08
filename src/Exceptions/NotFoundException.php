<?php

namespace Src\Exceptions;

use Exception;
use Src\Interfaces\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * 
 * @package \Src\Exceptions
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    
}