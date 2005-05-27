<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
#
# Copyright 2004 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

# IRC-Nick des Bots
$nick = ereg_replace( '/[^/]*[.]php$', '', $_SERVER[ 'PHP_SELF' ] );
$nick = ereg_replace( '^/(.*/)*', '', $nick );
#$nick = 'XDCC|'.$nick;
#$nick = 'XDCC|irofferbot';

# Statusfiles der bots
$filenames = array(
	'mybot.state',
);

$cache_file = 'size.data';
$default_group = '.neu';
$base_path = './';

$javascript = 1;

$strip_in_names = array (
	'^ *- *',
	"\002",
	"\0030[,]0",
	"\0030[,]5",
	"\0030",
	"\00312",
	"\00314",
	"\00315",
	"\0033",
	"\0034",
	"\0035\037",
	"\0037",
	"\0032",
	"\00310",
	"\003",
	"\017",
);

?>
<html>
<head>
<meta name="generator" content="iroffer-state 1.3, iroffer.dinoex.net">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="de-de">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="iroffer-state.css">
<title><?php echo $nick; ?></title>
<?php
if ( $javascript > 0 ) {
	echo '
<script language=javascript type=text/javascript>
<!--
function selectThis(src) {
    document.selection.clear;
    txt = eval(src +".innerText");
    theObj = document.all(txt);
    txtRange = document.body.createTextRange();
    txtRange.moveToElementText(eval(src));
    txtRange.select();
    txtRange.execCommand("RemoveFormat");
    txtRange.execCommand("Copy");
    alert(txt + " wurde in die Zwischenablage kopiert");
}
-->
</script>
	';
}
?>
</head>
<body>
<center>

<?php

#
# bytes in lesbarere Form ausgeben.
#
function makesize( $nbytes ) {
	global $debug;

	if ( $nbytes < 1000 ) {
		return sprintf( '%db', $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dk', $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $debug != '' ) {
		return sprintf( '%dM', $nbytes );
	}
	if ( $nbytes < 1000 ) {
		return sprintf( '%dM', $nbytes );
	}
	if ( $nbytes < 10000 ) {
		return sprintf( '%.1fG', $nbytes / 1024 );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dG', $nbytes );
	}
	if ( $nbytes < 10000 ) {
		return sprintf( '%.1fT', $nbytes / 1024 );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dT', $nbytes );
	}
	return sprintf( '%dE', $nbytes );
}

function clean_names( $text2 ) {
	global $strip_in_names;

	foreach ( $strip_in_names as $skey => $sdata) {
		$text2 = ereg_replace( $sdata, '', $text2 );
	}
	return $text2;
}

function read_sizecache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;

	$sizecache_dirty = 0;
	$len = filesize($filename);
	if ( $len <= 0 ) 
		return;
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$tread = fread($fp, $len);
		fclose($fp);
		$tlines = explode("\n", $tread);
		foreach ( $tlines as $ykey => $ydata) {
			if ( ereg( '[:]', $ydata ) ) {
				list( $key, $tsize ) = explode(':', $ydata, 2);
				$sizecache[ $key ] = $tsize;
			}
		}
	}
}

function write_sizecache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;

	if ( $sizecache_dirty == 0 )
		return;
	$fp = fopen( $filename, 'w' );
	if ( $fp ) {
		foreach ( $sizecache as $key => $ydata ) {
			fwrite( $fp, $key.':'.$ydata."\n" );
		}
		fclose($fp);
	}
}

function filesize_cache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;
	global $base_path;

	if ( isset( $sizecache[ $filename ] ) ) {
		return $sizecache[ $filename ];
	}
	$localfile = $filename;
	if ( !ereg( '^/', $filename ) )
		$localfile = $base_path.$filename;
	$tsize = filesize( $localfile );
	$sizecache[ $filename ] = $tsize;
	$sizecache_dirty ++;
	return $tsize;
}

function cgi_escape( $string ) {
	$string = ereg_replace( '[&]', '%26', $string );
	$string = ereg_replace( '[+]', '%2B', $string );
	return $string;
}

