<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
#
# Copyright 2004 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
# 

# IRC-Nick des Bots
$nick = ereg_replace( "/[^/]*[.]php$", '', $_SERVER[ "PHP_SELF" ] );
$nick = ereg_replace( "^/(.*/)*", '', $nick );
#$nick = "XDCC|".$nick;
#$nick = 'XDCC|irofferbot';

# Laufern mehr als ein Bot mit den gleichen daten?
$servers = 1;

# Statusfiles der bots
$filenames = array(
	'mybot.xdcc',
);

$cache_file = "size.data";

$strip_in_names = array (
	"^ *- *",
	"\002",
	"\0030[,]0",
	"\0030[,]5",
	"\0030",
	"\00314",
	"\00315",
	"\0033",
	"\0035\037",
	"\0037",
	"\003",
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

read_sizecache( $cache_file );

# Status aller Bots lesen
$read = '';
foreach ( $filenames as $key => $filename) {
	$fp = fopen( $filename, 'r' );
	$read .= fread($fp, filesize ($filename));
	fclose($fp);
}

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
 
$datalines = explode("\n", $read);
foreach ( $datalines as $key => $data) {
	if ( $data == "" )
		continue;

	if ( ereg( "^Do Not Edit This File[:] ", $data ) ) {
		list( $key, $text ) = explode(": ", $data, 2);
		list( $irec, $iband, $itotal, $irest ) = explode(" ", $text, 4);
		$total[ 'downl' ] += $itotal;
		$packs = 0;
		continue;
	}
	if ( !ereg( " ", $data ) )
		continue;

	list( $key, $text ) = explode(" ", $data, 2);
	if ( $text == "" )
		continue;

	# removed packages
	if ( $irest == '-' ) {
		if ( $key == 'xx_file' ) {
			$fsize = filesize_cache( $text );
			$total[ 'packs' ] ++;
			$total[ 'size' ] += filesize( $text );
		}
		if ( $key == 'xx_gets' ) {
			$total[ 'xx_gets' ] += $text;
			$total[ 'trans' ] += $fsize * $text;
		}
		continue;
	}

	if ( $key == 'xx_file' ) {
		$packs ++;
		$fsize = filesize_cache( $text );
		$info[ $packs ][ 'pack' ] = $packs;
		$info[ $packs ][ 'size' ] = $fsize;
		if ( !isset( $info[ $packs ][ 'xx_gets' ] ) ) {
			$info[ $packs ][ 'xx_gets' ] = 0;
			$info[ $packs ][ 'trans' ] = 0;
		}
		$total[ 'packs' ] ++;
		$total[ 'size' ] += $fsize;
	}

	if ( $key == 'xx_gets' ) {
		$tgets = $text;
		$ttrans = $info[ $packs ][ 'size' ] * $tgets;
		$info[ $packs ][ $key ] += $tgets;
		$info[ $packs ][ 'trans' ] += $ttrans;
		$total[ 'xx_gets' ] += $tgets;
		$total[ 'trans' ] += $ttrans;
		continue;
	}
	if ( $key == 'xx_desc' ) {
		$info[ $packs ][ 'xx_desc' ] = clean_names( $text );
		continue;
	}

	$info[ $packs ][ $key ] = $text;

	if ( $key == 'xx_trno' ) {
		if ( !isset( $gruppen[ $gr ][ 'xx_trno' ] ) ) {
			$gruppen[ $gr ][ 'xx_trno' ] = clean_names( $text );
		}
		continue;
	}
	
	if ( $key != 'xx_data' )
		continue;
	
	$gr = $text;
	if ( !isset( $gruppen[ $gr ][ 'packs' ] ) ) {
		$gruppen[ $gr ][ 'packs' ] = 0;
		$gruppen[ $gr ][ 'size' ] = 0;
		$gruppen[ $gr ][ 'xx_gets' ] = 0;
		$gruppen[ $gr ][ 'trans' ] = 0;
	}
	$gruppen[ $gr ][ 'packs' ] ++;
	$gruppen[ $gr ][ 'size' ] += $info[ $packs ][ 'size' ];
	$gruppen[ $gr ][ 'xx_gets' ] += $tgets;
	$gruppen[ $gr ][ 'trans' ] += $ttrans;
	$gruppen[ '*' ][ 'packs' ] ++;
	$gruppen[ '*' ][ 'size' ] += $info[ $packs ][ 'size' ];
	$gruppen[ '*' ][ 'xx_gets' ] += $tgets;
	$gruppen[ '*' ][ 'trans' ] += $ttrans;
}

write_sizecache( $cache_file );

if ( isset( $_GET[ 'group' ] ) ) {
	$link = '?group='.$_GET[ 'group' ];
	$hpack = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].$link.'">PACK</a>';
	$hgets = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].$link.'&order=gets">DLs</a>';
	$hsize = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].$link.'&order=size">GRÖSSE</a>';

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

		echo '
<tr>
<td class="right">#'.$info[ $key ][ 'pack' ].'</td>
<td class="right">'.$info[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($info[ $key ][ 'size' ]).'</td>
<td class="content">'.$info[ $key ][ 'xx_desc' ].'</td>
</tr>
';
	}

} else {
	$hpack = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].'?order=pack">PACKs</a>';
	$hgets = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].'?order=gets">DLs</a>';
	$hsize = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].'?order=size">GRÖSSE</a>';
	$hname = '<a class="head" href="'.$_SERVER[ "PHP_SELF" ].'">GRUPPE</a>';

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
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GRÖSSE';
		}
	}

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
<th class="head">'.$hsize.'</th>
<th class="head">'.$hname.'</th>
<th class="head">BESCHREIBUNG</th>
</tr>
</thead>
';

	$tpacks = $total[ 'packs' ] / $servers;
	$tsize = $total[ 'size' ] / $servers;
	$part = $total[ 'downl' ] - $total[ 'trans' ];
	$tcount = count($gruppen) - 1;

	echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$total[ 'xx_gets' ].'</th>
<th class="right">'.makesize($tsize).'</th>
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
		$tname = '';
		if ( isset( $gruppen[ $key ][ 'xx_trno' ] ) )
			$tname = $gruppen[ $key ][ 'xx_trno' ];
		$link = $_SERVER[ "PHP_SELF" ].'?group='.$key;

		echo '
<tr>
<td class="right">'.$tpacks.'</td>
<td class="right">'.$gruppen[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($asize).'</td>
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

