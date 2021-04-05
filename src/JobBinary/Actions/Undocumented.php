<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;
	use Exception;
	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Undocumented command - ESC i U
	 */
	class Undocumented extends JobBinary {
		public function __construct() {
			throw new Exception('Undocumented can not be constructed.');
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x55]; // ESC i U
		}

		public static function getName(): string {
			return 'Undocumented Command';
		}

		public static function argCount(): int {
			return 15;
		}
	}