function make_self_more() {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($_GET[ 'group' ]);
		$par ++;
	}
	if ( !isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen=1';
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function make_self_order( $order ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($_GET[ 'group' ]);
		$par ++;
	}
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( $order != '' ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$order;
		$par ++;
	}
	return $link;
}

function make_self_group( $group ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( $group != '' ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($group);
		$par ++;
	}
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function make_self_back( $order ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function read_removed( $statefile ) {
	global $total;
	global $seen;

	$filename = ereg_replace( '[.]state$', '.removed.xdcc', $statefile );

	if ( !file_exists( $filename ) )
		return;

	$read = '';
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$read .= fread($fp, filesize ($filename));
		fclose($fp);
	}

	$datalines = explode("\n", $read);
	foreach ( $datalines as $key => $data) {
		if ( $data == '' )
			continue;

		if ( ereg( '^Do Not Edit This File[:] ', $data ) ) {
			list( $key, $text ) = explode(': ', $data, 2);
			list( $irec, $iband, $itotal, $irest ) = explode(' ', $text, 4);
			$total[ 'downl' ] += $itotal;
			continue;
		}
		if ( !ereg( ' ', $data ) )
			continue;

		list( $key, $text ) = explode(' ', $data, 2);
		if ( $text == '' )
			continue;

		if ( $key == 'xx_file' ) {
			$fsize = filesize_cache( $text );
			if ( isset( $seen[ $text ] ) )
				continue;
			$seen[ $text ] = 0;
			$total[ 'packs' ] ++;
			$total[ 'size' ] += $fsize;
		}
		if ( $key == 'xx_gets' ) {
			$total[ 'xx_gets' ] += $text;
			$total[ 'trans' ] += $fsize * $text;
		}
	}
}

function read_status( $statefile ) {
	global $total;

	$filename = ereg_replace( '[.]state$', '.txt', $statefile );

	if ( !file_exists( $filename ) )
		return;

	$read = '';
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$read .= fread($fp, filesize ($filename));
		fclose($fp);
	}

	$datalines = explode("\n", $read);
	foreach ( $datalines as $key => $data) {
		if ( $data == '' )
			continue;

		if ( ereg( '^ *[*]*  [0-9]* packs ', $data ) ) {
			$words = explode(' ', $data);

			if ( !isset( $total[ 'freeslots' ] ) )
				$total[ 'freeslots' ] = $words[ 9 ];
			if ( !isset( $total[ 'maxslots' ] ) )
				$total[ 'maxslots' ] = $words[ 11 ];

			for ( $i = 14; isset( $words[ $i ]); $i += 2 ) {
				switch ( $words[ $i ] ) {
				case 'Queue:':
					if ( !isset( $total[ 'queue' ] ) )
						$total[ 'queue' ] = $words[ $i + 1 ];
					break;
				case 'Min:':
					if ( !isset( $total[ 'minspeed' ] ) )
						$total[ 'minspeed' ] = $words[ $i + 1 ];
					break;
				case 'Max:':
					if ( !isset( $total[ 'maxspeed' ] ) )
						$total[ 'maxspeed' ] = $words[ $i + 1 ];
					break;
				case 'Record:':
					if ( !isset( $total[ 'record' ] ) )
						$total[ 'record' ] = $words[ $i + 1 ];
					break;
				}
			}
			continue;
		}
		if ( ereg( '^ *[*]*  Bandwidth Usage ', $data ) ) {
			$words = explode(' ', $data);
			if ( !isset( $total[ 'current' ] ) )
				$total[ 'current' ] = $words[ 9 ];
			
			for ( $i = 10; isset( $words[ $i ]); $i += 2 ) {
				switch ( $words[ $i ] ) {
				case 'Cap:':
					if ( !isset( $total[ 'cap' ] ) )
						$total[ 'cap' ] = $words[ $i + 1 ];
					break;
				case 'Record:':
					if ( !isset( $total[ 'send' ] ) )
						$total[ 'send' ] = $words[ $i + 1 ];
					break;
				}
			}
			break;
		}
		continue;
	}
}

