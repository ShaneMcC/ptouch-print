<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Print information command - ESC i z
	 * Print information.
	 */
	class PrintInfo extends JobBinary {
		private int $tapeSize;
		private int $rasterSize;

		/**
		 * @param int $tapeSize Tape Size to require, in mm.
		 * @param int $rasterSize Length of print. Doesn't actually seem to be needed.
		 */
		public function __construct(int $tapeSize = 12, int $rasterSize = 0) {
			$this->tapeSize = $tapeSize;
			$this->rasterSize = $rasterSize;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x7A]; // ESC i z
		}

		public static function getName(): string {
			return 'Print Information';
		}

		public static function argCount(): int {
			return 10;
		}

		public  function getBinary(): String {
			$data = static::getMagicString();
			$data .= chr(($this->tapeSize != 0 ? 0x04 : 0x00) + 0x80); // {n1} - PI_WIDTH + PI_RECOVER
			$data .= chr(0x00); // {n2} - Media Type (Unused)
			$data .= chr($this->tapeSize); // {n3} - Media Width (mm)
			$data .= chr(0x00); // {n4} - Media Length (mm) (Unused)
			$data .= pack('V*', $this->rasterSize); // {n5} {n6} {n7} {n8}
			$data .= chr(0x00); // {n9} - Starting Page (0 = first, 1 = middle, 2 = last)
			$data .= chr(0x00); // {n10} - 0
			return $data;
		}

		public static function decodeBinary(array $args): String {
			$result = '';

			$flags = [];
			if ($args[0] & 0x02) { $flags[] = '02=kind'; }
			if ($args[0] & 0x04) { $flags[] = '04=width'; }
			if ($args[0] & 0x08) { $flags[] = '08=length'; }
			if ($args[0] & 0x40) { $flags[] = '40=quality'; }
			if ($args[0] & 0x80) { $flags[] = '80=recover'; }

			$result .= '(' . implode(' ', $flags) . ') ';
			if ($args[0] & 0x02) {
				switch ($args[1]) {
					case 0x00:
						break;
					case 0x01:
						$result .= 'LaminatedTape ';
						break;
					case 0x03:
						$result .= 'non-LaminatedTape ';
						break;
					case 0x11:
						$result .= 'heat-shrink ';
						break;
					case 0xFF:
						$result .= 'incompatible ';
						break;
					default:
						$result .= 'unknown-media';
						break;
				}
			}
			if ($args[0] & 0x04) { $result .= 'width=' . $args[2] . ' '; }
			if ($args[0] & 0x08) { $result .= 'length=' . $args[3] . ' '; }

			$packed = chr($args[4]) . chr($args[5]) . chr($args[6]) . chr($args[7]);
			$result .= 'lines=' . unpack('V', $packed)[1] . ' ';

			switch ($args[8]) {
				case 0x00:
					$result .= 'page=first';
					break;
				case 0x01:
					$result .= 'page=middle';
					break;
				case 0x03:
					$result .= 'page=last';
					break;
				default:
					$result .= 'page=unknown';
			}

			return $result;
		}
	}
