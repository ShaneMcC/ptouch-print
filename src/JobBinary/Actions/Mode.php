<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Various mode settings - ESC i M
	 */
	class Mode extends JobBinary {
		private bool $autoCut;
		private bool $mirror;

		/**
		 * @param bool $autoCut Enable AutoCut. (Default: true)
		 * @param bool $mirror Mirror Printing. (Default: true)
		 */
		public function __construct(bool $autoCut = true, bool $mirror = false) {
			$this->autoCut = $autoCut;
			$this->mirror = $mirror;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x4D]; // ESC i M
		}

		public static function getName(): string {
			return 'Various mode settings';
		}

		public static function argCount(): int {
			return 1;
		}

		public  function getBinary(): String {
			$val = 0;
			$val += (0 << 0); // Not Used
			$val += (0 << 1); // Not Used
			$val += (0 << 2); // Not Used
			$val += (0 << 3); // Not Used
			$val += (0 << 4); // Not Used
			$val += (0 << 5); // Not Used
			$val += ($this->autoCut << 6); // Auto Cut (1 == yes, 0 == no)
			$val += ($this->mirror << 7); // Mirror (1 = no, 0 == yes)

			$data = static::getMagicString();
			$data .= chr($val); // {n1}
			return $data;
		}

		public static function decodeBinary(array $args): String {
			$flags = [];
			$flags[] = ($args[0] & 0x40) ? '40=AutoCut' : '40=NoCut';
			$flags[] = ($args[0] & 0x80) ? '80=Mirror' : '80=NoMirror';

			return '(' . implode(' ', $flags) . ')';
		}
	}
