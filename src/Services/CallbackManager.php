<?php

namespace Popplestones\Quickbooks\Services;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;
use stdClass;

/**
 * @method void registerCustomers(Closure $query, Closure $filter)
 * @method void registerAccounts(Closure $query, Closure $filter)
 * @method void registerItems(Closure $query, Closure $filter)
 */
class CallbackManager
{
    // Allowed virtual methods.
    private $functions = [
        'customers',
        'accounts',
        'items'
    ];

    public function __construct()
    {
        $this->callbacks = collect();
    }

    public function __call($method, $args)
    {
        $type = (string)str($method)->remove('register')->lower();

        //Allow virtual public methods to be called.
        if (!in_array($type, $this->functions)) {
            throw new BadMethodCallException();
        }

        $this->callVirtualMethod($type, $args);
    }

    protected function callVirtualMethod($type, $args): void
    {
        $this->callbacks = $this->callbacks->mergeRecursive([
            $type => [
                'query'  => $args[0],
                'filter' => $args[1],
            ]
        ]);
    }

    public function getCallbacks(string $type): stdClass
    {
        if(!$this->callbacks->has($type))
        {
            throw new InvalidArgumentException(
                'Required callback must be registered: '. (string)str($type)->title()->prepend('register')
            );
        }
        return (object) $this->callbacks->get($type);
    }
}