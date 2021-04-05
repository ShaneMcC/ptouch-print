<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Select compression mode - M
	 * This subtly changes what we send, but we don't actually support compression just yet
	 * and still just send a full 16 bytes per line.
	 */
	class CompressionMode extends JobBinary {
		private int $compressionMode;

		public const NONE = 0;
		public const TIFF = 2;

		/**
		 * @param int $compressionMode Mode for compression of raster data.
		 *                  CompressionMode::NONE or CompressionMode::COMPRESSION_TIFF
		 */
		public function __construct(int $compressionMode = CompressionMode::NONE) {
			$this->compressionMode = $compressionMode;
		}

		public function getBinary(): String {
			$data = '';
			$data .= chr(0x4D); // m
			$data .= chr($this->compressionMode); // {n} = 0 == None, 2 == TIFF
			return $data;
		}
	}
