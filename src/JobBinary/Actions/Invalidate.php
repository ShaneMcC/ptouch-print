<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;
	use ShaneMcC\PTouchPrint\JobBinary\Repeatable;

	/**
	 * Invalidate - NULL
	 * Sends null bytes to the printer to reset it.
	 */
	class Invalidate extends JobBinary implements Repeatable {
		private int $count;

		/**
		 * @param int $count How many reset bytes to send.
		 */
		public function __construct(int $count = 100) {
			$this->count = $count;
		}

		public static function getMagic(): array {
			return [0x00]; // NULL
		}

		public static function getName(): string {
			return 'Invalidate';
		}

		public static function argCount(): int {
			return 0;
		}

		public function getBinary(): String {
			$data = static::getMagicString();
			for ($i = 1; $i < $this->count; $i++) {
				$data .= static::getMagicString();
			}
			return $data;
		}
	}
