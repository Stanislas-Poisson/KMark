<?php
/**
* KMark is an adaptation of the syntax MarkDown, dedicated to Web,
* allowing to give parameters has all the elements directly.
* Validity php 7.0.1 - http://sandbox.onlinephpfunctions.com/
* Copyright © 2013 Stanislas Poisson - Licence MIT
* https://stanislas-poisson.fr
*/
namespace KMark;

class Convert{

	public function setText( string $text = '' )
	{
		$this->text = trim( $text );

		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

	private $text;

	private $_regex = [
		'stylish' => '/([\*_\-\/]+) ([\w\d\s"<=:\/\.>]+) (?:[\*_\-\/]+)/',
		'clean' => [
			'str' => [
				"\r\n" => "\n",
			],
			'preg' => [
				"/\n{3,}/" => "\n\n",
				"/\n *\n/" => "\n\n",
				'/"$/' => '\" ',
				'{\r\n?}' => "\n",
			],
		],
		'links' => '/\[([^:]*)\]:\(([^\)]*)\)/',
		'link' => [
			'idClass' => '/(.*)
			{(.*)}$/s',
			'titleText' => '/(.*)[ ]*"(.*)"$/',
			'alt' => '/\[([^\]]*)/s',
		],
		'images' => '/!\[([^\]]*)\]\(([^\)]*)\)/',
	];

	public function convert()
	{
		$this->cleanWhiteSpace();
		$this->cleanWhiteSpace()->links();
		$this->cleanWhiteSpace()->links()->images();
		$this->cleanWhiteSpace()->links()->images()->blocks();
		/*$this->cleanWhiteSpace()->links()->images()->blocks()->briste();
		/*$this->cleanWhiteSpace()->links()->images()->blocks()->briste()->stylish();*/
		return $this;
	}

	private function cleanWhiteSpace()
	{
		foreach ( $this->_regex[ 'clean' ][ 'str' ] as $key => $value ) {
			$this->text = str_replace( $key, $value, $this->text );
		}
		foreach ( $this->_regex[ 'clean' ][ 'preg' ] as $key => $value ) {
			$this->text = preg_replace( $key, $value, $this->text );
		}
		return $this;
	}

	private function links()
	{
		$this->text = preg_replace_callback( $this->_regex[ 'links' ], array( &$this, '_link' ), $this->text );
		return $this;
	}

	private function _link( array $link ): string
	{
		$title = $id = $class = '';
		$alt = $link[ 1 ];
		$txt = $link[ 2 ];
		preg_match( $this->_regex[ 'link' ][ 'idClass' ], $link[ 2 ] ,$result );
		if ( isset( $result[ 1 ] ) ) {
			$txt = $result[ 1 ];
			$p = explode( ' ', $result[ 2 ] );
			foreach ( $p as $po ) {
				if ( substr( $po, 0, 1 ) == '#' ) {
					( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
				} else {
					$class .= ' ' . substr( $po, 1 );
				}
			}
			( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
		}
		preg_match( $this->_regex[ 'link' ][ 'titleText' ], $txt, $result );
		if ( isset( $result[ 1 ] ) ) {
			$title = ' title="' . trim( $result[ 2 ] ) . '"';
			$txt = $result[ 1 ];
		}
		preg_match( $this->_regex[ 'link' ][ 'alt' ], $link[ 1 ], $result );
		if ( isset( $result[ 1 ] ) ) {
			$alt = $result[ 1 ];
		}
		return '<a href="' . trim( $txt ) . '" ' . $title . $id . $class . '>' . $link[ 1 ] . '</a>';
			}

	private function images()
	{
		$this->text = preg_replace_callback( $this->_regex[ 'images' ], array( &$this,'_img' ), $this->text );
		return $this;
	}

	private function _img( $img )
	{
		$id = $class = '';
		$txt = $img[ 2 ];
		preg_match( '/(.*) {(.*)}$/s', $img[ 2 ], $result );
		if ( isset( $result[ 1 ] ) ) {
			$txt = $result[ 1 ];
			$p = explode( ' ', $result[ 2 ] );
			foreach ( $p as $po ) {
				if ( substr( $po, 0, 1 ) == '#' ) {
					( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
				} else {
					$class .= ' ' . substr( $po, 1);
				}
			}
			( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
		}
		return'<img src="' . $txt . '" alt="' . $img[ 1 ] . '"' . $id . $class . '>';
			}

	private function blocks()
	{
		$a = preg_split( '/\n{2,}/', $this->text, -1, PREG_SPLIT_NO_EMPTY );
		foreach( $a as $v )
		{
			if ( preg_match( '/^([#]{1,6}) (.*)/', $v, $result ) )
			{
				$this->text = str_replace( $v, $this->_helem( $result ), $this->text );
			} else if( preg_match( '/^[\+|\d\.]+\t(.*)/', $v ) )
			{
				$this->text = str_replace( $v, $this->_liste( $v ), $this->text );
			} else if ( preg_match_all( '/^>\t(.*)/m', $v, $result ) )
			{
				$this->text = str_replace( $v, $this->_citation( $result ), $this->text );
			} else if ( preg_match_all( '/[~~]{2,}([^~]*)[~~]{2,}/', $v, $result ) )
			{
				$this->text = str_replace( $v, $this->_code( $result ), $this->text );
			} else if ( preg_match_all( '/^(\|[^\n]*)/m', $v, $result ) )
			{
				$this->text = str_replace( $v, $this->_tableau( $result ), $this->text );
			} else if ( preg_match( '/([\-]{6,})/', $v ) )
			{
				$this->text = str_replace( $v, '<hr>', $this->text );
			} else{
				$this->text = str_replace( $v, $this->_paragraphe( $v ), $this->text );
			}
		}
		return $this;
	}

	private function _paragraphe( $text )
	{
		$id = $class = '';
		$txt = $text;
		preg_match( '/(.*)
			{(.*)}$/s', $text, $result );
		if ( isset( $result[ 1 ] ) )
		{
			$txt = $result[ 1 ];
			$p = explode( ' ', $result[ 2 ] );
			foreach ( $p as $po )
			{
				if ( substr( $po, 0, 1 ) == '#' )
				{
					( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
				} else {
					$class .= ' ' . substr( $po, 1 );
				}
			}
			( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
		}
		return '<p' . $id . $class . '>' . $txt . '</p>';
	}

	private function _tableau( $text )
	{
		$return = '<table>';
		$text = preg_grep( '/([\| ?\| [\-]+)$/', $text[ 0 ], PREG_GREP_INVERT );
		foreach ( $text as $v )
		{
			$return .= '<tr>';
			preg_match_all( '/\| ([^\|]+)/', $v, $a );
			foreach ( $a[ 1 ] as $x )
			{
				$return .= '<td>' . trim( $x ) . '</td>';
			}
			$return .= '</tr>';
		}
		return $return . '</table>';
	}

	private function _code( $text )
	{
		return '<code>' . nl2br( str_replace( '	', '&nbsp;&nbsp;&nbsp;&nbsp;', htmlspecialchars( $text[ 1 ][ 0 ] ) ) ) . '</code>';
	}

	private function _citation( $text )
	{
		$id = $class = $t = '';
		foreach ( $text[ 1 ] as $v )
		{
			preg_match( '/(.*)
				{(.*)}$/s', $v, $result );
			if ( isset( $result[ 1 ] ) )
			{
				$v = $result[ 1 ];
				$p = explode( ' ', $result[ 2 ] );
				foreach ( $p as $po )
				{
					if ( substr( $po, 0, 1) == '#' )
					{
						( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
					} else {
						$class .= ' ' . substr( $po, 1 );
					}
				}
				( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
			}
			$t .= $v . "\n";
		}
		return '<blockquote' . $id . $class . '>' . $t . '</blockquote>';
	}

	private function _liste( $a )
	{
		$return = '';
		$niveau = 0;
		$i = 0;
		$types = $azerty = [];
		$b = preg_split( '/\n(?:([\t]*)(\+|(?:\d*)\.)\t)/', $a, -1, PREG_SPLIT_DELIM_CAPTURE );
		foreach( $b as $v )
		{
			if( !isset( $azerty[ 0 ] ) )
			{
				$azerty[ $i ] = $v;
				$i++;
			} else {
				$azerty[ $i ][] = $v;
				if ( isset( $azerty[ $i ][ 2 ] ) )
				{
					$i++;
				}
			}
		}
		foreach ( $azerty as $v )
		{
			if ( $return != '' )
			{
				if ( strlen( $v[ 0 ] ) == $niveau )
				{
					$id = $class = '';
					$txt = $v[ 2 ];
					$return .= '</li>';
					preg_match( '/(.*)
						{(.*)}/s', $v[ 2 ], $w );
					if ( isset( $w[ 1 ] ) )
					{
						$txt = $w[ 1 ];
						$p = explode( ' ', $w[ 2 ] );
						foreach ( $p as $po )
						{
							if ( substr( $po, 0, 1 ) == '#' )
							{
								( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
							} else {
								$class .= ' ' . substr( $po, 1 );
							}
						}
						( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
					}
					$return .= '<li' . $id . $class . '>' . $txt;
				} else if ( strlen( $v[ 0 ] ) > $niveau )
				{
					$id = $class = '';
					$txt = $v[ 2 ];
					$niveau++;
					( strlen( $v[ 1 ]) == 1 ) ? $types[ $niveau ] = 'ul' : $types[ $niveau ] = 'ol';
					$return .= '<' . $types[ $niveau ] . '>';
					preg_match( '/(.*)
						{(.*)}/s', $v[ 2 ], $w );
					if ( isset( $w[ 1 ] ) )
					{
						$txt = $w[ 1 ];
						$p = explode( ' ', $w[ 2 ] );
						foreach ( $p as $po )
						{
							if ( substr( $po, 0, 1 ) == '#' )
							{
								( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
							} else {
								$class .= ' ' . substr( $po, 1 );
							}
						}
						( $class != '' ) ? $class = ' class=" ' . trim( $class ) . '"' : '';
					}
					$return .= '<li' . $id . $class . '>' . $txt;
				} else if ( strlen( $v[ 0 ] ) < $niveau )
				{
					$id = $class = '';
					$txt = $v[ 2 ];
					$return .= '</' . $types[ $niveau ] . '></li>';
					unset( $types[ $niveau ] );
					$niveau--;
					preg_match( '/(.*)
						{(.*)}/s', $v[ 2 ], $w );
					if ( isset( $w[ 1 ] ) )
					{
						$txt = $w[ 1 ];
						$p = explode( ' ', $w[ 2 ] );
						foreach ( $p as $po )
						{
							if ( substr( $po, 0, 1) == '#' )
							{
								( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
							} else {
								$class .= ' ' . substr( $po , 1 );
							}
						}
						( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
					}
					$return .= '<li' . $id . $class . '>' . $txt;
				}
			} else {
				$id = $class = '';
				preg_match( '/(\+|(?:\d*)\.)\t(.*)/s', $v, $x );
				$txt = $x[ 2 ];
				( strlen( $x[ 1 ] ) == 1 ) ? $types[ $niveau ] = 'ul' : $types[ $niveau ] = 'ol';
				$return .= '<' . $types[ $niveau ] . '>';
				preg_match( '/(.*){(.*)}/s', $x[ 2 ], $w );
				if ( isset( $w[ 1 ] ) )
				{
					$txt = $w[ 1 ];
					$p = explode( ' ', $w[ 2 ] );
					foreach ( $p as $po )
					{
						if( substr( $po, 0, 1 ) == '#' )
						{
							( $id == '' ) ? $id = ' id="' . substr( $po, 1 ) . '"' : '';
						} else {
							$class .= ' ' . substr( $po, 1 );
						}
					}
					( $class != '' ) ? $class = ' class="' . trim( $class ) . '"' : '';
				}
				$return .= '<li' . $id . $class . '>' . $txt;
			}
		}
		return $return . '</li></' . $types[ $niveau ] . '>';
	}

	private function _helem( $result )
	{
		$classCss = $id = '';
		preg_match( '/{([#\.\w\d\s]*)}$/', $result[ 2 ], $class );
		if ( count( $class ) != 0 )
		{
			$c = explode( ' ', trim( $class[ 1 ] ) );
			foreach ( $c as $x )
			{
				if ( substr( $x, 0, 1 ) == '#' )
				{
					( $id == '' ) ? $id = ' id="' . substr( $x, 1 ) . '"' : '';
				} else {
					( $classCss == '' ) ? $classCss = ' class="' : '';
					$classCss .= substr( $x, 1 ) . ' ';
				}
			}
			( $classCss != '' ) ? $classCss = trim( $classCss ) . '"' : '';
		}
		return '<h' . strlen( $result[ 1 ] ) . $id . $classCss . '>' . trim( str_replace( $class, '', $result[ 2 ] ) ) . '</h' . strlen( $result[ 1 ] ) . '>';
	}

	private function briste()
	{
		$this->text = preg_replace( '/([\s]{2}[\r\n])/', '<br>' ,$this->text);
		return $this;
	}

	private function stylish()
	{
		$this->text = preg_replace_callback( $this->_regex[ 'stylish' ], array( &$this,'_stylishCompile' ), $this->text );
	}

	private function _stylishCompile( $text )
	{
		$class = [
			'*' => ' b',
			'_' => ' u',
			'-' => ' i',
			'/' => ' d',
		];
		$a = $text[ 1 ];
		$b = strlen( $a );
		$r = '';
		for ( $i = 0; $i < $b; $i++ )
		{
			$r .= $class[ substr( $a, $i, 1 ) ];
		}
		return '<span class="' . trim( $r ) . '">' . $text[ 2 ] . '</span>';
	}
}
?>
