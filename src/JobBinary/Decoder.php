<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	use Exception;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\AdvancedMode;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\CompressionMode;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\cutEachX;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Initialize;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Invalidate;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Margin;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Mode;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Notify;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\PrintAndFeed;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\PrintInfo;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\PrintPage;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\RasterLine;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\RasterZero;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\SwitchDynamicCommand;
	use ShaneMcC\PTouchPrint\JobBinary\Actions\Undocumented;

	/**
	 * This will decode a raw stream and display it to the user.
	 *
	 * This is designed to replicate `ptexplain` from https://github.com/philpem/printer-driver-ptouch
	 *
	 * @package ShaneMcC\PTouchPrint
	 */
	class Decoder {
		private static function get(String &$string, int $index, int $count = 1) {
			$b = [];
			$s = [];

			for ($i = 0; $i < $count; $i++) {
				if (isset($string[$index + $i])) {
					$b[] = $_b = (int)ord($string[$index + $i]);
					$s[] = str_pad(dechex($_b), 2, '0', STR_PAD_LEFT);
				}
			}

			return [$b, $s];
		}

		private static function canColour() {
			return (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;
		}

		private static function colouriseString($string, $colour) {
			if (static::canColour()) {
				$colours['black'] = '0;30';
				$colours['dark_gray'] = '1;30';
				$colours['blue'] = '0;34';
				$colours['light_blue'] = '1;34';
				$colours['green'] = '0;32';
				$colours['light_green'] = '1;32';
				$colours['cyan'] = '0;36';
				$colours['light_cyan'] = '1;36';
				$colours['red'] = '0;31';
				$colours['light_red'] = '1;31';
				$colours['purple'] = '0;35';
				$colours['light_purple'] = '1;35';
				$colours['brown'] = '0;33';
				$colours['yellow'] = '1;33';
				$colours['light_gray'] = '0;37';
				$colours['white'] = '1;37';
				$colours['none'] = '0';

				return "\033[" . (isset($colours[$colour]) ? $colours[$colour] : $colours['none']) . "m" . $string . "\033[0m";
			} else {
				return $string;
			}
		}

		public static function decode(String $string, $drawRaster = false) {
			$compressionMode = 0;

			$classes = [];
			$classes[] = AdvancedMode::class;
			$classes[] = CompressionMode::class;
			$classes[] = cutEachX::class;
			$classes[] = Initialize::class;
			$classes[] = Invalidate::class;
			$classes[] = Margin::class;
			$classes[] = Mode::class;
			$classes[] = Notify::class;
			$classes[] = PrintAndFeed::class;
			$classes[] = PrintInfo::class;
			$classes[] = PrintPage::class;
			$classes[] = RasterLine::class;
			$classes[] = RasterZero::class;
			$classes[] = SwitchDynamicCommand::class;
			$classes[] = Undocumented::class;

			$classInfo = [];
			foreach ($classes as $class) {
				$ci = ['name' => $class::getName(),
				       'argcount' => $class::argCount(),
				       'magic' => $class::getMagic(),
				       'magicstring' => implode(' ', str_split($class::getMagicString())),
				       'decode' => fn($a) => $class::decodeBinary($a),
				       'repeatable' => is_subclass_of($class, Repeatable::class),
				      ];

				if (is_subclass_of($class, DynamicLength::class)) {
					$ci['additionalargcount'] = fn($a) => $class::getAdditionalArgCount($a);
				}

				if (is_subclass_of($class, Drawable::class)) {
					$ci['draw'] = fn($a, $c) => $class::draw($a, $c);
				}

				// Friendly replacements
				$ci['displaystring'] = $ci['magicstring'];
				$ci['displaystring'] = str_replace(chr(0x00), '00', $ci['displaystring']);
				$ci['displaystring'] = str_replace(chr(0x1A), '^Z', $ci['displaystring']);
				$ci['displaystring'] = str_replace(chr(0x1B), 'ESC', $ci['displaystring']);

				$classInfo[] = $ci;
			}

			$compressionMode = CompressionMode::NONE;
			// Go through each bit of data.
			for ($i = 0; $i < strlen($string); /* Code below increments as needed.*/ ) {
				// Check each of our known classes.
				foreach ($classInfo as $ci) {
					// If this class is not applicable, abort.
					if (strlen($string) < $i + count($ci['magic'])) { continue; }

					// Get the amount of data needed to check if this is a match
					[$bb, $bs] = static::get($string, $i, count($ci['magic']));

					// Check if we match.
					if ($ci['magic'] == $bb) {
						// We do, so lets output some information.
						$lineLen = 0;
						$i += count($ci['magic']);

						// This nice display string of this class
						echo static::colouriseString($ci['displaystring'], 'light_blue');
						$lineLen += strlen($ci['displaystring']);

						// If this class accepts args, we also want to display those.
						if ($ci['argcount'] > 0) {
							[$ab, $as] = static::get($string, $i, $ci['argcount']);
							$i += $ci['argcount'];
							$ias = implode(' ', $as);
							echo ' ', static::colouriseString($ias, 'light_green');
							$lineLen += 1 + strlen($ias);
						} else {
							[$ab, $as] = [[], []];
						}

						// Name
						echo ' - ', $ci['name'];
						$lineLen += 3 + strlen($ci['name']);

						// If this class accepts args, then we want to decode them where possible.
						if ($ci['argcount'] > 0) {
							// Some classes have dynamic length additional args, so also grab those.
							if (isset($ci['additionalargcount'])) {
								$additionalargcount = $ci['additionalargcount']($ab);
								[$eb, $es] = static::get($string, $i, $additionalargcount);
								$i += $additionalargcount;

								$ab = array_merge($ab, $eb);
								$as = array_merge($as, $es);
							}

							// Show decoded args.
							$decodeInfo = $ci['decode']($ab);
							echo ' ', $decodeInfo;
							$lineLen += 1 + strlen($decodeInfo);
						}

						// Extra processing we might need to do for state tracking.
						switch ($ci['magic']) {
							case CompressionMode::getMagic():
								$compressionMode = $ab[0];
								break;
						}

						// Track Repeats.
						if ($ci['repeatable']) {
							$count = 1;
							while (static::get($string, $i, count($ci['magic']) + count($ab))[0] === array_merge($ci['magic'], $ab)) {
								$count++;
								$i += count($ci['magic']) + count($ab);
							}
							if ($count > 0) {
								echo ' (Repeated: ', $count, ')';
								$lineLen += 13 + strlen($count);
							}
						}

						// Drawing.
						if ($drawRaster && isset($ci['draw'])) {
							$drawString = $ci['draw']($ab, $compressionMode);

							if (!empty($drawString)) {
								echo str_repeat(' ', max(0, 95 - $lineLen));
								echo '│', $drawString, '│';
							}
						}
						echo "\n";

						continue 2;
					}
				}

				[$bb, $bs] = static::get($string, $i, 10);
				throw new Exception('Unknown string encountered: ' . implode(' ', $bs));
			}
		}
	}
