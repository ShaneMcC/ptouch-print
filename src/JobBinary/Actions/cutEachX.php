<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Specify cut-each-X-labels mode - ESC i A
	 */
	class cutEachX extends JobBinary {
		private int $page;

		/**
		 * @param int $page Page to cut after. (Default: 1)
		 */
		public function __construct(int $page = 1) {
			$this->page = $page;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x41]; // ESC i A
		}

		public static function getName(): string {
			return 'Cut every X';
		}

		public static function argCount(): int {
			return 1;
		}

		public function getBinary(): String {
			$data = static::getMagicString();
			$data .= chr($this->page); // {n1}

			return $data;
		}

		public static function decodeBinary(array $args): String {
			return '(' . hexdec($args[0]) . ')';
		}
	}
