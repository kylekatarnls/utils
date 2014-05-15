<?

namespace Utils\Css

use Utils\Css\Stylus
use Utils\Css\StylusException

CssParser

	- $fichier = false;
	- $options = false;
	s+ $activeInstance = null;

	+ __construct $fichier = false, $options = false
		>fichier = $fichier;
		>options = $fichier;
		self::$activeInstance = $this;

	+ __destruct
		self::$activeInstance = null;

	- cp_unite $u
		if strval(floatval($u)) === $u
			< $u.'px';
		< $u;
	- cssb_sp $d
		$tab=$d[1];
		unset($d[1]);
		$d=array_values($d);
		if $d[1] === 'mp'
			<	$tab.
				'margin '.>cp_unite($d[2])."\n".$tab.
				'padding '.>cp_unite($d[3]).$d[4];
		if $d[1] === 'size'
			<	$tab.
				'width '.>cp_unite($d[2])."\n".$tab.
				'height '.>cp_unite($d[3]).$d[4];
		$c = array(
			'pos' => '',
			'abs' => "position absolute\n".$tab,
			'rel' => "position relative\n".$tab,
			'fix' => "position fixed\n".$tab
		);
		<	$tab.$c[$d[1]].
			'left '.>cp_unite($d[2])."\n".$tab.
			'top '.>cp_unite($d[3]).$d[4];

	- cssb $d
		$moins = 0;
		$tab = $d[1];
		unset($d[1]);
		$d = array_values($d);
		if preg_match('#^\@import#', $d[1])
			< $tab.$d[1];
		$d = array_map('trim', preg_split('#[\h:]#', $d[1], 2));
		rtrim(**$d[1], '; ');
		$d[0] :=
			'opacity' ::
				<	$tab.
					'filter:alpha(opacity='.intval($d[1]*100).");\n".$tab.
					'-o-'.$d[0].':'.$d[1].";\n".$tab.
					'-ms-'.$d[0].':'.$d[1].";\n".$tab.
					'-moz-'.$d[0].':'.$d[1].";\n".$tab.
					'-imac-'.$d[0].':'.$d[1].";\n".$tab.
					'-khtml-'.$d[0].':'.$d[1].";\n".$tab.
					'-webkit-'.$d[0].':'.$d[1].";\n".$tab.
					$d[0].':'.$d[1].";";
			'-border-radius' ::
				$moins = 1;
			'border-radius' ::
				if $moins && !defined('BEHAVIOR')
					define('BEHAVIOR', 1);
				$d1 = explode(' ', $d[1]);
				<	$tab.
					'-o-'.$d[0].':'.$d[1].";\n".$tab.
					'-ms-'.$d[0].':'.$d[1].";\n".$tab.
					(preg_match('#\s#',$d[1])?
						'-moz-'.$d[0].'-topleft:'.@$d1[0].";\n".$tab.
						'-moz-'.$d[0].'-topright:'.@$d1[1].";\n".$tab.
						'-moz-'.$d[0].'-bottomright:'.@$d1[2].";\n".$tab.
						'-moz-'.$d[0].'-bottomleft:'.@$d1[3].";\n".$tab:

						'-moz-'.$d[0].':'.@$d[1].";\n".$tab
					).
					'-imac-'.$d[0].':'.$d[1].";\n".$tab.
					'-khtml-'.$d[0].':'.$d[1].";\n".$tab.
					'-webkit-'.$d[0].':'.$d[1].';'.($moins? "\n".$tab."behavior:url('./border-radius.htc');":'')."\n".$tab.
					$d[0].':'.$d[1].";";

		$arobase = '';
		if $d[0][0] === '@'
			$arobase = '@';
			substr(**$d[0],1);
		<	$tab.
			$arobase.'-o-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.'-ms-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.'-moz-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.'-imac-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.'-khtml-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.'-webkit-'.$d[0].':'.$d[1].";\n".$tab.
			$arobase.$d[0].':'.$d[1].";";

	+ options $options = false
		>options = $fichier;

	+ typeAndIndent $code, $options = false
		if $options === false
			$options = >options;
		else
			>options = $options;
		$type = floor($options/18);
		$options%6 :=
			1 ::
				$i = "\t";
				:;
			2 ::
				$i = 'x';
				:;
			3 ::
				$i = '    ';
				:;
			4 ::
				$i = '      ';
				:;
			5 ::
				$i = '        ';
				:;
			d:
				$i = preg_match('#(\r|\n)(\h+)\H#', $code, $i) ? $i[2]:'x';

		floor($options/6) :=
			1 ::
				$a = 1;
				:;
			2 ::
				$a = 2;
				:;
			d:
				$a = preg_match('#(\r|\n)\{(\r|\n)#',$code) ? 1:2;

		< array($type, $i, $a);

	+ filterCssb $code, $options = false
		list($type, $i, $a) = >typeAndIndent($code, $options);

		$code = preg_replace_callback('#(?<![a-zA-Z0-9_-])image\s*\(\s*([\'"])([^\'"]+)\\1\s*\)#', f° $match
			< 'url(' . $match[1] . image($match[2]) . $match[1] . ')';
		, $code);

		// CSSB - Avant
		if $type != 1
			$code = preg_replace_callback('#(?<=^|\n|\r)(\h*)\[\[([\s\S]*)\]\]#mU', array($this, 'cssb'), $code);
			$racv = array(
				 'lm' => 'float:left;margin'
				,'rm' => 'float:right;margin'
				,'bm' => 'display:block;margin'
				,'lp' => 'float:left;padding'
				,'rp' => 'float:right;padding'
				,'bp' => 'display:block;padding'
				,'m0p' => 'display:block;margin:0;padding'
			);
			$rac = array(
				 'abs' => 'position:absolute'
				,'rel' => 'position:relative'
				,'fix' => 'position:fixed'
				,'db' => 'display:block'
				,'dl' => 'display:block;float:left'
				,'fl' => 'float:left'
				,'dr' => 'display:block;float:right'
				,'fr' => 'float:right'
				,'di' => 'display:inline'
				,'dn' => 'display:none'
				,'fb' => 'font-weight:bold'
				,'fi' => 'font-style:italic'
				,'fbi' => 'font-weight:bold;font-style:italic'
				,'fu' => 'text-decoration:underline'
				,'fnu' => 'text-decoration:none'
				,'bn' => 'border:none'
				,'b1sb' => 'border:1px solid black'
				,'b2sb' => 'border:2px solid black'
				,'b3sb' => 'border:3px solid black'
				,'cp' => 'cursor:pointer'
				,'tal' => 'text-align:left'
				,'tar' => 'text-align:right'
				,'tac' => 'text-align:center'
				,'taj' => 'text-align:justify'
				,'oh' => 'overflow:hidden'
				,'oa' => 'overflow:auto'
				,'mp0' => 'margin:0;padding:0'
			);
			foreach $rac as $c => $r
				$code = preg_replace('#(?<![a-zA-Z0-9-\:])'.$c.'\s*[;\n]#', $r.";\n", $code);
			foreach $racv as $c => $r
				$code = preg_replace('#(?<![a-zA-Z0-9-\:])'.$c.'\s*:#U', $r.':', $code);

		// if($type!=2)
		// 	try
		// 		f less_css_chaines($c)
		// 			< ':~"'.addcslashes($c[1],'"').'";';
		// 		$code=((new lessc)->parse(preg_replace_callback('#:(\s*[a-z]+\(.+\));#U','less_css_chaines',$code)));
		// 	catch(Exception $e)
		// 		$code='/* Erreur LessCSS'."\n\n".$e->getMessage()."\n\n*/\n\n".$code;

		// CSSB - Après
		if $type != 1
			$code = preg_replace_callback('#(?<=^|\n|\r)(\h*)(size|pos|abs|rel|fix|mp)\s*[:\s]\s*(\S+)\s*[\s,]\s*(\S+)\s*([;}\n])#U', array($this, 'cssb_sp'), $code);

		< preg_replace('#(?<=[a-z]):([^\n;]+);#i', ' $1', $code);

	+ parse $code = false, $options = false
		if $code === false
			$code = file_get_contents(>fichier);
		list($type, $i, $a) = >typeAndIndent($code, $options);

		$dir = dirname($fichier = >fichier);
		if strpos($code,'[[import') !== false
			$code = preg_replace_callback(
				'#\[\[import(\s+\-p)?[\s:]\s*(\\"([^\\"]*)\\"|[^\\"\n\r]*)\]\]#',
				f° $f use $dir, $fichier
					if substr($f[0],-7) !== '.stylus'
						< '/*!'.$f[0].'!*/';
					trim(**$f[2], "\" \n\t");
					$file = $dir.'/'.$f[2];
					if !file_exists($file)
						$file = $dir.'/lib/'.$f[2];
					$fgc = file_get_contents($file);
					if class_exists('DependancesCache')
						DependancesCache::add($fichier, $file);
					// if(!empty($f[1]))
					// 	try
					// 		$fgc=(new lessc)->parse($fgc);
					// 	catch(Exception $e)
					// 		$fgc="/* Le fichier ".$f[2]." n'a pas pu être compilé par LessCSS */\n".$fgc;
					< "\n".$fgc."\n";
				,
				$code
			);

		$code = >filterCssb($code, $options);

		$code = Stylus::parse(>fichier, $code);
		if $a === 1
			$code = preg_replace('#\s\{(?=\n|\r)#', "\n{", $code);
		if $i!=='x'
			$code = preg_replace('#(?<=\n|\r)\h+(?=\H)#', $i, $code);
		if strpos($code,'/*![[import') !== false
			$code = preg_replace_callback(
				'#\/\*\!\[\[import(\s+\-p)?[\s:]\s*(\\"([^\\"]*)\\"|[^\\"\n\r]*)\]\]\!\*\/#',
				f° $f use $dir
					trim(**$f[2],"\" \n\t");
					$fgc = file_get_contents($dir.'/'.$f[2]);
					// if(!empty($f[1]))
					// 	try
					// 		$fgc=(new lessc)->parse($fgc);
					// 	catch(Exception $e)
					// 		$fgc="/* Le fichier ".$f[2]." n'a pas pu être compilé par LessCSS */\n".$fgc;
					< "\n".$fgc."\n";
				,
				$code
			);
		$code = str_replace('},', '}', $code);
		if !Config::get('app.debug')
			$code = preg_replace('#\s+#', ' ', $code);
			$code = preg_replace('#\s*([\}\{:;])\s*#', '$1', $code);
			$code = str_replace(';}', '}', $code);
		< $code;

	+ out $fichier, $options = false
		$options = >options;
		< file_put_contents($fichier, >parse());

	s+ stylusFile $file
		$files = array(
			app_path().'/assets/styles/' . $file . '.stylus',
			app_path().'/../public/css/lib/' . $file . '.stylus',
		);
		foreach $files as $iFile
			if file_exists($iFile)
				$isALib = str_contains($iFile, 'lib/');
				< $iFile;
		< array_get($files, 0);

	s+ cssFile $file, &$isALib = null
		$files = array(
			app_path().'/../public/css/' . $file . '.css',
			app_path().'/../public/css/lib/' . $file . '.css',
		);
		foreach $files as $iFile
			if file_exists($iFile)
				$isALib = str_contains($iFile, 'lib/');
				< $iFile;
		< array_get($files, 0);