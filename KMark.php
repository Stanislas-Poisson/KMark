<?php
#
# KMark est une adaptation de la syntaxe MarkDown, dédié au web, permettant de donner des paramètres a tous les éléments directement. 
# Copyright © 2013 Stanislas Poisson  
# http://www.stanislas-poisson.fr/
#
class KMark{
	function transition($text){
		$text=$this->cleanWhiteSpace($text);
		$text=$this->links($text);
		$text=$this->images($text);
		$text=$this->blocks($text);
		$text=$this->briste($text);
		$text=$this->stylish($text);
		return $text;
	}
	function stylish($text){
		return preg_replace_callback('/([\*_\-\/]+) ([\w\d\s"<=:\/\.>]+) (?:[\*_\-\/]+)/',array(&$this,'_stylishCompile'),$text);
	}
	function _stylishCompile($text){
		$class=array('*'=>' b','_'=>' u','-'=>' i','/'=>' d',);
		$a=$text[1];
		$b=strlen($a);
		$r='';
		for($i=0;$i<$b;$i++){$r.=$class[substr($a,$i,1)];}
		return '<span class="'.trim($r).'">'.$text[2].'</span>';
	}
	function briste($text){
		$text=preg_replace('/([\s]{2}[\r\n])/','<br>',$text);
		return $text;
	}
	function links($text){
		return preg_replace_callback('/\[([^:]*)\]:\(([^\)]*)\)/',array(&$this,'_link'),$text);
	}
	function _link($link){
		$title=$id=$class='';$alt=$link[1];$txt=$link[2];
		preg_match('/(.*) {(.*)}$/s',$link[2],$result);
		if(isset($result[1])){$txt=$result[1];$p=explode(' ',$result[2]);
			foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
			($class!='')?$class=' class="'.trim($class).'"':'';
		}
		preg_match('/(.*)[ ]*"(.*)"$/',$txt,$result);
		if(isset($result[1])){$title=' title="'.trim($result[2]).'"';$txt=$result[1];}
		preg_match('/\[([^\]]*)/s',$link[1],$result);
		if(isset($result[1])){$alt=$result[1];}
		$return='<a href="'.trim($txt).'" '.$title.$id.$class.'>'.$link[1].'</a>';
		return $return;
	}
	function images($text){
		return preg_replace_callback('/!\[([^\]]*)\]\(([^\)]*)\)/',array(&$this,'_img'),$text);
	}
	function _img($img){
		$id=$class='';$txt=$img[2];
		preg_match('/(.*) {(.*)}$/s',$img[2],$result);
		if(isset($result[1])){$txt=$result[1];$p=explode(' ',$result[2]);
			foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
			($class!='')?$class=' class="'.trim($class).'"':'';
		}
		$return='<img src="'.$txt.'" alt="'.$img[1].'"'.$id.$class.'>';
		return $return;
	}
	function blocks($text){
		$a=preg_split('/\n{2,}/',$text,-1,PREG_SPLIT_NO_EMPTY);
		foreach($a as $v){
			if(preg_match('/^([#]{1,6}) (.*)/',$v,$result)){$text=str_replace($v,$this->_helem($result),$text);}
			elseif(preg_match('/^[\+|\d\.]+\t(.*)/',$v)){$text=str_replace($v,$this->_liste($v),$text);}
			elseif(preg_match_all('/^>\t(.*)/m',$v,$result)){$text=str_replace($v,$this->_citation($result),$text);}
			elseif(preg_match_all('/[~~]{2,}([^~]*)[~~]{2,}/',$v,$result)){$text=str_replace($v,$this->_code($result),$text);}
			elseif(preg_match_all('/^(\|[^\n]*)/m',$v,$result)){$text=str_replace($v,$this->_tableau($result),$text);}
			elseif(preg_match('/([\-]{6,})/',$v)){$text=str_replace($v,'<hr>',$text);}
			else{$text=str_replace($v,$this->_paragraphe($v),$text);}
		}
		return $text;
	}
	function _paragraphe($text){$id=$class='';$txt=$text;
		preg_match('/(.*) {(.*)}$/s',$text,$result);
		if(isset($result[1])){$txt=$result[1];$p=explode(' ',$result[2]);
			foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
			($class!='')?$class=' class="'.trim($class).'"':'';
		}
		$text='<p'.$id.$class.'>'.$txt.'</p>';
		return $text;
	}
	function _tableau($text){
		$return='<table>';
		$text=preg_grep('/([\| ?\| [\-]+)$/',$text[0],PREG_GREP_INVERT);
		foreach($text as $v){$return.='<tr>';preg_match_all('/\| ([^\|]+)/',$v,$a);
			foreach($a[1] as $x){$return.='<td>'.trim($x).'</td>';}
			$return.='</tr>';
		}
		return $return.'</table>';
	}
	function _code($text){
		return '<code>'.nl2br(str_replace('	','&nbsp;&nbsp;&nbsp;&nbsp;',htmlspecialchars($text[1][0]))).'</code>';
	}
	function _citation($text){
		$id=$class=$t='';
		foreach($text[1] as $v){
			preg_match('/(.*) {(.*)}$/s',$v,$result);
			if(isset($result[1])){$v=$result[1];$p=explode(' ',$result[2]);
				foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
				($class!='')?$class=' class="'.trim($class).'"':'';
			}
			$t.=$v."\n";
		}
		$return='<blockquote'.$id.$class.'>'.$t.'</blockquote>';
		return $return;
	}
	function _liste($a){$return='';$niveau=0;$i=0;$types=$azerty=array();
		$b=preg_split('/\n(?:([\t]*)(\+|(?:\d*)\.)\t)/',$a,-1,PREG_SPLIT_DELIM_CAPTURE);
		foreach($b as $v){if(!isset($azerty[0])){$azerty[$i]=$v;$i++;}else{$azerty[$i][]=$v;if(isset($azerty[$i][2])){$i++;}}}
		foreach($azerty as $v){
			if($return!=''){
				if(strlen($v[0])==$niveau){$id=$class='';$txt=$v[2];$return.='</li>';
					preg_match('/(.*) {(.*)}/s',$v[2],$w);
					if(isset($w[1])){$txt=$w[1];$p=explode(' ',$w[2]);
						foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
						($class!='')?$class=' class="'.trim($class).'"':'';
					}$return.='<li'.$id.$class.'>'.$txt;
				}elseif(strlen($v[0])>$niveau){$id=$class='';$txt=$v[2];$niveau++;(strlen($v[1])==1)?$types[$niveau]='ul':$types[$niveau]='ol';$return.='<'.$types[$niveau].'>';
					preg_match('/(.*) {(.*)}/s',$v[2],$w);
					if(isset($w[1])){$txt=$w[1];$p=explode(' ',$w[2]);
						foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
						($class!='')?$class=' class="'.trim($class).'"':'';
					}
					$return.='<li'.$id.$class.'>'.$txt;
				}elseif(strlen($v[0])<$niveau){$id=$class='';$txt=$v[2];$return.='</'.$types[$niveau].'></li>';unset($types[$niveau]);$niveau--;
					preg_match('/(.*) {(.*)}/s',$v[2],$w);
					if(isset($w[1])){$txt=$w[1];$p=explode(' ',$w[2]);
						foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
						($class!='')?$class=' class="'.trim($class).'"':'';
					}$return.='<li'.$id.$class.'>'.$txt;
				}
			}else{$id=$class='';preg_match('/(\+|(?:\d*)\.)\t(.*)/s',$v,$x);$txt=$x[2];(strlen($x[1])==1)?$types[$niveau]='ul':$types[$niveau]='ol';$return.='<'.$types[$niveau].'>';
				preg_match('/(.*) {(.*)}/s',$x[2],$w);
				if(isset($w[1])){$txt=$w[1];$p=explode(' ',$w[2]);
					foreach($p as $po){if(substr($po,0,1)=='#'){($id=='')?$id=' id="'.substr($po,1).'"':'';}else{$class.=' '.substr($po,1);}}
					($class!='')?$class=' class="'.trim($class).'"':'';
				}$return.='<li'.$id.$class.'>'.$txt;
			}
		}
		return $return.'</li></'.$types[$niveau].'>';
	}
	function _helem($result){
		$classCss=$id='';
		preg_match('/{([#\.\w\d\s]*)}$/',$result[2],$class);
		if(count($class)!=0){
			$c=explode(' ',trim($class[1]));
			foreach($c as $x){
				if(substr($x,0,1)=='#'){($id=='')?$id=' id="'.substr($x,1).'"':'';}
				else{($classCss=='')?$classCss=' class="':'';$classCss.=substr($x,1).' ';}
			}
			($classCss!='')?$classCss=trim($classCss).'"':'';
		}
		return '<h'.strlen($result[1]).$id.$classCss.'>'.trim(str_replace($class,'',$result[2])).'</h'.strlen($result[1]).'>';
	}
	function cleanWhiteSpace($text){
        $text=str_replace("\r\n","\n",$text);
        $text=preg_replace("/\n{3,}/","\n\n",$text);
        $text=preg_replace("/\n *\n/","\n\n",$text);
        $text=preg_replace('/"$/',"\" ",$text);
		$text=preg_replace('{\r\n?}',"\n",$text);
        return $text;
    }
}
?>
