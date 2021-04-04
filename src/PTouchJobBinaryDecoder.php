<?php
	namespace ShaneMcC\PTouchPrint;


	use Exception;

	/**
	 * This will decode a raw stream and display it to the user.
	 *
	 * This is designed to replicate `ptexplain` from https://github.com/philpem/printer-driver-ptouch
	 *
	 * @package ShaneMcC\PTouchPrint
	 */
	class PTouchJobBinaryDecoder {
		private static function get(String &$string, int $index, int $count = 1) {
			$b = [];
			$s = [];

			for ($i = 0; $i < $count; $i++) {
				$b[] = $_b = (int)ord($string[$index + $i]);
				$s[] = str_pad(dechex($_b), 2, '0', STR_PAD_LEFT);
			}

			if ($count == 1) { $b = $b[0]; $s = $s[0]; }

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
			for ($i = 0; $i < strlen($string); $i++) {
				[$b, $s] = static::get($string, $i);

				switch ($b) {
					case 0x00: // NULL
						echo static::colouriseString($s, 'light_blue'), ' - Invalidate';
						$count = 0;
						do { $count++; } while (ord($string[++$i]) === 0);
						echo ' (', $count, ')', "\n";
						$i--;
						break;
					case 0x1B: // ESC
						[$b, $s] = static::get($string, ++$i);
						switch ($b) {
							case 0x40: // @
								echo static::colouriseString('ESC @', 'light_blue'), ' - Initialize', "\n";
								break;
							case 0x69: // i
								[$b, $s] = static::get($string, ++$i);
								switch ($b) {
									case 0x63: // s
										echo static::colouriseString('ESC i s', 'light_blue'), ' - Status information request', "\n";
										break;
									case 0x61: // a
										[$n1b, $n1s] = static::get($string, ++$i);
										switch ($n1b) {
											case 0:
												$mode = 'ESC/P';
												break;
											case 1:
												$mode = 'Raster';
												break;
											case 3:
												$mode = 'P-Touch Template';
												break;
											default:
												$mode = 'Unknown';
										}
										echo static::colouriseString('ESC i a ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Switch Mode (', $mode, ')', "\n";
										break;
									case 0x21: // !
										[$n1b, $n1s] = static::get($string, ++$i);
										switch ($n1b) {
											case 0:
												$mode = 'Notify (Default)';
												break;
											case 1:
												$mode = 'Do not notify';
												break;
											default:
												$mode = 'Unknown';
										}
										echo static::colouriseString('ESC i ! ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Automatic Status Notification (', $mode, ')', "\n";
										break;
									case 0x55: // z
										[$nb, $ns] = static::get($string, ++$i, 15);
										$i += 14;
										echo static::colouriseString('ESC i U ', 'light_blue'), static::colouriseString(implode(' ', $ns), 'light_green'), ' - Udocumented Command', "\n";
										break;
									case 0x7a: // z
										[$nb, $ns] = static::get($string, ++$i, 10);
										$i += 9;
										$flags = [];
										if ($nb[0] & 0x02) { $flags[] = '02=kind'; }
										if ($nb[0] & 0x04) { $flags[] = '04=width'; }
										if ($nb[0] & 0x08) { $flags[] = '08=length'; }
										if ($nb[0] & 0x40) { $flags[] = '40=quality'; }
										if ($nb[0] & 0x80) { $flags[] = '80=recover'; }

										echo static::colouriseString('ESC i z ', 'light_blue'), static::colouriseString(implode(' ', $ns), 'light_green'), ' - Print Information ';
										echo '(', implode(' ', $flags), ') ';

										if ($nb[0] & 0x02) {
											switch ($nb[1]) {
												case 0x00:
													break;
												case 0x01:
													echo 'LaminatedTape ';
													break;
												case 0x03:
													echo 'non-LaminatedTape ';
													break;
												case 0x11:
													echo 'heat-shrink ';
													break;
												case 0xFF:
													echo 'incompatible ';
													break;
												default:
													echo 'unknown-media';
													break;
											}
										}
										if ($nb[0] & 0x04) { echo 'width=', $nb[2], ' '; }
										if ($nb[0] & 0x08) { echo 'length=', $nb[3], ' '; }

										$packed = chr($nb[4]) . chr($nb[5]) . chr($nb[6]) . chr($nb[7]);
										echo 'lines=', unpack('V', $packed)[1], ' ';

										switch ($nb[8]) {
											case 0x00:
												echo 'page=first';
												break;
											case 0x01:
												echo 'page=middle';
												break;
											case 0x03:
												echo 'page=last';
												break;
											default:
												echo 'page=unknown';
										}
										echo "\n";

										break;
									case 0x4d: // M
										[$n1b, $n1s] = static::get($string, ++$i);
										$flags = [];
										$flags[] = ($n1b & 0x40) ? '40=AutoCut' : '40=NoCut';
										$flags[] = ($n1b & 0x80) ? '80=Mirror' : '80=NoMirror';

										echo static::colouriseString('ESC i M ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Various mode settings ';
										echo '(', implode(' ', $flags), ') ', "\n";
										break;
									case 0x41: // A
										[$n1b, $n1s] = static::get($string, ++$i);
										echo static::colouriseString('ESC i K ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Cut every X (', hexdec($n1s), ')', "\n";
										break;
									case 0x4B: // K
										[$n1b, $n1s] = static::get($string, ++$i);
										$flags = [];
										$flags[] = ($n1b & 0x04) ? '04=HalfCut' : '04=NoHalfCut';
										$flags[] = ($n1b & 0x08) ? '08=NoChain' : '08=Chain';
										$flags[] = ($n1b & 0x10) ? '10=SpecialTape' : '10=NoSpecialTape';
										$flags[] = ($n1b & 0x40) ? '40=HighRes' : '40=Normal';
										$flags[] = ($n1b & 0x80) ? '80=NoBufferClear' : '80=BufferClear';

										echo static::colouriseString('ESC i K ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Advanced mode settings ';
										echo '(', implode(' ', $flags), ') ', "\n";
										break;
									case 0x64: // d
										[$nb, $ns] = static::get($string, ++$i, 2);
										$i += 1;
										$packed = chr($nb[0]) . chr($nb[1]);
										echo static::colouriseString('ESC i d ', 'light_blue'), static::colouriseString(implode(' ', $ns), 'light_green'), ' - Margin (', unpack('v', $packed)[1], ' lines)', "\n";
										break;
									default:
										throw new Exception('Unknown `' . static::colouriseString('ESC i') . '` character encountered: ' . $s);
								}
								break;
							default:
								throw new Exception('Unknown `' . static::colouriseString('ESC') . '` character encountered: ' . $s);
						}
						break;
					case 0x4D: // M
						[$n1b, $n1s] = static::get($string, ++$i);
						$compressionMode = $n1b;
						switch ($n1b) {
							case 0:
								$mode = 'None';
								break;
							case 1:
								$mode = 'Reserved';
								break;
							case 2:
								$mode = 'TIFF';
								break;
							default:
								$mode = 'Unknown';
						}
						echo static::colouriseString('M ', 'light_blue'), static::colouriseString($n1s, 'light_green'), ' - Compression (', $mode, ')', "\n";

						break;
					case 0x5a: // Z
						echo static::colouriseString('Z', 'light_blue'), ' - Zero Graphics Transfer ';
						if ($drawRaster) {
							echo sprintf('%81s', ' ');
							echo '│', str_repeat(' ', 128), '│';
						}
						echo "\n";
						break;
					case 0x47: // G
						[$nb, $ns] = static::get($string, ++$i, 2);
						$i++;
						$packed = chr($nb[0]) . chr($nb[1]);
						$len = unpack('v', $packed)[1];
						echo static::colouriseString('G ', 'light_blue'), static::colouriseString(implode(' ', $ns), 'light_green'), ' - Graphics Transfer ';

						[$db, $ds] = static::get($string, $i + 1, $len);
						$i += $len;
						echo sprintf('%-80s', '(' . $len . ' bytes) [' . implode(' ', $ds) . '] ');
						if ($drawRaster) {
							// Draw
							if ($compressionMode == 2) {
								// Decompress the drawbits
								$drawBits = [];
								for ($d = 0; $d < count($db); $d++) {
									$bytes = unpack('c', chr($db[$d]))[1];
									if ($bytes < 0) {
										$bytes = abs($bytes);
										$d++;
										for ($z = 0; $z <= $bytes; $z++) { $drawBits[] = $db[$d]; }
									} else {
										for ($z = 0; $z <= $bytes; $z++) { $drawBits[] = $db[++$d]; }
									}
								}
							} else {
								// Already decompressed.
								$drawBits = $db;
							}

							echo '│';
							foreach (array_reverse($drawBits) as $bit) {
								$bin = str_pad(strrev(decbin($bit)), 8, '0', STR_PAD_RIGHT);
								// $bin = str_pad(decbin($bit), 8, '0', STR_PAD_RIGHT);
								echo str_replace('1', '█', str_replace('0', ' ', $bin));
							}
							echo '│';
							}
						echo "\n";

						break;
					case 0x0C: // FF
						echo static::colouriseString('FF', 'light_blue'), ' - Print Page', "\n";
						break;
					case 0x1A: // FF
						echo static::colouriseString('^Z', 'light_blue'), ' - Print and Feed', "\n";
						break;
					default:
						throw new Exception('Unknown character encountered: ' . $s);
				}
			}
		}
	}
