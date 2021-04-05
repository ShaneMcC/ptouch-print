<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Various mode settings - ESC i M
	 */
	class Mode extends JobBinary {
		private bool $autoCut;
		private bool $mirror;

		/**
		 * @param bool $autoCut Enable AutoCut. (Default: true)
		 * @param bool $mirror Mirror Printing. (Default: true)
		 */
		public function __construct(bool $autoCut = true, bool $mirror = false) {
			$this->autoCut = $autoCut;
			$this->mirror = $mirror;
		}

		public function getBinary(): String {
			$val = 0;
			$val += (0 << 0); // Not Used
			$val += (0 << 1); // Not Used
			$val += (0 << 2); // Not Used
			$val += (0 << 3); // Not Used
			$val += (0 << 4); // Not Used
			$val += (0 << 5); // Not Used
			$val += ($this->autoCut << 6); // Auto Cut (1 == yes, 0 == no)
			$val += ($this->mirror << 7); // Mirror (1 = no, 0 == yes)

			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x4D); // M
			$data .= chr($val); // {n1}
			return $data;
		}
	}
