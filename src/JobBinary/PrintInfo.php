<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Print information command - ESC i z
	 * Print information.
	 */
	class PrintInfo extends JobBinary {
		private int $tapeSize;
		private int $rasterSize;

		/**
		 * @param int $tapeSize Tape Size to require, in mm.
		 * @param int $rasterSize Length of print. Doesn't actually seem to be needed.
		 */
		public function __construct(int $tapeSize = 12, int $rasterSize = 0) {
			$this->tapeSize = $tapeSize;
			$this->rasterSize = $rasterSize;
		}

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x7A); // z
			$data .= chr(($this->tapeSize != 0 ? 0x04 : 0x00) + 0x80); // {n1} - PI_WIDTH + PI_RECOVER
			$data .= chr(0x00); // {n2} - Media Type (Unused)
			$data .= chr($this->tapeSize); // {n3} - Media Width (mm)
			$data .= chr(0x00); // {n4} - Media Length (mm) (Unused)
			$data .= pack('V*', $this->rasterSize); // {n5} {n6} {n7} {n8}
			$data .= chr(0x00); // {n9} - Starting Page (0 = first, 1 = middle, 2 = last)
			$data .= chr(0x00); // {n10} - 0
			return $data;
		}
	}
