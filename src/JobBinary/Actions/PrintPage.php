<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Print command - FF
	 * This is used in between pages to show the start of a new page.
	 */
	class PrintPage extends JobBinary {

		public static function getMagic(): array {
			return [0x0C]; // FF
		}

		public static function getName(): string {
			return 'Print Page';
		}

		public static function argCount(): int {
			return 0;
		}
	}