function get_long( $string ) {
	$l = ord( substr( $string, 0, 1 ) );
	$l = $l * 256;
	$l += ord( substr( $string, 1, 1 ) );
	$l = $l * 256;
	$l += ord( substr( $string, 2, 1 ) );
	$l = $l * 256;
	$l += ord( substr( $string, 3, 1 ) );
	return $l;
}

function get_xlong( $string ) {
	$l = (float)get_long( substr( $string, 0, 4 ) );
	$l = $l * 4294967296.0;
	$l += (float)get_long( substr( $string, 4, 4 ) );
	return $l;
}

function get_text( $string ) {
	return substr( $string, 0, strlen( $string ) - 1 );
}

function update_group( $gr, $fpacks, $newfile, $tgets, $ttrans ) {
	global $info;
	global $gruppen;

	$info[ $fpacks ][ 'xx_data' ] = $gr;
	if ( !isset( $gruppen[ $gr ][ 'packs' ] ) ) {
		$gruppen[ $gr ][ 'packs' ] = 0;
		$gruppen[ $gr ][ 'size' ] = 0;
		$gruppen[ $gr ][ 'xx_gets' ] = 0;
		$gruppen[ $gr ][ 'trans' ] = 0;
	}
	$gruppen[ $gr ][ 'xx_gets' ] += $tgets;
	$gruppen[ $gr ][ 'trans' ] += $ttrans;
	if ( $newfile != 0 ) {
		$gruppen[ $gr ][ 'packs' ] ++;
		$gruppen[ $gr ][ 'size' ] += $info[ $fpacks ][ 'size' ];
	}
}

read_sizecache( $cache_file );

$support_groups = 0;
$nick2 = ereg_replace( '[^A-Za-z_0-9]', '', $nick );
$packs = 0;
$fpacks = 0;
$newfile = 0;
$nogroup = 0;
$total[ 'packs' ] = 0;
$total[ 'size' ] = 0;
$total[ 'downl' ] = 0;
$total[ 'xx_gets' ] = 0;
$total[ 'trans' ] = 0;
$total[ 'uptime' ] = 0;
$total[ 'daily' ] = 0;
$total[ 'weekly' ] = 0;
$total[ 'monthly' ] = 0;
$gruppen[ '*' ][ 'packs' ] = 0;
$gruppen[ '*' ][ 'size' ] = 0;
$gruppen[ '*' ][ 'xx_gets' ] = 0;
$gruppen[ '*' ][ 'trans' ] = 0;

