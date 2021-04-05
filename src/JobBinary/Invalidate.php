<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Invalidate - NULL
	 * Sends null bytes to the printer to reset it.
	 */
	class Invalidate extends JobBinary {
		private int $count;

		/**
		 * @param int $count How many reset bytes to send.
		 */
		public function __construct(int $count = 100) {
			$this->count = $count;
		}

		public function getBinary(): String {
			$data = '';
			for ($i = 0; $i < $this->count; $i++) {
				$data .= chr(0x00);
			}
			return $data;
		}
	}
