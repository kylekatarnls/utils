<?php
namespace Utils\Widgets;

class StarPush
{
	const GRAY_STAR = 'http://starpush.selfbuild.fr/images/etoile_g.png';
	const GREEN_STAR = 'http://starpush.selfbuild.fr/images/etoile_v.png';
	const BLUE_STAR = 'http://starpush.selfbuild.fr/images/etoile_b.png';
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
	public function images($url1,$url2=false,$url3=false,$url4=false)
	{
		$this->url1=$url1;
		$this->url2=$url2;
		$this->url3=$url3;
		$this->url4=$url4;
		return $this;
	}
	public function nojs($version_nojs=true)
	{
		$this->version_nojs=($version_nojs==true);
		return $this;
	}
	public function compression($compression=true)
	{
		$this->compression=($compression==true);
		return $this;
	}
	public function out($parametres='',$echo=true)
	{
		if($this->url1!==false)
			$images=urlencode($this->url1);
		else
			$images=urldecode(static::GRAY_STAR);
		if($this->url2!==false)
			$images.=','.urlencode($this->url2);
		else
			$images.=','.urldecode(static::GREEN_STAR);
		if($this->url3!==false)
			$images.=','.urlencode($this->url3);
		elseif($this->url2!==false)
			$images.=','.urlencode($this->url2);
		elseif($this->url1!==false)
			$images.=','.urlencode($this->url1);
		else
			$images.=','.urldecode(static::BLUE_STAR);

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
		if(!$echo)
			return $out;
		echo $out;
		return $this;
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
	public function microdata($name='StarPush', $txt1 = '', $txt2 = 'sur', $txt3 = 'notes', $scope=null)
	{
		if(is_null($scope))
			$scope=$this->identifiant;
		$infos=$this->infos();
		$total=intval($infos['total']);
		$moyenne=round($infos['notes']/2/max($total,1));
		return '
		<span itemscope="'.$scope.'" itemtype="http://schema.org/Product">'.
			'<span itemprop="name">StarPush</span>'.$txt1.
			'<span itemprop="aggregateRating" itemscope="'.$scope.'" itemtype="http://schema.org/AggregateRating">'.
				'<span itemprop="ratingValue">'.number_format($moyenne, 1, ',', ' ').'</span>'.$txt2.
				'<span itemprop="reviewCount">'.$total.'</span>'.$txt3.
			'</span>'.
		'</span>';
	}
}
?>