# Status aller Bots lesen
foreach ( $filenames as $key => $filename) {
	read_removed( $filename );
	read_status( $filename );

	$filebytes = 0;
	$filedata = '';
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$filebytes = filesize ($filename);
		$filedata = fread($fp, $filebytes);
		fclose($fp);
	}

	$filebytes -= 16; # MD%
	for ( $i=8; $i < $filebytes; ) {
		$tag = get_long( substr( $filedata, $i, 4 ) );
		$len = get_long( substr( $filedata, $i + 4, 4 ) );
		if ( $len <= 8 ) {
			printf( ':tag=%d<br>', $tag );
			printf( ":len=%d<br>", $len );
			print 'Warning: parsing statfile aborted<br>';
			$i = $filebytes;
			break;
		}
		switch ($tag) {
		case 256: # IROFFER_VERSION
			if ( isset( $total[ 'version' ] ) )
				break;
			$text = substr( $filedata, $i + 8, $len - 8 );
			$tver = clean_names( $text );
			$tver = ereg_replace( ',.*$', '', $tver );
			$tver = ereg_replace( '\[.*\]', '', $tver );
			$total[ 'version' ] = $tver;
			break;
		case 257: # TIMESTAMP
			$text = get_long( substr( $filedata, $i + 8, 4 ) );
			if ( isset( $total[ 'time' ] ) ) {
				if ( $total[ 'time' ] > $text )
					break;
			}
			$total[ 'time' ] = $text;
			break;
		case 514: # TOTAL_SENT
			$text = substr( $filedata, $i + 8, $len - 8 );
			$itotal = get_xlong( $text );
			$total[ 'downl' ] += $itotal;
			$packs = 0;
			break;
		case 515: # TOTAL_UPTIME
			$text = substr( $filedata, $i + 8, $len - 8 );
			$itotal = get_xlong( $text );
			$total[ 'uptime' ] += $itotal;
			break;
		case 3072: # XDCCS
			$chunkdata = substr( $filedata, $i, $len );
			for ( $j=8; $j < $len; ) {
				$jtag = get_long( substr( $chunkdata, $j, 4 ) );
				$jlen = get_long( substr( $chunkdata, $j + 4, 4 ) );
				if ( $jlen <= 8 ) {
					printf( ':xtag=%d<br>', $jtag );
					printf( ':xlen=%d<br>', $jlen );
					print 'Warning: parsing statfile aborted<br>';
					$j = $len;
					break;
				}
				switch ($jtag) {
				case 0:
					$j = $len;
					break;
				case 3073: # FILE
					if ( $nogroup != 0 )
						update_group( $default_group, $fpacks, $newfile, $tgets, $ttrans );
					$nogroup = 1;
					$newfile = 0;
					$packs ++;
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$fsize = filesize_cache( $text );
					if ( !isset( $seen[ $text ] ) ) {
						$newfile = 1;
						$seen[ $text ] = $packs;
						$total[ 'packs' ] ++;
						$total[ 'size' ] += $fsize;
						$gruppen[ '*' ][ 'packs' ] ++;
						$gruppen[ '*' ][ 'size' ] += $fsize;
					}
					$fpacks = $seen[ $text ];
					$info[ $fpacks ][ 'pack' ] = $fpacks;
					$info[ $fpacks ][ 'size' ] = $fsize;
					if ( !isset( $info[ $fpacks ][ 'xx_gets' ] ) ) {
						$info[ $fpacks ][ 'xx_gets' ] = 0;
						$info[ $fpacks ][ 'trans' ] = 0;
					}
					break;
				case 3074: # DESC
					if ( isset( $info[ $fpacks ][ 'xx_desc' ] ) )
						break;
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$info[ $fpacks ][ 'xx_desc' ] = clean_names( $text );
					break;
				case 3075: # NOTE
					if ( isset( $info[ $fpacks ][ 'xx_note' ] ) )
						break;
					$text = substr( $chunkdata, $j + 8, $jlen - 8 );
					$tnote = clean_names( $text );
					if ( $tnote == "" )
						break;
					$info[ $fpacks ][ 'xx_note' ] = $tnote;
					break;
				case 3076: # GETS
					$text = substr( $chunkdata, $j + 8, $jlen - 8 );
					$tgets = get_long( $text );
					$ttrans = $info[ $fpacks ][ 'size' ] * $tgets;
					$info[ $fpacks ][ 'xx_gets' ] += $tgets;
					$info[ $fpacks ][ 'trans' ] += $ttrans;
					$total[ 'xx_gets' ] += $tgets;
					$total[ 'trans' ] += $ttrans;
					$gruppen[ '*' ][ 'xx_gets' ] += $tgets;
					$gruppen[ '*' ][ 'trans' ] += $ttrans;
					break;
				case 3079: # MD5SUM_INFO
					$tmd5a = get_long( substr( $chunkdata, $j + 36, 4 ) );
					$tmd5b = get_long( substr( $chunkdata, $j + 40, 4 ) );
					$tmd5c = get_long( substr( $chunkdata, $j + 44, 4 ) );
					$tmd5d = get_long( substr( $chunkdata, $j + 48, 4 ) );
					$tmd5 = sprintf( '%08lx%08lx%08lx%08lx',
						$tmd5a, $tmd5b, $tmd5c, $tmd5d );
					$info[ $fpacks ][ 'xx_md5' ] = $tmd5;
					break;
				case 3080: # GROUP NAME
					$nogroup = 0;
					$support_groups = 1;
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$gr = $text;
					update_group( $gr, $fpacks, $newfile, $tgets, $ttrans );
					break;
				case 3081: # GROUP DESC
					if ( isset( $gruppen[ $gr ][ 'xx_trno' ] ) )
						break;
					$text = substr( $chunkdata, $j + 8, $jlen - 8 );
					$gruppen[ $gr ][ 'xx_trno' ] = clean_names( $text );
					break;
				case 3082: # LOCK
					$info[ $fpacks ][ 'xx_lock' ] = 1;
					break;
				}
				$j += $jlen;
				$r = $jlen % 4;
				if ( $r > 0 )
					$j += 4 - $r;
			}
			break;
		case 3328: # TLIMIT_DAILY_USED
			$text = substr( $filedata, $i + 8, $len - 8 );
			$traffic = get_xlong( $text );
			$total[ 'daily' ] += $traffic;
			break;
		case 3330: # TLIMIT_WEEKLY_USED
			$text = substr( $filedata, $i + 8, $len - 8 );
			$traffic = get_xlong( $text );
			$total[ 'weekly' ] += $traffic;
			break;
		case 3332: # TLIMIT_MONTHLY_USED
			$text = substr( $filedata, $i + 8, $len - 8 );
			$traffic = get_xlong( $text );
			$total[ 'monthly' ] += $traffic;
			break;
		}
		$i += $len;
		$r = $len % 4;
		if ( $r > 0 )
			$i += 4 - $r;
	}
}
if ( ( $nogroup != 0 ) && ( $support_groups != 0 ) )
	update_group( $default_group, $fpacks, $newfile, $tgets, $ttrans );

