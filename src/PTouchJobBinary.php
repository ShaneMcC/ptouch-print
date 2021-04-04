<?php

	namespace ShaneMcC\PTouchPrint;

	/**
	 *  Class that actually generates the binary data needed to print.
	 *
	 * Most of this comes from https://download.brother.com/welcome/docp100064/cv_pte550wp750wp710bt_eng_raster_101.pdf
	 * with clarifications from other sources:
	 *  - https://github.com/clarkewd/ptouch-print
	 *  - https://github.com/philpem/printer-driver-ptouch
	 */
	class PTouchJobBinary {
		private String $data;
		private int $compressionMode = PTouchJobBinary::COMPRESSION_NONE;

		public const COMPRESSION_NONE = 0;
		public const COMPRESSION_TIFF = 2;

		public function __construct() {
			$this->data = '';

			// These are set by default in a job, but then the Printer needs to set the rest.
			$this->invalidate();
			$this->initialize();
			$this->switchDynamicCommand();
			$this->notify(false);
		}

		/**
		 * Get the binary string to send to the printer.
		 *
		 * @return String Binary string to send to printer.
		 */
		public function getData(): String {
			return $this->data;
		}

		/**
		 * Invalidate - NULL
		 * Sends null bytes to the printer to reset it.
		 *
		 * @param int $count How many reset bytes to send.
		 * @return PTouchJobBinary This instance for chaining.
		 */
		private function invalidate($count = 100): PTouchJobBinary {
			for ($i = 0; $i < $count; $i++) {
				$this->data .= chr(0x00);
			}

			return $this;
		}

		/**
		 * Initialize - ESC @
		 * Sends initialization string to start the job.
		 *
		 * @return PTouchJobBinary This instance for chaining.
		 */
		private function initialize(): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x40); // @

			return $this;
		}

		// Not included: Status information request - ESC i S

		/**
		 * Switch dynamic command mode - ESC i a
		 * Switches the printer into raster mode.
		 * Technically other modes (ESC/P and P-touch Template) are supported
		 * by the device, but not by us.
		 *
		 * @return PTouchJobBinary This instance for chaining.
		 */
		private function switchDynamicCommand(): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x61); // a
			$this->data .= chr(0x01); // {n1} - 1 for raster mode

			return $this;
		}

		/**
		 * Switch automatic status notification mode - ESC i !
		 * We use this to turn status notifications off.
		 *
		 * @param bool $enabled Should status notifications be on or off?
		 * @return PTouchJobBinary This instance for chaining.
		 */
		private function notify(bool $enabled): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x21); // !
			$this->data .= chr($enabled ? 0 : 1); // {n1} - 0: Notify. (default), 1: Do not notify.

			return $this;
		}

		/**
		 * Print information command - ESC i z
		 * Print information.
		 *
		 * @param int $tapeSize Tape Size to require, in mm.
		 * @param int $rasterSize Length of print. Doesn't actually seem to be needed.
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function printInfo(int $tapeSize = 12, int $rasterSize = 0): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x7A); // z
			$this->data .= chr(($tapeSize != 0 ? 0x04 : 0x00) + 0x80); // {n1} - PI_WIDTH + PI_RECOVER
			$this->data .= chr(0x00); // {n2} - Media Type (Unused)
			$this->data .= chr($tapeSize); // {n3} - Media Width (mm)
			$this->data .= chr(0x00); // {n4} - Media Length (mm) (Unused)
			$this->data .= pack('V*', $rasterSize); // {n5} {n6} {n7} {n8}
			$this->data .= chr(0x00); // {n9} - Starting Page (0 = first, 1 = middle, 2 = last)
			$this->data .= chr(0x00); // {n10} - 0

			return $this;
		}

		/**
		 * Various mode settings - ESC i M
		 *
		 * @param bool $autoCut Enable AutoCut. (Default: true)
		 * @param bool $mirror Mirror Printing. (Default: true)
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function mode(bool $autoCut = true, bool $mirror = false): PTouchJobBinary {
			$val = 0;
			$val +=  (0 << 0); // Not Used
			$val +=  (0 << 1); // Not Used
			$val +=  (0 << 2); // Not Used
			$val +=  (0 << 3); // Not Used
			$val +=  (0 << 4); // Not Used
			$val +=  (0 << 5); // Not Used
			$val +=  ($autoCut << 6); // Auto Cut (1 == yes, 0 == no)
			$val +=  ($mirror << 7); // Mirror (1 = no, 0 == yes)

			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x4D); // M
			$this->data .= chr($val); // {n1}

			return $this;
		}

		/**
		 * Advanced mode settings - ESC i K
		 *
		 * @param bool $halfCut Enable half-cut. (Default: true)
		 * @param bool $chainPrinting Enable chain printing when printing multiple pages. (Default: true)
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function advancedMode(bool $halfCut = true, bool $chainPrinting = true): PTouchJobBinary {
			$val = 0;
			$val +=  (0 << 0); // Not Used
			$val +=  (0 << 1); // Not Used
			$val +=  ($halfCut << 2); // Half Cut (1 == yes, 0 == no)
			$val +=  (!$chainPrinting << 3); // Chain Printing (1 = no, 0 == yes) (This param is really "nochain")
			$val +=  (0 << 4); // Special Tape (1 == on, 0 == off)
			$val +=  (0 << 5); // Not Used
			$val +=  (0 << 6); // High Resolution
			$val +=  (0 << 7); // No buffer cleaning.

			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x4B); // K
			$this->data .= chr($val); // {n1}

			return $this;
		}

		/**
		 * Specify margin (feed amount) - ESC i d
		 *
		 * @param int $marginSize Number of lines for margin (Default: 10)
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function margin(int $marginSize = 10): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x64); // d
			$this->data .= pack('v*', $marginSize); // {n1} {n2}

			return $this;
		}

		/**
		 * Specify cut-each-X-labels mode - ESC i A
		 *
		 * @param int $page Page to cut after. (Default: 1)
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function cutEachX(int $page = 1): PTouchJobBinary {
			$this->data .= chr(0x1B); // ESC
			$this->data .= chr(0x69); // i
			$this->data .= chr(0x41); // A
			$this->data .= chr($page); // {n1}

			return $this;
		}


		/**
		 * Select compression mode - M
		 * This subtly changes what we send, but we don't actually support compression just yet
		 * and still just send a full 16 bytes per line.
		 *
		 * @param int $mode Mode for compression of raster data.
		 *                  PTouchJobBinary::COMPRESSION_NONE or PTouchJobBinary::COMPRESSION_TIFF
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function compressionMode(int $mode = PTouchJobBinary::COMPRESSION_TIFF): PTouchJobBinary {
			$this->compressionMode = $mode;

			$this->data .= chr(0x4D); // m
			$this->data .= chr($this->compressionMode); // {n} = 0 == None, 2 == TIFF

			return $this;
		}

		/**
		 * Raster graphics transfer - G
		 * Sends a line of raster graphics.
		 * This does slightly different things if TIFF compression is enabled, but for the moment
		 * we still don't actually implement the RLE for this.
		 * We do however short-circuit out to `Z` if we have a full line of 0s
		 *
		 * @param array $lineArr
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function rasterLine(Array $lineArr): PTouchJobBinary {
			if (array_sum($lineArr) == 0) { return $this->rasterZero(); }

			$this->data .= chr(0x47); // G
			if ($this->compressionMode == PTouchJobBinary::COMPRESSION_TIFF) {
				$this->data .= pack('v*', count($lineArr) + 1);
				$this->data .= chr(count($lineArr) - 1);
			} else {
				$this->data .= pack('v*', count($lineArr));
			}
			foreach ($lineArr as $l) {
				$this->data .= chr($l);
			}

			return $this;
		}

		/**
		 * Zero raster graphic - Z
		 * Sends a complete line of 0s.
		 *
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function rasterZero(): PTouchJobBinary {
			if ($this->compressionMode == PTouchJobBinary::COMPRESSION_TIFF) {
				$this->data .= chr(0x5A); // Z
			}

			return $this;
		}

		/**
		 * Helper method to automatically raster a full image.
		 * You still need to call the appropriate end-of-page command.
		 *
		 * @param RasterImage $image
		 * @return PTouchJobBinary
		 */
		public function rasterImage(RasterImage $image): PTouchJobBinary {
			foreach ($image->getLines() as $line) {
				$this->rasterLine($line);
			}

			return $this;
		}

		/**
		 * Print command - FF
		 * This is used in between pages to show the start of a new page.
		 *
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function printPage(): PTouchJobBinary {
			$this->data .= chr(0x0C); // FF

			return $this;
		}

		/**
		 * Print with feeding - Control-Z
		 * This is used at the end of the job.
		 *
		 * @return PTouchJobBinary This instance for chaining.
		 */
		public function printAndFeed(): PTouchJobBinary {
			$this->data .= chr(0x1A); // ^Z

			return $this;
		}

		/**
		 * Helper method to display the hex for this job.
		 *
		 * @param int $width How many hex bits to display per line.
		 */
		public function displayHex(int $width = 16) {
			$c = 1;
			foreach (str_split($this->data) as $d) {
				echo str_pad(dechex(ord($d)), 2, '0', STR_PAD_LEFT), ' ';
				if ($c++ % $width == 0) {
					echo "\n";
				}
			}
			echo "\n";
		}
	}
