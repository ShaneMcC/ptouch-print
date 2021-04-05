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
			$outBits = $this->lineArr;
			if ($this->compressionMode == CompressionMode::TIFF) {
				// Attempt to compress the line.
				$inBits = $outBits;
				$outBits = [];
				$group = [];
				for ($i = 0; $i < count($inBits); /* Incremented Internally */) {
					// Current bit.
					$bit = $inBits[$i];

					// Find how many times this repeats.
					$count = 1;
					while (isset($inBits[$i + 1]) && $inBits[$i + 1] == $bit) {
						$i++;
						$count++;
					}

					// If we repeated more than twice then we send the data as 2 bytes of <count> <data>
					if ($count > 2) {
						// If there was a previous group of non-repeating data, send it first.
						if (!empty($group)) {
							$outBits[] = (count($group) - 1);
							$outBits = array_merge($outBits, $group);
						}
						$group = [];

						// Now send our bits.
						$outBits[] = 0 - ($count - 1);
						$outBits[] = $bit;
					} else {
						// Otherwise, add it to the group an appropriate number of times.
						$group = array_merge($group, array_fill(0, $count, $bit));
					}
					$i++;
				}
				// Send the final bit of non-repeated data.
				if (!empty($group)) {
					$outBits[] = (count($group) - 1);
					$outBits = array_merge($outBits, $group);
				}

				// Shorter to just send the line as a consecutive set of characters.
				if (count($outBits) > count($inBits) + 1) {
					$outBits = $inBits;
					array_unshift($outBits, count($outBits) - 1);
				}
			}

			$data .= pack('v*', count($outBits));
			foreach ($outBits as $l) {
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