write_sizecache( $cache_file );

if ( $support_groups == 0 )
	$_GET[ 'group' ] = '*';

# Ueberschrift:
if ( isset( $_GET[ 'group' ] ) ) {
	echo '<h1>'.$nick." Datei-Liste</h1>\n";
	echo "\n";
	echo '<p>Download im IRC mit "/msg '.$nick.' xdcc send #nummer"</p>';
	echo "\n";
} else {
	echo '<h1>'.$nick." Gruppen-Liste</h1>\n";
	echo "\n";
}

?>

<table cellpadding="2" cellspacing="0" summary="list">
<thead>

<?php


if ( isset( $_GET[ 'group' ] ) ) {
	$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.make_self_order( '' ).'">PACK</a>';
	$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hsize = '<a class="head" title="sortieren nach Göße der Files"
href="'.make_self_order( 'size' ).'">GRÖSSE</a>';

	if ( !isset( $_GET[ 'order' ] ) ) {
		foreach ( $info as $key => $data)
			$ausgabe[ $key ] = $key;
		asort( $ausgabe );
		$hpack = 'PACK';
	} else {
		$ofound = 0;
		if ( $_GET[ 'order' ] == 'gets' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'xx_gets' ];
			arsort( $ausgabe );
			$hgets = 'DLs';
			$ofound = 1;
		}
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GRÖSSE';
			$ofound = 1;
		}
		if ( $ofound == 0 ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $key;
			asort( $ausgabe );
			$hpack = 'PACK';
		}
	}
	$linkmore = '&nbsp;<a title="zurück" href="'.make_self_back( '' ).'">(zurück)</a>';

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
<th class="head">'.$hsize.'</th>
<th class="head">BESCHREIBUNG'.$linkmore.'</th>
</tr>
</thead>
';

	$gr = $_GET[ 'group' ];
	$tpacks = $gruppen[ $gr ][ 'packs' ];
	$tsize = $gruppen[ $gr ][ 'size' ];

	echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$gruppen[ $gr ][ 'xx_gets' ].'</th>
