<?php namespace Widgets;

class StarPush
{
	private $identifiant='none';
	private $url1=false;
	private $url2=false;
	private $url3=false;
	private $url4=false;
	private $version_nojs=true;
	private $compression=true;
	public function __construct($identifiant='')
	{
		return $this->id($identifiant);
	}
	public function id($identifiant='')
	{
		$identifiant=preg_replace('#[^a-z0-9]#i','',$identifiant);
		if(!empty($identifiant))
			$this->identifiant=$identifiant;
		return $this->identifiant;
	}
	public function images($url1,$url2=false,$url3=false)
	{
		$this->url1=$url1;
		$this->url2=$url2;
		$this->url3=$url3;
		$this->url4=$url4;
	}
	public function nojs($version_nojs=true)
	{
		$this->version_nojs=($version_nojs==true);
	}
	public function compression($compression=true)
	{
		$this->compression=($compression==true);
	}
	public function out($parametres='',$echo=true)
	{
		if($this->url1!==false)
			$images=urlencode($this->url1);
		else
			$images='http%3A%2F%2Fstarpush.selfbuild.fr%2Fimages%2Fetoile_g.png';
		if($this->url2!==false)
			$images.=','.urlencode($this->url2);
		else
			$images.=',http%3A%2F%2Fstarpush.selfbuild.fr%2Fimages%2Fetoile_v.png';
		if($this->url3!==false)
			$images.=','.urlencode($this->url3);
		elseif($this->url2!==false)
			$images.=','.urlencode($this->url2);
		elseif($this->url1!==false)
			$images.=','.urlencode($this->url1);
		else
			$images.=',http%3A%2F%2Fstarpush.selfbuild.fr%2Fimages%2Fetoile_b.png';

		if(false!==strpos(','.$parametres.',',',mini,'))
			$lettre='m';
		elseif(false!==strpos(','.$parametres.',',',jaime,'))
			$lettre='j';
		elseif(false!==strpos(','.$parametres.',',',pm,'))
			$lettre='pm';
		else
			$lettre='p';

		$out='<script
	type="text/javascript"
	src="http://starpush.selfbuild.fr/'.$lettre.'.js?'.$images.','.$parametres.'-'.$this->identifiant.'"
></script>';
		$float=' float:right;';
		if(false!==strpos(','.$parametres.',',',left,'))
			$float=' float:left;';
		if(false!==strpos(','.$parametres.',',',none,'))
			$float='';
		if($this->version_nojs)
		{
			$out.='
<noscript>
	<iframe
		src="http://starpush.selfbuild.fr/nojs.php?ref='.$this->identifiant.'"
		width="80"
		height="16"
		style="border:none;'.$float.'"
	></iframe>
</noscript>';
		}
		if($this->compression)
		{
			$out=preg_replace('#>\s+<#','><',$out);
			$out=preg_replace('#"\s+>#','">',$out);
			$out=preg_replace('#\s+#',' ',$out);
		}
		if($echo)
			echo $out;
		else
			return $out;
	}
	public function get($parametres='',$return=true)
	{
		return $this->out($parametres,!$return);
	}
	public function infos($tolerant=false)
	{
		$url='http://starpush.selfbuild.fr/get.php?ref='.$this->identifiant;
		$url.='&host='.urlencode($_SERVER['HTTP_HOST']);
		$donnees=@unserialize(file_get_contents($url));
		if(!isset($donnees['succes']) && !$donnees['succes'] && ($tolerant || $donnees['ref']==$this->identifiant))
			return array('ref'=>$this->identifiant,'succes'=>false);
		return $donnees;
	}
}
?>