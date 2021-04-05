<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

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

		public static function getMagic(): array {
			return [0x4D]; // m
		}

		public static function getName(): string {
			return 'Compression';
		}

		public static function argCount(): int {
			return 1;
		}

		public  function getBinary(): String {
			$data = static::getMagicString();
			$data .= chr($this->compressionMode); // {n} = 0 == None, 2 == TIFF
			return $data;
		}

		public static function decodeBinary(array $args): String {
			switch ($args[0]) {
				case 0:
					return '(None)';
				case 1:
					return '(Reserved)';
				case 2:
					return '(TIFF)';
				default:
					return '(Unknown)';
			}
		}
	}
