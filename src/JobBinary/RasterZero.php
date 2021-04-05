<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Print command - FF
	 * This is used in between pages to show the start of a new page.
	 */
	class RasterZero extends JobBinary {
		private int $compressionMode;

		/**
		 * @param int $compressionMode
		 */
		public function __construct(int $compressionMode) {
			$this->compressionMode = $compressionMode;
		}

		public function getBinary(): String {
			if ($this->compressionMode == CompressionMode::TIFF) {
				return chr(0x5A); // Z
			} else {
				return (new RasterLine(array_fill(0, 16, 0), CompressionMode::NONE))->getBinary();
			}
		}
	}
