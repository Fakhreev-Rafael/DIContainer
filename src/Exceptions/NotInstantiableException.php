<?php

namespace Src\Exceptions;

use Exception;
use Src\Interfaces\ContainerExceptionInterface;

/**
 * Class NotInstantiableException
 * 
 * @package \Src\Exceptions
 */
class NotInstantiableException extends Exception implements ContainerExceptionInterface
{

}