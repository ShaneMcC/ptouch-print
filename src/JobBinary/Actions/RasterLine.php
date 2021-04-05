<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\Drawable;
	use ShaneMcC\PTouchPrint\JobBinary\DynamicLength;
	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Raster graphics transfer - G
	 * Sends a line of raster graphics.
	 * This does slightly different things if TIFF compression is enabled, but for the moment
	 * we still don't actually implement the RLE for this.
	 * We do however short-circuit out to `Z` if we have a full line of 0s
	 */
	class RasterLine extends JobBinary implements DynamicLength, Drawable {
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

		public static function getMagic(): array {
			return [0x47]; // G
		}

		public static function getName(): string {
			return 'Graphics Transfer';
		}

		public static function argCount(): int {
			// Technically more, but it's dynamic.
			return 2;
		}

		public  function getBinary(): String {
			if ($this->compressionMode == CompressionMode::TIFF && array_sum($this->lineArr) == 0) {
				return (new RasterZero(CompressionMode::TIFF))->getBinary();
			}

			$data = static::getMagicString();
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

		public static function getAdditionalArgCount(array $args): int {
			$packed = chr($args[0]) . chr($args[1]);
			return unpack('v', $packed)[1];
		}

		public static function decodeBinary(array $args): String {
			$nice = array_slice($args, 2);
			$nice = array_map(fn($i) => str_pad(dechex($i), 2, '0', STR_PAD_LEFT), $nice);

			$len = count($nice);
			return '(' . $len . ' bytes) [' . implode(' ', $nice) . ']';
		}

		public static function draw(array $args, int $compressionMode): String {
			if ($compressionMode == CompressionMode::TIFF) {
				for ($d = 2; $d < count($args); $d++) {
					$bytes = unpack('c', chr($args[$d]))[1];
					if ($bytes < 0) {
						$bytes = abs($bytes);
						$d++;
						for ($z = 0; $z <= $bytes; $z++) { $drawBits[] = $args[$d]; }
					} else {
						for ($z = 0; $z <= $bytes; $z++) { $drawBits[] = $args[++$d]; }
					}
				}
			} else {
				$drawBits = array_slice($args, 2);
			}

			$result = '';
			foreach (array_reverse($drawBits) as $bit) {
				$bin = str_pad(strrev(decbin($bit)), 8, '0', STR_PAD_RIGHT);
				$result .= str_replace('1', '█', str_replace('0', ' ', $bin));
			}

			return $result;
		}
	}
