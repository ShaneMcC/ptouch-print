<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 *  Classes that actually generate the binary data needed to print.
	 *
	 * Most of this comes from https://download.brother.com/welcome/docp100064/cv_pte550wp750wp710bt_eng_raster_101.pdf
	 * with clarifications from other sources:
	 *  - https://github.com/clarkewd/ptouch-print
	 *  - https://github.com/philpem/printer-driver-ptouch
	 */
	abstract class JobBinary {
		public abstract static function getMagic(): array;

		public static function getMagicString(): String {
			return array_reduce(static::getMagic(), fn($c, $i) => $c . chr($i), '');
		}

		public abstract static function getName(): String;
		public abstract static function argCount(): int;

		/**
		 * @return String Binary string for this part of the job.
		 */
		public function getBinary(): String {
			return static::getMagicString();
		}

		public static function decodeBinary(array $args): String {
			return '';
		}
	}
