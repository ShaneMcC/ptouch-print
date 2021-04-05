<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\Drawable;
	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Print command - FF
	 * This is used in between pages to show the start of a new page.
	 */
	class RasterZero extends JobBinary implements Drawable {
		private int $compressionMode;

		/**
		 * @param int $compressionMode
		 */
		public function __construct(int $compressionMode) {
			$this->compressionMode = $compressionMode;
		}

		public static function getMagic(): array {
			return [0x5A]; // Z
		}

		public static function getName(): string {
			return 'Zero Graphics Transfer';
		}

		public static function argCount(): int {
			return 0;
		}

		public  function getBinary(): String {
			if ($this->compressionMode == CompressionMode::TIFF) {
				return static::getMagicString();
			} else {
				return (new RasterLine(array_fill(0, 16, 0), CompressionMode::NONE))->getBinary();
			}
		}

		public static function draw(array $args, int $compressionMode): string {
			return str_repeat(' ', 128);
		}
	}