<th class="right">'.makesize($tsize).'</th>
<th class="head">['.makesize($gruppen[ $gr ][ 'trans' ]).'] vollständig heruntergeladen</th>
</tr>
</tfoot>
<tbody>
';

	foreach ( $ausgabe as $key => $data) {
		if ( $key == '' )
			continue;
		if ( ( $_GET[ 'group' ] != '*' )
		&& ( $info[ $key ][ 'xx_data' ] != $_GET[ 'group' ] ) )
			continue;

		$tpack = $info[ $key ][ 'pack' ];
		$tname = $info[ $key ][ 'xx_desc' ];
		$jsid= $nick2.'_'.$tpack;

		if ( isset( $info[ $key ][ 'xx_lock' ] ) )
			$tname .= ' (gesperrt)';
		$tname = htmlspecialchars( $tname);
		if ( $javascript > 0 ) {
			$tname = '<span class="selectable" onclick=javascript:selectThis(\''.
				$jsid.'\');>'.
				$tname."</span>\n".
				'<span id="'.$jsid.'" class="hidden">'.
				'/msg '.$nick.' xdcc send #'.$tpack."</span>\n";
		}
		if ( isset( $info[ $key ][ 'xx_note' ] ) )
			$tname .= '<br>'.$info[ $key ][ 'xx_note' ];

		$label = "Download mit:\n/msg ".$nick.' xdcc send #'.$tpack."\n";
		if ( isset( $info[ $key ][ 'xx_md5' ] ) )
			$label .= "\nmd5: ".$info[ $key ][ 'xx_md5' ];

		echo '
<tr>
<td class="right">#'.$tpack.'</td>
<td class="right">'.$info[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($info[ $key ][ 'size' ]).'</td>
<td class="content" title="'.$label.'">'.$tname.'</td>
</tr>
';
	}

} else {
	$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.make_self_order( 'pack' ).'">PACKs</a>';
	$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hrget = '<a class="head" title="sortieren nach Downloads per Datei"
href="'.make_self_order( 'rget' ).'">DLs/Pack</a>';
	$hsize = '<a class="head" title="sortieren nach Göße der Files"
href="'.make_self_order( 'size' ).'">GRÖSSE</a>';
	$htvol = '<a class="head" title="sortieren nach Übertragusngsvolumen"
href="'.make_self_order( 'tvol' ).'">Volumen</a>';
	$hname = '<a class="head" title="sortieren nach Guppe"
href="'.make_self_order( '' ).'">GRUPPE</a>';

	if ( !isset( $_GET[ 'order' ] ) ) {
		foreach ( $gruppen as $key => $data)
			$ausgabe[ $key ] = $key;
		asort( $ausgabe );
		$hname = 'GRUPPE';
	} else {
		if ( $_GET[ 'order' ] == 'pack' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'packs' ];
			arsort( $ausgabe );
			$hpack = 'PACKs';
		}
		if ( $_GET[ 'order' ] == 'gets' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'xx_gets' ];
			arsort( $ausgabe );
			$hgets = 'DLs';
		}
		if ( $_GET[ 'order' ] == 'rget' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'xx_gets' ] / $gruppen[ $key ][ 'packs' ];
			arsort( $ausgabe );
			$hrget = 'DLs/Pack';
		}
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GRÖSSE';
		}
		if ( $_GET[ 'order' ] == 'tvol' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'trans' ];
			arsort( $ausgabe );
			$htvol = 'Volumen';
		}
	}

	$tvol1 = '';
	$rget1 = '';
	if ( isset( $_GET[ 'volumen' ] ) ) {
		$tvol1 = '<th class="head">'.$htvol.'</th>';
		$rget1 = '<th class="head">'.$hrget.'</th>';
		$linkmore = '&nbsp;<a title="Volumen ausblenden" href="'.make_self_more().'">(weniger)</a>';
	} else {
		$linkmore = '&nbsp;<a title="Volumen anzeigen" href="'.make_self_more().'">(mehr)</a>';
	}

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
'.$rget1.'
<th class="head">'.$hsize.'</th>
'.$tvol1.'
<th class="head">'.$hname.'</th>
<th class="head">BESCHREIBUNG'.$linkmore.'</th>
</tr>
</thead>
';

	$tpacks = $total[ 'packs' ];
	$tsize = $total[ 'size' ];
	$part = $total[ 'downl' ] - $total[ 'trans' ];
	$tcount = count($gruppen) - 1;

	$tvol2 = '';
	$rget2 = '';
	if ( isset( $_GET[ 'volumen' ] ) ) {
		$tvol2 = '<th class="right">'.makesize($total[ 'trans' ]).'</th>';
		$getsperpack = sprintf( '%.1f', $total[ 'xx_gets' ] / $tpacks );
		$rget2 = '<th class="right">'.$getsperpack.'</th>';
	}

	echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$total[ 'xx_gets' ].'</th>
