<?php

	namespace ShaneMcC\PTouchPrint;

	/**
	 * Convert a PNG Image into a brother ptouch-compatible binary.
	 *
	 * The image should be horizontal as you want it to come out of the
	 * printer, no more than 128 pixels tall (Smaller for shorter tape) and as
	 * long as needed.
	 */
	class RasterImage {
		private array $lines;
		private bool $mirrored;

		/**
		 * Create a new RasterImage from an image file.
		 *
		 * @param String $imageFile Path to image file
		 * @param int $maxPixels Max pixels for image height. Leave at default mostly. (Default: 128)
		 * @param bool $storeMirrored Store the image flipped. Leave at default mostly. (Default: true)
		 */
		public function __construct(String $imageFile, $maxPixels = 128, $storeMirrored = true) {
			// Storage mode.
			$this->mirrored = $storeMirrored;

			// The image lines.
			$this->lines = [];

			// Load the image.
			$original = imagecreatefrompng($imageFile);

			// Convert to greyscale
			imagefilter($original, IMG_FILTER_GRAYSCALE);
			imagefilter($original, IMG_FILTER_CONTRAST, -100);

			// Rotate into the orientation we need it for printing.
			$image = imagerotate($original, 270, 0);
			imagedestroy($original);

			$width = imagesx($image);
			$height = imagesy($image);

			// Ensure we print in the center of the label.
			$offset = ($maxPixels / 2) - ($width / 2);
			$lineSize = $maxPixels / 8;

			// Top to bottom
			for ($y = 0; $y < $height; $y++) {
				// New Line
				$line = array_fill(0, $lineSize, 0);

				// Left to right
				for ($x = 0; $x < $width; $x++) {
					// Check what colour this pixel is.
					$rgb = imagecolorat($image, $x, $y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					// We only support on or off, so if any of the pixels RGB
					// values are more than "half" on, then we'll treat this as
					// a pixel to print
					$pixelOn = ($r > 128 || $g > 128 || $b > 128) ? false : true;

					if ($pixelOn) {
						// Where in our output do we actually want to put this
						// pixel.
						$pixelPos = $x + $offset;

						if ($this->mirrored) {
							// The image is stored reversed for actually sending to
							// the printer as it reads right to left.
							$line[($lineSize - 1) - floor($pixelPos / 8)] += (1 << ($pixelPos % 8));
						} else {
							// Non-reversed
							$line[floor($pixelPos / 8)] += (1 << (7 - ($pixelPos % 8)));
						}
					}
				}

				// Add this line to our lines.
				$this->lines[] = $line;
			}

			imagedestroy($image);
		}

		/**
		 * Get the lines we need to send.
		 *
		 * @return array Array of lines representing this image
		 */
		public function getLines(): array {
			return $this->lines;
		}

		/**
		 * How wide is this image?
		 *
		 * @return int Width of this image.
		 */
		public function getWidth(): int {
			return count($this->lines[0]) * 8;
		}

		/**
		 * How long is this image?
		 *
		 * @return int Length of this image.
		 */
		public function getLength(): int {
			return count($this->lines);
		}

		/**
		 * Is this image stored mirrored?
		 *
		 * @return bool Are we mirrored internally?
		 */
		public function isMirrored(): bool {
			return $this->mirrored;
		}

		/**
		 * Utility function for displaying the image to the CLI.
		 */
		public function displayImage() {
			echo '┌', str_repeat('─', $this->getWidth()), '┐', "\n";
			foreach ($this->lines as $line) {
				echo '│', RasterImage::getLineForDisplay($line, $this->mirrored), '│', "\n";
			}
			echo '└', str_repeat('─', $this->getWidth()), '┘', "\n";
		}

		/**
		 * Get a single line of data as a String suitable for displaying what the line looks like.
		 *
		 * @param array $line Line to display
		 * @param bool $isMirrored Is this line stored Mirrored?
		 * @return String String representation of line.
		 */
		public static function getLineForDisplay(array $line, bool $isMirrored = true): String {
			$result = '';
			foreach (($isMirrored ? array_reverse($line) : $line) as $bit) {
				$bin = decbin($bit);
				if ($isMirrored) { $bin = strrev($bin); }
				$bin = str_pad($bin, 8, '0', $isMirrored ? STR_PAD_RIGHT : STR_PAD_LEFT);
				$result .= str_replace('1', '█', str_replace('0', ' ', $bin));
			}
			return $result;
		}
	}
