<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class ArgumentRepresentations {
	const STRING = 1;
	const VARIABLE = 2;
	const VALID_REPRESENTATIONS_COUNT = 2;

	/**
	 * @throws InvalidArgumentException
	 */
	static function assertIsValid( int $representation ): void {
		if ( $representation > self::VALID_REPRESENTATIONS_COUNT || $representation < 0 ) {
			throw new InvalidArgumentException( "Unknown representation: $representation" );
		}
	}
}
