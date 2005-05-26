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
$nick = ereg_replace( "/[^/]*[.]php$", '', $_SERVER[ "PHP_SELF" ] );
$nick = ereg_replace( "^/(.*/)*", '', $nick );
#$nick = "XDCC|".$nick;
#$nick = 'XDCC|irofferbot';

# Laufern mehr als ein Bot mit den gleichen daten?
$servers = 1;

# force to show no group
$_GET[ 'group' ] = '*';

# Statusfiles der bots
$filenames = array(
	'mybot.state',
);

$cache_file = "size.data";

$highlight_color = '#81A381';

$strip_in_names = array (
	"^ *- *",
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
<style type="text/css">
body{
background-color: #ffffff;
color: #000000;
height: 100%;
font-family: Arial;
font-size:  small;
text-align: center;
}
a.head {
background-color: #81A381;
color: #ffffff;
font-family: Verdana,Arial,Tahoma,sans-serif;
font-size: 9pt;
font-weight: bold;
text-align: left;
}
th.head {
background-color: #81A381;
color: #ffffff;
font-family: Verdana,Arial,Tahoma,sans-serif;
font-size: 9pt;
font-weight: bold;
text-align: left;
padding-left: 5px;
padding-right: 5px;
}
th.right {
background-color: #81A381;
color: #ffffff;
font-family: Verdana,Arial,Tahoma,sans-serif;
font-size: 9pt;
font-weight: bold;
text-align: right;
padding-left: 5px;
padding-right: 5px;
}
td.content {
background-color: #eafaea;
color: #252525;
font-family: Verdana,Arial,Tahoma;
font-size: 10pt;
border-bottom: 1px solid #cccccc;
text-align: left;
padding-left: 5px;
padding-right: 5px;
}
td.right {
background-color: #eafaea;
color: #252525;
font-family: Verdana,Arial,Tahoma;
font-size: 10pt;
border-bottom: 1px solid #cccccc;
text-align: right;
padding-left: 5px;
padding-right: 5px;
}
#list h1 {
font-family: Verdana,Arial,Tahoma;
font-size: 14pt;
color: #a0a0a0;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="de-de">
<link rel="icon" href="/favicon.ico">
<title><?php echo $nick; ?></title>
<script language=javascript type=text/javascript>
function highlight(which,color){
if (document.all||document.getElementById)
    which.style.backgroundColor=color
}
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
</script>
</head>
<body>
<center>

<?php

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

#
# bytes in lesbarere Form ausgeben.
#
function makesize( $nbytes ) {
	global $debug;

	if ( $nbytes < 1000 ) {
		return sprintf( "%3db", $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( "%3dk", $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $debug != "" ) {
		return sprintf( "% dM", $nbytes );
	}
	if ( $nbytes < 1000 ) {
		return sprintf( "%3dM", $nbytes );
	}
	if ( $nbytes < 10000 ) {
		return sprintf( "%3.1fG", $nbytes / 1024 );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( "%3dG", $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( "%3dT", $nbytes );
	}
	return sprintf( "%3dE", $nbytes );
}

function clean_names( $text2 ) {
	global $strip_in_names;

	foreach ( $strip_in_names as $skey => $sdata) {
		$text2 = ereg_replace( $sdata, "", $text2 );
	}
	return ereg_replace( "[&]", "&amp;", $text2 );
}

function read_sizecache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;

	$sizecache_dirty = 0;
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$tread = fread($fp, filesize ($filename));
		fclose($fp);
		$tlines = explode("\n", $tread);
		foreach ( $tlines as $ykey => $ydata) {
			if ( ereg( "[:]", $ydata ) ) {
				list( $key, $tsize ) = explode(":", $ydata, 2);
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

	if ( isset( $sizecache[ $filename ] ) ) {
		return $sizecache[ $filename ];
	}
	$tsize = filesize( $filename );
	$sizecache[ $filename ] = $tsize;
	$sizecache_dirty ++;
	return $tsize;
}

function make_self_more() {
	$par = 0;
	$link = $_SERVER[ "PHP_SELF" ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'group='.$_GET[ 'group' ];
		$par ++;
	}
	if ( !isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'volumen=1';
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function make_self_order( $order ) {
	$par = 0;
	$link = $_SERVER[ "PHP_SELF" ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'group='.$_GET[ 'group' ];
		$par ++;
	}
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( $order != '' ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&';
		$link .= 'order='.$order;
		$par ++;
	}
	return $link;
}

function get_long( $string ) {
	$l = ord( substr( $string, 0, 1 ) );
	$l = $l << 8;
	$l += ord( substr( $string, 1, 1 ) );
	$l = $l << 8;
	$l += ord( substr( $string, 2, 1 ) );
	$l = $l << 8;
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

read_sizecache( $cache_file );

$nick2 = ereg_replace( "[^A-Za-z_0-9]", '', $nick );
$packs = 0;
$total[ 'packs' ] = 0;
$total[ 'size' ] = 0;
$total[ 'downl' ] = 0;
$total[ 'xx_gets' ] = 0;
$total[ 'trans' ] = 0;
$gruppen[ '*' ][ 'packs' ] = 0;
$gruppen[ '*' ][ 'size' ] = 0;
$gruppen[ '*' ][ 'xx_gets' ] = 0;
$gruppen[ '*' ][ 'trans' ] = 0;

# Status aller Bots lesen
foreach ( $filenames as $key => $filename) {
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
			printf( ":tag=%d<br>", $tag );
			printf( ":len=%d<br>", $len );
			print 'Warning: parsing statfile aborted<br>';
			$i = $filebytes;
			break;
		}
		switch ($tag) {
		case 256: # IROFFER_VERSION
		case 257: # TIMESTAMP
		case 512: # XFR_RECORD
		case 513: # SENT_RECORD
		case 515: # TOTAL_UPTIME
		case 516: # LAST_LOGROTATE
		case 2816: #
			break;
		case 514: # TOTAL_SENT
			$text = substr( $filedata, $i + 8, $len - 8 );
			$itotal = get_xlong( $text );
			$total[ 'downl' ] += $itotal;
			$packs = 0;
			break;
		case 3072: # XDCCS
			$chunkdata = substr( $filedata, $i, $len );
			for ( $j=8; $j < $len; ) {
				$jtag = get_long( substr( $chunkdata, $j, 4 ) );
				$jlen = get_long( substr( $chunkdata, $j + 4, 4 ) );
				if ( $jlen <= 8 ) {
					printf( ":xtag=%d<br>", $jtag );
					printf( ":xlen=%d<br>", $jlen );
					print 'Warning: parsing statfile aborted<br>';
					$j = $len;
					break;
				}
				switch ($jtag) {
				case 0:
					$j = $len;
					break;
				case 3077: # MINSPEED
				case 3078: # MAXSPEED
				case 3079: # MD5SUM_INFO
					break;
				case 3073: # FILE
					$packs ++;
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$fsize = filesize_cache( $text );
					$info[ $packs ][ 'pack' ] = $packs;
					$info[ $packs ][ 'size' ] = $fsize;
					if ( !isset( $info[ $packs ][ 'xx_gets' ] ) ) {
						$info[ $packs ][ 'xx_gets' ] = 0;
						$info[ $packs ][ 'trans' ] = 0;
					}
					$total[ 'packs' ] ++;
					$total[ 'size' ] += $fsize;
					$gruppen[ '*' ][ 'packs' ] ++;
					$gruppen[ '*' ][ 'size' ] += $info[ $packs ][ 'size' ];
					break;
				case 3074: # DESC
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$info[ $packs ][ 'xx_desc' ] = clean_names( $text );
					break;
				case 3075: # NOTE
				case 3076: # GETS
					$text = substr( $chunkdata, $j + 8, $jlen - 8 );
					$tgets = get_long( $text );
					$ttrans = $info[ $packs ][ 'size' ] * $tgets;
					$info[ $packs ][ 'xx_gets' ] += $tgets;
					$info[ $packs ][ 'trans' ] += $ttrans;
					$total[ 'xx_gets' ] += $tgets;
					$total[ 'trans' ] += $ttrans;
					$gruppen[ '*' ][ 'xx_gets' ] += $tgets;
					$gruppen[ '*' ][ 'trans' ] += $ttrans;
					break;
				case 17777: # GROUP NAME
					$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
					$info[ $packs ][ 'xx_trno' ] = $text;
					$gr = $text;
					if ( !isset( $gruppen[ $gr ][ 'packs' ] ) ) {
						$gruppen[ $gr ][ 'packs' ] = 0;
						$gruppen[ $gr ][ 'size' ] = 0;
						$gruppen[ $gr ][ 'xx_gets' ] = 0;
						$gruppen[ $gr ][ 'trans' ] = 0;
					}
					if ( !isset( $gruppen[ $gr ][ 'xx_trno' ] ) ) {
						$gruppen[ $gr ][ 'xx_trno' ] = clean_names( $text );
					}
					$gruppen[ $gr ][ 'packs' ] ++;
					$gruppen[ $gr ][ 'size' ] += $info[ $packs ][ 'size' ];
					$gruppen[ $gr ][ 'xx_gets' ] += $tgets;
					$gruppen[ $gr ][ 'trans' ] += $ttrans;
					$gruppen[ '*' ][ 'packs' ] ++;
					$gruppen[ '*' ][ 'size' ] += $info[ $packs ][ 'size' ];
					$gruppen[ '*' ][ 'xx_gets' ] += $tgets;
					$gruppen[ '*' ][ 'trans' ] += $ttrans;
					break;
				case 17778: # GROUP DESC
					$text = substr( $chunkdata, $j + 8, $jlen - 8 );
					$info[ $packs ][ 'xx_data' ] = $text;
					break;
				default:
					printf( ":xtag=%d<br>", $jtag );
					printf( ":xlen=%d<br>", $jlen );
				}
				$j += $jlen;
				$r = $jlen % 4;
				if ( $r > 0 )
					$j += 4 - $r;
			}

			break;
		default:
			printf( ":tag=%d<br>", $tag );
			printf( ":len=%d<br>", $len );
		}
		$i += $len;
		$r = $len % 4;
		if ( $r > 0 )
			$j += 4 - $r;
	}
}

write_sizecache( $cache_file );

if ( isset( $_GET[ 'group' ] ) ) {
	$hpack = '<a class="head" href="'.make_self_order( '' ).'">PACK</a>';
	$hgets = '<a class="head" href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hsize = '<a class="head" href="'.make_self_order( 'size' ).'">GRÖSSE</a>';

	if ( !isset( $_GET[ 'order' ] ) ) {
		foreach ( $info as $key => $data)
			$ausgabe[ $key ] = $key;
		asort( $ausgabe );
		$hpack = 'PACK';
	} else {
		if ( $_GET[ 'order' ] == 'gets' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'xx_gets' ];
			arsort( $ausgabe );
			$hgets = 'DLs';
		}
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GRÖSSE';
		}
	}

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
<th class="head">'.$hsize.'</th>
<th class="head">BESCHREIBUNG</th>
</tr>
</thead>
';

	$gr = $_GET[ 'group' ];
	$tpacks = $gruppen[ $gr ][ 'packs' ] / $servers;
	$tsize = $gruppen[ $gr ][ 'size' ] / $servers;

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
		if ( $key == "" )
			continue;
		if ( ( $_GET[ 'group' ] != '*' )
		&& ( $info[ $key ][ 'xx_data' ] != $_GET[ 'group' ] ) )
			continue;

		$tpack = $info[ $key ][ 'pack' ];
		$tname = $info[ $key ][ 'xx_desc' ];
		$jsid= $nick2.'_'.$tpack;

		$tname = '<span width="100%" onmouseover="highlight(this,\''.
			$highlight_color.'\')" style="CURSOR: pointer" onclick=\'selectThis("'.
			$jsid.'");\' onmouseout="highlight(this,\'\')" href="">'.
			$tname."</span>\n".
			'<span id="'.$jsid.'" style="VISIBILITY: hidden; POSITION: absolute">'.
			'/msg '.$nick.' xdcc send #'.$tpack."</span>\n";

		echo '
<tr>
<td class="right">#'.$tpack.'</td>
<td class="right">'.$info[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($info[ $key ][ 'size' ]).'</td>
<td class="content">'.$tname.'</td>
</tr>
';
	}

} else {
	$hpack = '<a class="head" href="'.make_self_order( 'pack' ).'">PACKs</a>';
	$hgets = '<a class="head" href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hrget = '<a class="head" href="'.make_self_order( 'rget' ).'">DLs/Pack</a>';
	$hsize = '<a class="head" href="'.make_self_order( 'size' ).'">GRÖSSE</a>';
	$htvol = '<a class="head" href="'.make_self_order( 'tvol' ).'">Volumen</a>';
	$hname = '<a class="head" href="'.make_self_order( '' ).'">GRUPPE</a>';

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
		$linkmore = '&nbsp;<a href="'.make_self_more().'">(weniger)</a>';
	} else {
		$linkmore = '&nbsp;<a href="'.make_self_more().'">(mehr)</a>';
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

	$tpacks = $total[ 'packs' ] / $servers;
	$tsize = $total[ 'size' ] / $servers;
	$part = $total[ 'downl' ] - $total[ 'trans' ];
	$tcount = count($gruppen) - 1;

	$tvol2 = '';
	$rget2 = '';
	if ( isset( $_GET[ 'volumen' ] ) ) {
		$tvol2 = '<th class="right">'.makesize($total[ 'trans' ]).'</th>';
		$getsperpack = sprintf( "%.1f", $total[ 'xx_gets' ] / $tpacks );
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
<th class="head"><a href="'.$_SERVER[ "PHP_SELF" ].'?group=*">alle Packs</a> ['.makesize($total[ 'trans' ]).'] vollständig heruntergeladen, ['.makesize($part).']&nbsp;unvollständig</th>
</tr>
</tfoot>
<tbody>
';

	foreach ( $ausgabe as $key => $data) {
		if ( $key == "" )
			continue;
		if ( $key == '*' )
			continue;

		$tpacks= $gruppen[ $key ][ 'packs' ] / $servers;;
		$asize = $gruppen[ $key ][ 'size' ] / $servers;
		$tsize = $gruppen[ $key ][ 'trans' ];
		$tname = '';
		if ( isset( $gruppen[ $key ][ 'xx_trno' ] ) )
			$tname = $gruppen[ $key ][ 'xx_trno' ];
		$link = $_SERVER[ "PHP_SELF" ].'?group='.$key;

		$tvol3 = '';
		$rget3 = '';
		if ( isset( $_GET[ 'volumen' ] ) ) {
			$tvol3 = '<td class="right">'.makesize($tsize).'</td>';
			$getsperpack = sprintf( "%.1f", $gruppen[ $key ][ 'xx_gets' ] / $tpacks );
			$rget3 = '<td class="right">'.$getsperpack.'</td>';
		}

		echo '
<tr>
<td class="right">'.$tpacks.'</td>
<td class="right">'.$gruppen[ $key ][ 'xx_gets' ].'</td>
'.$rget3.'
<td class="right">'.makesize($asize).'</td>
'.$tvol3.'
<td class="content">'.$key.'</td>
<td class="content"><a href="'.$link.'">'.$tname.'</a></td>
</tr>
';
	}

}

?>

</tbody>
</table>
</center>
</body>
</html>

