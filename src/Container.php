<?php

namespace Src;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;
use Src\Exceptions\BuiltinTypeException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\NotInstantiableException;
use Src\Exceptions\UndefinedTypeException;
use Src\Exceptions\UnionTypeException;
use Src\Interfaces\ContainerInterface;

/**
 * Class Container
 * 
 * @package \Src
 */
class Container implements ContainerInterface
{
    /**
     * @var array $entries
     */
    protected array $entries = [];

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id): mixed
    {
        if ($this->has($id)) {

            $entry = $this->entries[$id];

            if ($entry instanceof Closure) {
                /**
                 * Execute the Closure and if it returns object than return it
                 * else resolve the entry
                 */
                $entry = $entry($this);

                if (is_object($entry)) {
                    return $entry;
                }
            }

            $id = $entry;
        }

        return $this->resolve($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return key_exists($id, $this->entries);
    }

    /**
     * Set an entry to the container
     * 
     * @param string $id
     * @param string|callable $concrete
     * 
     * @return void
     */
    protected function set(string $id, string|callable $concrete): void
    {
        $this->entries[$id] = $concrete;
    }

    /**
     * Bind the entries to the container
     * 
     * @param string $id
     * @param string|callable|null $concrete
     * 
     * @return void
     */
    public function bind(string $id, string|callable|null $concrete = null): void
    {
        /**
         * Droping the entry if it exists
         */
        $this->dropStaleEntries($id);

        if (is_null($concrete)) {
            $concrete = $id;
        }

        $this->set($id, $concrete);
    }

    /**
     * Drop the stale entries
     * 
     * @param string $id
     * 
     * @return void
     */
    public function dropStaleEntries(string $id): void
    {
        if ($this->has($id)) {
            unset($this->entries[$id]);
        }
    }

    /**
     * Resolve the concrete
     * 
     * @param string $id
     * 
     * @return mixed
     * 
     * @throws NotFoundException
     * @throws NotInstantiableException
     */
    protected function resolve(string $id): mixed
    {
        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new NotInstantiableException("[$id] is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $parameters = $constructor->getParameters();

        if (!$parameters) {
            return $reflectionClass->newInstance();
        }

        // If the entry has parameters than shound to resolve them
        $dependencies[] = array_map('$this->resolveParameter', $parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * Resolve the parameter
     * 
     * @param ReflectionParameter $parameters
     * 
     * @return mixed
     * 
     * @throws UndefinedTypeException
     * @throws UnionTypeException
     * @throws BuiltinTypeException
     */
    protected function resolveParameter(ReflectionParameter $parameter): mixed
    {

        $name = $parameter->getName();
        $type = $parameter->getType();

        if (is_null($type)) {
            throw new UndefinedTypeException("Parameter [$name] with a undefined type");
        }

        if ($type instanceof ReflectionUnionType) {
            throw new UnionTypeException("Parameter [$name] with a union type");
        }

        if ($type->isBuiltin()) {
            throw new BuiltinTypeException("Parameter [$name] with a builtin type");
        }

        return $this->get($type->getName());
    }
}
