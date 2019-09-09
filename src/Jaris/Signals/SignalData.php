<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris\Signals;

/**
 * Container of variable references that is passed to every signal listener.
 */
class SignalData
{

/**
 * Associative array with references to stored arguments.
 * @var array
 */
    public $arguments;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->arguments = [];
    }

    /**
     * Store a reference to a variable.
     *
     * @param string $name Name of variable.
     * @param mixed $value Current variable.
     */
    public function Add(string $name, &$value): void
    {
        $this->arguments[$name] = &$value;
    }

    /**
     * Override default getter so we can get stored references.
     *
     * @param string $name
     * @return mixed Returns null if property isn't found.
     */
    public function &__get(string $name)
    {
        if (!isset($this->arguments[$name])) {
            return null;
        }

        return $this->arguments[$name];
    }
}
