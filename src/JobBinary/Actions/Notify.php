<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Switch automatic status notification mode - ESC i !
	 * We use this to turn status notifications off.
	 */
	class Notify extends JobBinary {
		private bool $enabled;

		/**
		 * @param bool $enabled Should status notifications be on or off?
		 */
		public function __construct(bool $enabled) {
			$this->enabled = $enabled;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x21]; // ESC i !
		}

		public static function getName(): string {
			return 'Automatic Status Notification';
		}

		public static function argCount(): int {
			return 1;
		}

		public  function getBinary(): String {
			$data = static::getMagicString();
			$data .= chr($this->enabled ? 0 : 1); // {n1} - 0: Notify. (default), 1: Do not notify.
			return $data;
		}

		public static function decodeBinary(array $args): String {
			switch ($args[0]) {
				case 0:
					return '(Notify (Default))';
				case 1:
					return '(Do not notify)';
				default:
					return '(Unknown)';
			}
		}
	}
