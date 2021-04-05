<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	/**
	 * Raster graphics transfer - G
	 * Sends a line of raster graphics.
	 * This does slightly different things if TIFF compression is enabled, but for the moment
	 * we still don't actually implement the RLE for this.
	 * We do however short-circuit out to `Z` if we have a full line of 0s
	 */
	class RasterLine extends JobBinary {
		private array $lineArr;
		private int $compressionMode;

		/**
		 * @param array $lineArr
		 * @param int $compressionMode
		 */
		public function __construct(array $lineArr, int $compressionMode) {
			$this->lineArr = $lineArr;
			$this->compressionMode = $compressionMode;
		}

		public function getBinary(): String {
			if ($this->compressionMode == CompressionMode::TIFF && array_sum($this->lineArr) == 0) {
				return (new RasterZero(CompressionMode::TIFF))->getBinary();
			}

			$data = '';
			$data .= chr(0x47); // G
			if ($this->compressionMode == CompressionMode::TIFF) {
				$data .= pack('v*', count($this->lineArr) + 1);
				$data .= chr(count($this->lineArr) - 1);
			} else {
				$data .= pack('v*', count($this->lineArr));
			}
			foreach ($this->lineArr as $l) {
				$data .= chr($l);
			}

			return $data;
		}
	}
