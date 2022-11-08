<?php

require_once '../vendor/autoload.php';

use Src\Container;

interface InterfaceA
{
}
interface InterfaceB
{
}
class ClassA
{
    public function __construct(InterfaceB $classB)
    {   
    }
}
class ClassB implements InterfaceB
{
    public function __construct()
    {       
    }
}

$container = new Container();

$container->bind(InterfaceB::class, ClassB::class);
$container->bind(InterfaceA::class, fn($container) => new ClassA($container->get(InterfaceB::class)));

var_dump($container->get(InterfaceA::class));