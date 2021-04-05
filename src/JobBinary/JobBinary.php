<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	abstract class JobBinary {
		/**
		 * @return String Binary string for this part of the job.
		 */
		public abstract function getBinary();
	}
