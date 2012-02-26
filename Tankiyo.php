<?php

class Tankiyo {

	protected static $connection = null;

	public static function getConnection() {
		if (self::$connection) {
			return self::$connection;
		}
		$dsn = getenv('CLEARDB_DATABASE_URL_A');
		$dsn = preg_replace('/([a-z0-9])\/([a-z0-9])/', '${1};dbname=${2}', $dsn);
		$matches = array();
		preg_match('/([a-zA-Z0-9]*):([a-zA-Z0-9]*)@/', $dsn, $matches);
		$dsn = preg_replace('/([a-zA-Z0-9]*):([a-zA-Z0-9]*)@/', '', $dsn);
		$dsn = preg_replace('/mysql:\/\//', 'mysql:host=', $dsn);

		self::$connection = new PDO(
			$dsn,
			$matches[1],
			$matches[2]
		);
		return self::$connection;
	}

	public static function getDates($date = null) {
		$return_dates = array();
		if (is_null($date)) {
			$date = date('Y-m-d',time());
		}

		$date_object = new DateTime();
		$date_object->setTimestamp(strtotime($date));

		$steps = array(1,3,5,8);
		while ( count($return_dates) < 4) {
			if ($date_object->format('N') == 7) {
				$date_object->add(DateInterval::createFromDateString('2 day'));
			} else if ($date_object->format('N') == 1) {
				$date_object->add(DateInterval::createFromDateString('1 day'));
			}
			$return_dates[] = $date_object->format('Y-m-d');
			$step = array_shift($steps);
			$date_object->add(DateInterval::createFromDateString($step.' day'));
		}

		return $return_dates;
	}

	public static function format_date($date) {
		$date_object = new DateTime();
		$date_object->setTimestamp(strtotime($date));
		$week = array('日','月','火','水','木','金','土');
		return $date_object->format('Y年m月d日').' '.$week[$date_object->format('w')].'曜日';
	}

	public static function getParties() {
		$dbh = self::getConnection();
		$data = array();
		foreach($dbh->query('SELECT * from parties') as $row) {
			$data[date('Y-m-d',strtotime($row['party_date']))] = $row;
		}
		return $data;
	}

	public static function createParty($date) {
		$dbh = self::getConnection();
		$dbh->query(
			sprintf(
				'INSERT INTO parties set party_date = %s',
				$dbh->quote($date)
			)
		);

		return $dbh->lastInsertId();
	}

	public static function joinParty($user_id,$name,$party_id) {
		$dbh = self::getConnection();
		return $dbh->query(
			sprintf(
				'INSERT INTO attendees set party_id = %s, name = %s, user_id = %s',
				$dbh->quote($party_id),
				$dbh->quote($name),
				$dbh->quote($user_id)
			)
		);
	}

	public static function getAttendees($party_ids) {
		$dbh = self::getConnection();
		$data = array();
		$sql = sprintf(
			'SELECT * from attendees where party_id in (%s) ORDER BY party_id, id ASC',
			implode(',',$party_ids)
		);
		if (empty($party_ids)) {
			return array();
		}
		foreach($dbh->query($sql) as $row) {
			if (!isset($data[$row['party_id']])) {
				$data[$row['party_id']] = array();
			}
			$data[$row['party_id']][$row['user_id']] = $row;
		}
		return $data;
	}
}
