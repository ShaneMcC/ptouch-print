<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

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

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x21); // !
			$data .= chr($this->enabled ? 0 : 1); // {n1} - 0: Notify. (default), 1: Do not notify.
			return $data;
		}
	}
