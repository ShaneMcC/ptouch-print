<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	interface Drawable {
		public static function draw(array $args, int $compressionMode): String;
	}
