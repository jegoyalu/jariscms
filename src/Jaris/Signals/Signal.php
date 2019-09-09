<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris\Signals;

/**
 * Signal management that can be implemented at a per object basic.
 */
class Signal
{

/**
 * @var array
 */
private $listeners;

/**
 * Default constructor
 */
public function __construct()
{
    $this->listeners = array();
}

/**
 * Calls all callbacks listening for a given signal type.
 *
 * @param string $signal_type
 * @param \Jaris\Signals\SignalData|null $signal_data
 */
public function Send(
    string $signal_type, ?\Jaris\Signals\SignalData &$signal_data=null
): void
{
    if(!isset($this->listeners[$signal_type]))
        return;

    foreach($this->listeners[$signal_type] as $callback_data)
    {
        $callback = $callback_data['callback'];

        if(is_object($signal_data))
            $callback($signal_data);
        else
            $callback();
    }
}

/**
 * Add a callback that listens to a specific signal.
 *
 * @param string $signal_type
 * @param callable $callback
 * @param int $priority
 */
public function Listen(
    string $signal_type, callable $callback, int $priority=20
): void
{
    if(!isset($this->listeners[$signal_type]))
        $this->listeners[$signal_type] = array();

    $this->listeners[$signal_type][] = array(
        'callback'=>$callback,
        'priority'=>$priority
    );

    $this->listeners[$signal_type] = \Jaris\Data::Sort(
        $this->listeners[$signal_type], 'priority'
    );
}

/**
 * Remove a callback from listening a given signal type.
 *
 * @param string $signal_type
 * @param callable $callback
 */
public function Unlisten(string $signal_type, callable $callback): void
{
    if(!isset($this->listeners[$signal_type]))
        return;

    foreach($this->listeners[$signal_type] as $position=>$callback_data)
    {
        $stored_callback = $callback_data['callback'];

        if($callback == $stored_callback)
        {
            unset($this->listeners[$signal_type][$position]);
            return;
        }
    }
}

}