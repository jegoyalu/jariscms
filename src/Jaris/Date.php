<?php
/**
 * @author Jefferson González <jgonzalez@jegoyalu.com>
 * @license https://opensource.org/licenses/GPL-3.0
 * @link http://github.com/jegoyalu/jariscms Source code.
 */

namespace Jaris;

/**
 * Helper functions for handling dates.
 */
class Date
{

/**
 * static function that returns a days of the month array ready
 * for selects on generate form functions.
 *
 * @return array A list of day numbers.
 */
static function getDays(): array
{
    $dates = array();

    for($i = 1; $i <= 31; $i++)
    {
        $dates[$i] = $i;
    }

    return $dates;
}

/**
 * static function that returns a months array ready for selects
 * on generate form functions.
 *
 * @return array A list of month days.
 */
static function getMonths(): array
{
    $months = array(
        t("January") => 1,
        t("February") => 2,
        t("March") => 3,
        t("April") => 4,
        t("May") => 5,
        t("June") => 6,
        t("July") => 7,
        t("August") => 8,
        t("September") => 9,
        t("October") => 10,
        t("November") => 11,
        t("December") => 12
    );

    return $months;
}

/**
 * static function that returns a years array ready for
 * selects on generate form functions.
 *
 * @param int $additional_years
 *
 * @return array A list of year numbers.
 */
static function getYears(int $additional_years=0): array
{
    $current_year = date("Y", time());
    $current_year += $additional_years;

    $years = array();

    for($i = 1900; $i <= $current_year; $i++)
    {
        $years[$i] = $i;
    }

    arsort($years);

    return $years;
}

/**
 * Get the amount of time in a easy to read human format.
 *
 * @param int $fromtimestamp Should be lower number than $totimestamp
 * @param int $totimestamp Should be higher number than $fromtimestamp
 * @param bool $ago Enables or disables the addition of ago predicate.
 *
 * @return string
 */
static function getElapsedTime(
    int $fromtimestamp, int $totimestamp=0, bool $ago = true
): string
{
    if($totimestamp == 0)
        $totimestamp = time();

    $etime = $totimestamp - $fromtimestamp;

    if($etime < 1)
    {
        return t('0 seconds');
    }

    $a = array(
        12 * 30 * 24 * 60 * 60 => array(t('year'), t('years')),
        30 * 24 * 60 * 60 => array(t('month'), t('months')),
        24 * 60 * 60 => array(t('day'), t('days')),
        60 * 60 => array(t('hour'), t('hours')),
        60 => array(t('minute'), t('minutes')),
        1 => array(t('second'), t('seconds'))
    );

    foreach($a as $secs => $labels)
    {
        $d = $etime / $secs;

        if($d >= 1)
        {
            $time = round($d);

            if($time > 1)
                $period = $labels[1];
            else
                $period = $labels[0];

            $output = t('{time} {period} ago');

            if(!$ago)
            {
                $output = t('{time} {period}');
            }

            return str_replace(
                array("{time}", "{period}"),
                array($time, $period),
                $output
            );
        }
    }
}

/**
 * Get the amount of days from one timestamp to the other.
 *
 * @param int $fromtimestamp Should be lower number than $totimestamp
 * @param int $totimestamp Should be higher number than $fromtimestamp
 *
 * @return int
 */
static function getElapsedDays(int $fromtimestamp, int $totimestamp=0): int
{
    if($totimestamp == 0)
        $totimestamp = time();

    $etime = $totimestamp - $fromtimestamp;

    if($etime < 1)
    {
        return 0;
    }

    $days = $etime / (24 * 60 * 60);

    if($days >= 1)
    {
        return (int) round($days);
    }

    return 0;
}

}