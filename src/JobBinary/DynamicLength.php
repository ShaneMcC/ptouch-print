<?php
	namespace ShaneMcC\PTouchPrint\JobBinary;

	interface DynamicLength {
		public static function getAdditionalArgCount(array $args): int;
	}
