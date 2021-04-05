<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

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

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x41); // A
			$data .= chr($this->page); // {n1}

			return $data;
		}
	}
