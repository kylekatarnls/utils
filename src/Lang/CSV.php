<?php
namespace Utils\Lang;

class CSV {

	const DELIMITER = ';';
	const BOM_UTF8 = true;

	static public function files($directory) {

		$files = array();
		foreach (scandir($directory) as $file) {
			$path = $directory . '/' . $file;
			if(substr($file, 0, 1) !== '.') {
				if(is_file($path)) {
					$files[] = $path;
				} else {
					$files = array_merge($files, static::files($path));
				}
			}
		}
		sort($files);
		return $files;
	}

	static public function langTexts($file, $key = null, $default = null) {

		static $cache = array();
		if( ! isset($cache[$file])) {
			$cache[$file] = array_dot(include($file));
		}
		return is_null($key) ? $cache[$file] : array_get($cache[$file], $key, $default);
	}

	static public function convert($languages = null, $onlyFiles = null) {

		$dir = app_path() . '/lang';
		if(is_null($languages)) {
			$languages = scandir($dir);
		}
		$languages = array_filter($languages, function ($language) use($dir) {
			return is_dir($dir . '/' . $language) && substr($language, 0, 1) !== '.';
		});
		if( ! is_null($onlyFiles)) {
			$onlyFiles = array_map(function ($file) {
				return ltrim(preg_replace('#\.php$#', '', $file), '/');
			}, $onlyFiles);
		}
		$langFiles = array_map(function ($language) use($onlyFiles, $dir) {
			$baseDir = $dir . '/' . $language;
			$files = static::files($baseDir);
			if(!is_null($onlyFiles)) {
				$len = strlen($baseDir);
				$files = array_filter($files, function ($file) use($onlyFiles, $len) {
					$basePath = ltrim(preg_replace('#\.php$#', '', substr($file, $len)), '/');
					return in_array($basePath, $onlyFiles);
				});
			}
			return $files;
		}, array_combine($languages, $languages));
		$time = max(array_map('filemtime', array_flatten($langFiles)));
		$storage = app_path() . '/storage/lang-csv';
		if( ! file_exists($storage)) {
			mkdir($storage, 0777);
			file_put_contents($storage . '/.gitignore', "*\n!.gitignore");
		}
		$csvFile = $storage . '/' . implode('-', $languages) . '-' . date('Y-m-d-H-i-s', $time) . '.csv';
		if( ! file_exists($csvFile)) {
			$stream = fopen($csvFile, 'w');
			if(static::BOM_UTF8) {
				fwrite($stream, "\xEF\xBB\xBF");
			}
			static::put($stream, array_merge(array('file', 'key'), $languages));
			foreach (head($langFiles) as $index => $subLangFile) {
				$file = preg_replace('#\.php$#', '', substr($subLangFile, intval(strpos($subLangFile, '/', strlen($dir) + 1)) + 1));
				foreach (static::langTexts($subLangFile) as $key => $value) {
					$fields = array($file, $key);
					foreach ($langFiles as $language => $subLangFiles) {
						$fields[] = static::langTexts($subLangFiles[$index], $key);
					}
					static::put($stream, $fields);
				}
			}
			fclose($stream);
		}
		return $csvFile;
	}

	static public function put($stream, $fields) {

		return fputcsv($stream, $fields, static::DELIMITER);
	}

	static public function add($stream, $fields) {

		return static::put($stream, $fields);
	}

	static public function get($stream, $param = null) {

		return fgetcsv($stream, $param, static::DELIMITER);
	}

	static public function next($stream, $param = null) {

		return static::get($stream, $param);
	}
}