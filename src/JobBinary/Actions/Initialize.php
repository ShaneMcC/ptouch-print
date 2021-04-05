<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Initialize - ESC @
	 * Sends initialization string to start the job.
	 */
	class Initialize extends JobBinary {
		public static function getMagic(): array {
			return [0x1B, 0x40]; // ESC @
		}

		public static function getName(): string {
			return 'Initialize';
		}

		public static function argCount(): int {
			return 0;
		}
	}
