<?php declare(strict_types=1);



namespace Observability\Client;




class Metrics
{
	private static $timings = array();


	private function __construct() {}


	public static function output( $metricName, $metricValue, array $tags = array() ) {
		$params = array();

		$params['metric_name']  = $metricName;
		$params['metric_value'] = $metricValue;

		Core\Core::outputMetric( $params, $tags );
	}


	public static function startTiming( $timingLabel, array $tags = array() ) {
		self::$timings[ $timingLabel ] = microtime( true );

		$params = array();

		$params['label']        = $timingLabel;
		$params['metric_value'] = self::$timings[ $timingLabel ];

		Core\Core::startTiming( $params, $tags );
	}


	public static function outputTiming( $timingLabel, $metricName = '', array $tags = array(), $description = '' ) {
		if ( array_key_exists( $timingLabel, self::$timings ) ) {
			$params = array();
			if ( $description ) {
				$params['output'] = $description;
			}

			$params['label'] = $timingLabel;

			if ( $metricName ) {
				$params['metric_name'] = $metricName;
			} else {
				$params['metric_name'] = $timingLabel;
			}

			$params['metric_value'] = microtime( true ) - self::$timings[ $timingLabel ];

			Core\Core::outputTiming( $params, $tags );
		}

	}


}
