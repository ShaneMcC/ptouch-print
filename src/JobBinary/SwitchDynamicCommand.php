<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Switch dynamic command mode - ESC i a
	 * Switches the printer into raster mode.
	 * Technically other modes (ESC/P and P-touch Template) are supported
	 * by the device, but not by us.
	 */
	class SwitchDynamicCommand extends JobBinary {

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x69); // i
			$data .= chr(0x61); // a
			$data .= chr(0x01); // {n1} - 1 for raster mode
			return $data;
		}
	}
