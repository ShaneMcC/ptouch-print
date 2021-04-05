<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Advanced mode settings - ESC i K
	 */
	class AdvancedMode extends JobBinary {
		private bool $halfCut;
		private bool $chainPrinting;

		/**
		 * @param bool $halfCut Enable half-cut. (Default: true)
		 * @param bool $chainPrinting Enable chain printing when printing multiple pages. (Default: true)
		 */
		public function __construct(bool $halfCut = true, bool $chainPrinting = true) {
			$this->halfCut = $halfCut;
			$this->chainPrinting = $chainPrinting;
		}

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x4B]; // ESC i K
		}

		public static function getName(): string {
			return 'Advanced mode settings';
		}

		public static function argCount(): int {
			return 1;
		}

		public  function getBinary(): String {
			$val = 0;
			$val += (0 << 0); // Not Used
			$val += (0 << 1); // Not Used
			$val += ($this->halfCut << 2); // Half Cut (1 == yes, 0 == no)
			$val += (!$this->chainPrinting << 3); // Chain Printing (1 = no, 0 == yes) (This param is really "nochain")
			$val += (0 << 4); // Special Tape (1 == on, 0 == off)
			$val += (0 << 5); // Not Used
			$val += (0 << 6); // High Resolution
			$val += (0 << 7); // No buffer cleaning.

			$data = static::getMagicString();
			$data .= chr($val); // {n1}
			return $data;
		}

		public static function decodeBinary(array $args): String {
			$flags = [];
			$flags[] = ($args[0] & 0x04) ? '04=HalfCut' : '04=NoHalfCut';
			$flags[] = ($args[0] & 0x08) ? '08=NoChain' : '08=Chain';
			$flags[] = ($args[0] & 0x10) ? '10=SpecialTape' : '10=NoSpecialTape';
			$flags[] = ($args[0] & 0x40) ? '40=HighRes' : '40=Normal';
			$flags[] = ($args[0] & 0x80) ? '80=NoBufferClear' : '80=BufferClear';

			return '(' . implode(' ', $flags) . ')';
		}
	}
