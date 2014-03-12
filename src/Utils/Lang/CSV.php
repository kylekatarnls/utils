<?php
namespace Utils\Lang;

class CSV {

	static function convert($languages = null, $files = null) {

		$dir = app_path() . '/lang';
		if(is_null($languages)) {
			$languages = scandir($dir);
		}
		return implode(';', array_filter($languages, function ($language) use($dir) {
			return is_dir($dir . '/' . $language) && substr($language, 0, 1) !== '.';
		}));
	}
}