'.$rget2.'
<th class="right">'.makesize($tsize).'</th>
'.$tvol2.'
<th class="head">'.$tcount.'</th>
<th class="head"><a title="alle Packs in einer Liste anzeigen" href="'.make_self_group( '*' ).'">alle Packs</a> ['.makesize($total[ 'trans' ]).'] vollständig heruntergeladen, ['.makesize($part).']&nbsp;unvollständig</th>
</tr>
</tfoot>
<tbody>
';

	foreach ( $ausgabe as $key => $data) {
		if ( $key == '' )
			continue;
		if ( $key == '*' )
			continue;

		$tpacks= $gruppen[ $key ][ 'packs' ];
		$asize = $gruppen[ $key ][ 'size' ];
		$tsize = $gruppen[ $key ][ 'trans' ];
		$tname = $key;
		if ( isset( $gruppen[ $key ][ 'xx_trno' ] ) )
			$tname = $gruppen[ $key ][ 'xx_trno' ];
		$link = make_self_group( $key );

		$tvol3 = '';
		$rget3 = '';
		if ( isset( $_GET[ 'volumen' ] ) ) {
			$tvol3 = '<td class="right">'.makesize($tsize).'</td>';
			$getsperpack = sprintf( '%.1f', $gruppen[ $key ][ 'xx_gets' ] / $tpacks );
			$rget3 = '<td class="right">'.$getsperpack.'</td>';
		}
		echo '
<tr>
<td class="right">'.$tpacks.'</td>
<td class="right">'.$gruppen[ $key ][ 'xx_gets' ].'</td>
'.$rget3.'
<td class="right">'.makesize($asize).'</td>
'.$tvol3.'
<td class="content">'.htmlspecialchars($key).'</td>
<td class="content"><a title="Liste dieser Packs anzeigen" href="'.$link.'">'.htmlspecialchars($tname).'</a></td>
</tr>
';
	}

}

?>

</tbody>
</table>
<table class="status">
<tbody>
<tr><td>Version</td>
<?php

$traffic = array (
	'daily' => "Traffic heute",
	'weekly' => "Traffic diese Woche",
	'monthly' => "Traffic diesem Monat",
);

$label = '';
foreach ( $traffic as $skey => $sdata) {
	if ( !isset( $total[ $skey ] ) )
		continue;
	$label .= sprintf( "%6s %s\n", makesize($total[ $skey ]), $sdata );
}
echo '<td title="'.$label.'">'.$total[ 'version' ]."</td></tr>\n";

$statistik = array (
#	'version' => 'Version',
#	'uptime' => 'Online',
#	'time' => 'letztes Update',
	'freeslots' => 'Freie Slots',
#	'maxslots' => 'Anzahl Slots',
	'queue' => 'Warteschlange',
#	'minspeed' => 'Mindest-Rate',
#	'maxspeed' => 'Maximale-Rate',
	'current' => 'Aktuelle Bandbreite',
#	'cap' => 'Maximale Bandbreite',
#	'record' => 'Rekord-Rate',
#	'send' => 'Rekord-Download',
#	'daily' => "Traffic heute",
#	'weekly' => "Traffic diese Woche",
#	'monthly' => "Traffic diesem Monat",
);

foreach ( $statistik as $skey => $sdata) {
	if ( !isset( $total[ $skey ] ) )
		continue;
	echo '<tr><td>'.$sdata."</td>\n";
	echo '<td>'.$total[ $skey ]."</td></tr>\n";
}

?>
</tbody>
</table>
</center>
</body>
</html>

