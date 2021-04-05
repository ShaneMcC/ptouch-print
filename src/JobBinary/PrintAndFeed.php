<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Print with feeding - Control-Z
	 * This is used at the end of the job.
	 */
	class PrintAndFeed extends JobBinary {
		public function getBinary(): String {
			return chr(0x1A); // ^Z
		}
	}
