<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris\Signals;

/**
 * Assist on the management of signals send at a global scope
 * thru the whole system.
 */
class SignalHandler
{

/**
 * Flag that indicates if listeners need sorting.
 * @var bool
 */
    private static $listenersSorted = false;

    /**
     * Flag that indicates if listeners with params need sorting.
     * @var bool
     */
    private static $listenersWithParamsSorted = false;

    /**
     * @var array
     */
    private static $listeners = [];

    /**
     * @var array
     */
    private static $listenersWithParams = [];

    /**
     * Disable constructor
     */
    private function __construct()
    {
    }

    /**
     * Calls all callbacks listening for a given signal type.
     * The $var1-$var6 are optional parameters passed to the callback.
     *
     * @param string $signal_type
     * @param \Jaris\Signals\SignalData|null $signal_data
     */
    public static function Send(
    string $signal_type,
    ?\Jaris\Signals\SignalData &$signal_data = null
): void {
        if (!isset(self::$listeners[$signal_type])) {
            return;
        }

        if (!self::$listenersSorted) {
            self::$listeners[$signal_type] = \Jaris\Data::Sort(
            self::$listeners[$signal_type],
            'priority'
        );

            self::$listenersSorted = true;
        }

        foreach (self::$listeners[$signal_type] as $callback_data) {
            $callback = $callback_data['callback'];

            if (is_object($signal_data)) {
                $callback($signal_data);
            } else {
                $callback();
            }
        }
    }

    /**
     * Add a callback that listens to a specific signal.
     *
     * @param string $signal_type
     * @param callable $callback
     * @param int $priority
     */
    public static function Listen(
    string $signal_type,
    callable $callback,
    int $priority = 20
): void {
        if (!isset(self::$listeners[$signal_type])) {
            self::$listeners[$signal_type] = [];
        }

        self::$listeners[$signal_type][] = [
        'callback' => $callback,
        'priority' => $priority
    ];

        self::$listenersSorted = false;
    }

    /**
     * Remove a callback from listening a given signal type.
     *
     * @param string $signal_type
     * @param callable $callback
     */
    public static function Unlisten(string $signal_type, callable $callback): void
    {
        if (!isset(self::$listeners[$signal_type])) {
            return;
        }

        if (is_array(self::$listeners[$signal_type])) {
            foreach (self::$listeners[$signal_type] as $position => $callback_data) {
                $stored_callback = $callback_data['callback'];

                if ($callback == $stored_callback) {
                    unset(self::$listeners[$signal_type][$position]);
                    break;
                }
            }
        }

        if (count(self::$listeners[$signal_type]) <= 0) {
            unset(self::$listeners[$signal_type]);
        }
    }

    /**
     * Calls all callbacks with params listening for a given signal type.
     * The $var1-$var6 are optional parameters passed to the callback.
     *
     * @param string $signal_type
     * @param mixed $var1 Optional argument passed to the callback.
     * @param mixed $var2 Optional argument passed to the callback.
     * @param mixed $var3 Optional argument passed to the callback.
     * @param mixed $var4 Optional argument passed to the callback.
     * @param mixed $var5 Optional argument passed to the callback.
     */
    public static function sendWithParams(
    string $signal_type,
    &$var1 = "null",
    &$var2 = "null",
    &$var3 = "null",
    &$var4 = "null",
    &$var5 = "null"
): void {
        if (!isset(self::$listenersWithParams[$signal_type])) {
            return;
        }

        if (!self::$listenersWithParamsSorted) {
            foreach (self::$listenersWithParams as $signal_t => $signals) {
                self::$listenersWithParams[$signal_t] = \Jaris\Data::sort(
                $signals,
                'priority'
            );
            }

            self::$listenersWithParamsSorted = true;
        }

        foreach (self::$listenersWithParams[$signal_type] as $callback_data) {
            $callback = $callback_data['callback'];

            if (
            $var1 !== "null" && $var2 !== "null" && $var3 !== "null" &&
            $var4 !== "null" && $var5 !== "null"
        ) {
                $callback($var1, $var2, $var3, $var4, $var5);
            } elseif (
            $var1 !== "null" && $var2 !== "null" && $var3 !== "null" &&
            $var4 !== "null"
        ) {
                $callback($var1, $var2, $var3, $var4);
            } elseif ($var1 !== "null" && $var2 !== "null" && $var3 !== "null") {
                $callback($var1, $var2, $var3);
            } elseif ($var1 !== "null" && $var2 !== "null") {
                $callback($var1, $var2);
            } elseif ($var1 !== "null") {
                $callback($var1);
            } else {
                $callback();
            }
        }
    }

    /**
     * Add a callback with params that listens to a specific signal.
     *
     * @param string $signal_type
     * @param callable $callback
     * @param int $priority
     */
    public static function listenWithParams(
    string $signal_type,
    callable $callback,
    int $priority = 20
): void {
        if (!isset(self::$listenersWithParams[$signal_type])) {
            self::$listenersWithParams[$signal_type] = [];
        }

        self::$listenersWithParams[$signal_type][] = [
        'callback' => $callback,
        'priority' => $priority
    ];

        self::$listenersWithParamsSorted = false;
    }

    /**
     * Remove a params callback from listening a given signal type.
     *
     * @param string $signal_type
     * @param callable $callback
     */
    public static function unlistenWithParams(
    string $signal_type,
    callable $callback
): void {
        if (!isset(self::$listenersWithParams[$signal_type])) {
            return;
        }

        if (is_array(self::$listenersWithParams[$signal_type])) {
            foreach (
            self::$listenersWithParams[$signal_type] as
            $position => $callback_data
        ) {
                $stored_callback = $callback_data['callback'];

                if ($callback == $stored_callback) {
                    unset(self::$listenersWithParams[$signal_type][$position]);
                    break;
                }
            }
        }

        if (count(self::$listenersWithParams[$signal_type]) <= 0) {
            unset(self::$listenersWithParams[$signal_type]);
        }
    }
}
