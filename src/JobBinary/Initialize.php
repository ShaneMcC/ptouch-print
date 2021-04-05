<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Initialize - ESC @
	 * Sends initialization string to start the job.
	 */
	class Initialize {
		public function getBinary(): String {
			$data = '';
			$data .= chr(0x1B); // ESC
			$data .= chr(0x40); // @

			return $data;
		}
	}
