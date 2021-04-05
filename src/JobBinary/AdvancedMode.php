<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Advanced mode settings - ESC i K
	 */
	class AdvancedMode extends JobBinary {
		private bool $halfCut;
		private bool $chainPrinting;

		/**
		 * @param bool $halfCut Enable half-cut. (Default: true)
		 * @param bool $chainPrinting Enable chain printing when printing multiple pages. (Default: true)
		 */
		public function __construct(bool $halfCut = true, bool $chainPrinting = true) {
			$this->halfCut = $halfCut;
			$this->chainPrinting = $chainPrinting;
		}

		public function getBinary(): String {
			$val = 0;
			$val += (0 << 0); // Not Used
			$val += (0 << 1); // Not Used
			$val += ($this->halfCut << 2); // Half Cut (1 == yes, 0 == no)
			$val += (!$this->chainPrinting << 3); // Chain Printing (1 = no, 0 == yes) (This param is really "nochain")
			$val += (0 << 4); // Special Tape (1 == on, 0 == off)
			$val += (0 << 5); // Not Used
			$val += (0 << 6); // High Resolution
			$val += (0 << 7); // No buffer cleaning.

			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x4B); // K
			$data .= chr($val); // {n1}
			return $data;
		}
	}
