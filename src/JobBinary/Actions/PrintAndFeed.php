<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Print with feeding - Control-Z
	 * This is used at the end of the job.
	 */
	class PrintAndFeed extends JobBinary {
		public static function getMagic(): array {
			return [0x1A]; // ^Z
		}

		public static function getName(): string {
			return 'Print and Feed';
		}

		public static function argCount(): int {
			return 0;
		}
	}
