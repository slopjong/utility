<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use \DateTime;
use \DateTimeZone;

/**
 * Time provides functionality for calculating date and time ranges and making timezones easy to use.
 *
 * @package Titon\Utility
 */
class Time {

	/**
	 * Time constants represented as seconds.
	 */
	const YEAR = 31536000;
	const MONTH = 2592000; // 30 days
	const WEEK = 604800;
	const DAY = 86400;
	const HOUR = 3600;
	const MINUTE = 60;
	const SECOND = 1;

	/**
	 * Calculate the difference in seconds between 2 dates.
	 *
	 * @param string|int $time1
	 * @param string|int $time2
	 * @return int
	 */
	public static function difference($time1, $time2) {
		return self::toUnix($time1) - self::toUnix($time2);
	}

	/**
	 * Return a DateTime object based on the current time and timezone.
	 *
	 * @param string|int $time
	 * @param string $timezone
	 * @return \DateTime
	 */
	public static function factory($time = null, $timezone = null) {
		return new DateTime($time, self::timezone($timezone));
	}

	/**
	 * Returns true if date passed is today.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function isToday($time) {
		return (date('Ymd', self::toUnix($time)) === date('Ymd'));
	}

	/**
	 * Returns true if date passed is within this week.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function isThisWeek($time) {
		return (date('Wo', self::toUnix($time)) === date('Wo'));
	}

	/**
	 * Returns true if date passed is within this month.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function isThisMonth($time) {
		return (date('mY', self::toUnix($time)) === date('mY'));
	}

	/**
	 * Returns true if date passed is within this year.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function isThisYear($time) {
		return (date('Y', self::toUnix($time)) === date('Y'));
	}

	/**
	 * Returns true if date passed is tomorrow.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function isTomorrow($time) {
		return (date('Ymd', self::toUnix($time)) === date('Ymd', strtotime('tomorrow')));
	}

	/**
	 * Returns true if the date passed will be within the next time frame span.
	 *
	 * @param string|int $time
	 * @param int $span
	 * @return bool
	 * static
	 */
	public static function isWithinNext($time, $span) {
		$time = self::toUnix($time);
		$span = self::toUnix($span);

		return ($time < $span && $time > time());
	}

	/**
	 * Return a DateTimeZone object based on the current timezone.
	 *
	 * @param string $timezone
	 * @return \DateTimeZone
	 */
	public static function timezone($timezone = null) {
		if (!$timezone) {
			$timezone = date_default_timezone_get();
		}

		return new DateTimeZone($timezone);
	}

	/**
	 * Return a unix timestamp. If the time is a string convert it, else cast to int.
	 *
	 * @param int|string $time
	 * @return int
	 */
	public static function toUnix($time) {
		if (!$time) {
			return time();

		} else if ($time instanceof DateTime) {
			return $time->format('U');
		}

		return is_string($time) ? strtotime($time) : (int) $time;
	}

	/**
	 * Returns true if date passed was within last week.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function wasLastWeek($time) {
		$start = strtotime('last week 00:00:00');
		$end = strtotime('next week -1 second', $start);
		$time = self::toUnix($time);

		return ($time >= $start && $time <= $end);
	}

	/**
	 * Returns true if date passed was within last month.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function wasLastMonth($time) {
		$start = strtotime('first day of last month 00:00:00');
		$end = strtotime('next month -1 second', $start);
		$time = self::toUnix($time);

		return ($time >= $start && $time <= $end);
	}

	/**
	 * Returns true if date passed was within last year.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function wasLastYear($time) {
		$start = strtotime('last year January 1st 00:00:00');
		$end = strtotime('next year -1 second', $start);
		$time = self::toUnix($time);

		return ($time >= $start && $time <= $end);
	}

	/**
	 * Returns true if date passed was yesterday.
	 *
	 * @param string|int $time
	 * @return bool
	 */
	public static function wasYesterday($time) {
		return (date('Ymd', self::toUnix($time)) === date('Ymd', strtotime('yesterday')));
	}

	/**
	 * Returns true if the date passed was within the last time frame span.
	 *
	 * @param string|int $time
	 * @param int $span
	 * @return bool
	 */
	public static function wasWithinLast($time, $span) {
		$time = self::toUnix($time);
		$span = self::toUnix($span);

		return ($time > $span && $time < time());
	}

}