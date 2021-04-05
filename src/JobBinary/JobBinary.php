<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

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
