<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Specify margin (feed amount) - ESC i d
	 */
	class Margin extends JobBinary {
		private int $marginSize;

		/**
		 * @param int $marginSize Number of lines for margin (Default: 10)
		 */
		public function __construct(int $marginSize = 10) {
			$this->marginSize = $marginSize;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x64]; // ESC i d
		}

		public static function getName(): string {
			return 'Margin';
		}

		public static function argCount(): int {
			return 2;
		}

		public  function getBinary(): String {
			$data = static::getMagicString();
			$data .= pack('v*', $this->marginSize); // {n1} {n2}
			return $data;
		}

		public static function decodeBinary(array $args): String {
			$packed = chr($args[0]) . chr($args[1]);
			return '(' . unpack('v', $packed)[1] . ' lines)';
		}
	}
