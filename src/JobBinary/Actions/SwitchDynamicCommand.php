<?php
	namespace ShaneMcC\PTouchPrint\JobBinary\Actions;

	use ShaneMcC\PTouchPrint\JobBinary\JobBinary;

	/**
	 * Switch dynamic command mode - ESC i a
	 * Switches the printer into raster mode.
	 * Technically other modes (ESC/P and P-touch Template) are supported
	 * by the device, but not by us.
	 */
	class SwitchDynamicCommand extends JobBinary {

		public static function getMagic(): array {
			return [0x1B, 0x69, 0x61]; // ESC i a
		}

		public static function getName(): string {
			return 'Switch Mode';
		}

		public static function argCount(): int {
			return 1;
		}

		public  function getBinary(): String {
			$data = static::getMagicString();
			$data .= chr(0x01); // {n1} - 1 for raster mode
			return $data;
		}

		public static function decodeBinary(array $args): String {
			switch ($args[0]) {
				case 0:
					return '(ESC/P)';
				case 1:
					return '(Raster)';
				case 3:
					return '(P-Touch Template)';
				default:
					return '(Unknown)';
			}
		}
	}
