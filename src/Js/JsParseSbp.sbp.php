<?

namespace Utils\Js

use Coffeescript\Coffeescript

JsParser

	YES = 'yes|true|on|1';
	NO = 'no|false|off|0';

	* $coffeeFile;

	+ __construct $coffeeFile
		>coffeeFile = $coffeeFile;

	+ out $jsFile
		< file_put_contents(
			$jsFile,
			>parse(>coffeeFile)
		);

	s+ resolveRequire $coffeeFile, $firstFile = null
		if is_null($firstFile)
			$firstFile = $coffeeFile
		< preg_replace_callback(
			'#\/\/-\s*require\s*\(?\s*([\'"])(.*(?<!\\\\)(?:\\\\{2})*)\\1(?:[ \t]*,[ \t]*(' . :YES . '|' . :NO . '))?[ \t]*\)?[ \t]*(?=[\n\r]|$)#i',
			f° $match use $coffeeFile, $firstFile
				$file = stripslashes($match[2]);
				$file = preg_match('#^(http|https|ftp|sftp|ftps):\/\/#', $file) ?
					$file :
					static::findFile($file);
				$isCoffee = empty($match[3]) ?
					ends_with($file, '.coffee') :
					in_array(strtolower($match[3]), explode('|', :YES));
				DependancesCache::add($firstFile, $file);
				$file = static::resolveRequire($file, $firstFile)
				if ! $isCoffee
					$file = "`$file`";
				< $file
			,
			file_get_contents($coffeeFile)
		)

	+ parse $coffeeFile
		DependancesCache::flush($coffeeFile);
		$code = CoffeeScript\Compiler::compile(
			static::resolveRequire($coffeeFile),
			array(
				'filename' => $coffeeFile,
				'bare' => true
			)
		);
		if ! Config::get('app.debug')
			$code = preg_replace('#;(?:\\r\\n|\\r|\\n)\\h*#', ';', $code);
			$code = preg_replace('#(?:\\r\\n|\\r|\\n)\\h*#', ' ', $code);
		< $code;

	s* findFile $file
		if file_exists($file)
			< $file;
		$coffeeFile = static::coffeeFile($file);
		if file_exists($coffeeFile)
			< $coffeeFile;
		< static::jsFile($file);

	s+ coffeeFile $file, &$isALib = null
		$files = array(
			app_path() . '/assets/scripts/' . $file . '.coffee',
			app_path() . '/../public/js/lib/' . $file . '.coffee',
		);
		foreach $files as $iFile
			if file_exists($iFile)
				$isALib = str_contains($iFile, 'lib/');
				< $iFile;
		< array_get($files, 0);

	s+ jsFile $file, &$isALib = null
		$jsDir = app_path() . '/../public/js/';
		foreach array($jsDir, $jsDir . 'lib/') as $dir
			foreach array('coffee', 'js') as $ext
				if file_exists($dir . $file . '.' . $ext)
					$isALib = ends_with($dir, 'lib/');
					< $dir . $file . '.js';
		< app_path() . '/../public/js/' . $file . '.js';