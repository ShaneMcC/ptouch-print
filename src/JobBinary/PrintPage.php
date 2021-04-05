<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Print command - FF
	 * This is used in between pages to show the start of a new page.
	 */
	class PrintPage extends JobBinary {

		public function getBinary(): String {
			return chr(0x0C); // FF
		}
	}
