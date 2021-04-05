<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

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

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x64); // d
			$data .= pack('v*', $this->marginSize); // {n1} {n2}
			return $data;
		}
	}
