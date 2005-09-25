<?php
#
# Copyright 2004 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
#
# Updates on:
#	http://iroffer.dinoex.net/
#

$meta_generator = '
<meta name="generator" content="iroffer-state 2.0, iroffer.dinoex.net">
';

# IRC-Farbe-Codes ausblenden
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

$javascript_code = '';
if ( $javascript > 0 ) {
	$javascript_code = '
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
				if ( $tsize > 0 )
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
	$localfile = $filename;
	if ( !ereg( '^/', $filename ) )
		$localfile = $base_path.$filename;
	if ( $chroot_path != '' )
		$localfile = $chroot_path.$localfile;
	$tsize = filesize( $localfile );
	$sizecache[ $filename ] = $tsize;
	$sizecache_dirty ++;
	return $tsize;
}

#
# bytes in lesbarere Form ausgeben.
#
function makesize( $nbytes ) {
	global $debug;

	if ( $nbytes < 0 ) {
		return '0b';
	}
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

function cgi_escape( $string ) {
	$string = ereg_replace( '[&]', '%26', $string );
	$string = ereg_replace( '[+]', '%2B', $string );
	return $string;
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

function seconds_to_text( $sec ) {
	$text = '';
	$rest = $sec % 60;
	$text .= $rest.' Sek.';
	$mehr = floor( $sec / 60 );

	$rest = $mehr % 60;
	$text = $rest.' Min. '.$text;
	$mehr = floor( $mehr / 60 );

	$rest = $mehr % 24;
	$text = $rest.' Std. '.$text;
	$mehr = floor( $mehr / 24 );

	$text = $mehr.' Tage '.$text;
	return $text;
}

class iroffer_botlist {
# input
	var $nick;
	var $filenames;
	var $base_path;
	var $chroot_path;
	var $statistik;
	var $add_url;
# output
	var $total;
	var $gruppen;
	var $info;
	var $support_groups;
# internal
	var $seen;

function make_self_more() {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	if ( $this->add_url != '' ) {
		$link .= '?';
		$link .= $this->add_url;
		$par ++;
	}
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
	if ( $this->add_url != '' ) {
		$link .= '?';
		$link .= $this->add_url;
		$par ++;
	}
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
	if ( $this->add_url != '' ) {
		$link .= '?';
		$link .= $this->add_url;
		$par ++;
	}
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
	if ( $this->add_url != '' ) {
		$link .= '?';
		$link .= $this->add_url;
		$par ++;
	}
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
			$this->total[ 'downl' ] += $itotal;
			continue;
		}
		if ( !ereg( ' ', $data ) )
			continue;

		list( $key, $text ) = explode(' ', $data, 2);
		if ( $text == '' )
			continue;

		if ( $key == 'xx_file' ) {
			$fsize = filesize_cache( $text );
			if ( isset( $this->seen[ $text ] ) )
				continue;
			$this->seen[ $text ] = 0;
			$this->total[ 'packs' ] ++;
			$this->total[ 'size' ] += $fsize;
		}
		if ( $key == 'xx_gets' ) {
			$this->total[ 'xx_gets' ] += $text;
			$this->total[ 'trans' ] += $fsize * $text;
		}
	}
}

function read_status( $statefile ) {
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

			if ( !isset( $this->total[ 'freeslots' ] ) )
				$this->total[ 'freeslots' ] = $words[ 9 ];
			if ( !isset( $this->total[ 'maxslots' ] ) )
				$this->total[ 'maxslots' ] = $words[ 11 ];

			for ( $i = 14; isset( $words[ $i ]); $i += 2 ) {
				$words[ $i + 1 ] = str_replace( ',', '', $words[ $i + 1 ] );
				switch ( $words[ $i ] ) {
				case 'Queue:':
					if ( !isset( $this->total[ 'queue' ] ) )
						$this->total[ 'queue' ] = $words[ $i + 1 ];
					break;
				case 'Min:':
					if ( !isset( $this->total[ 'minspeed' ] ) )
						$this->total[ 'minspeed' ] = $words[ $i + 1 ];
					break;
				case 'Max:':
					if ( !isset( $this->total[ 'maxspeed' ] ) )
						$this->total[ 'maxspeed' ] = $words[ $i + 1 ];
					break;
				case 'Record:':
					if ( !isset( $this->total[ 'record' ] ) )
						$this->total[ 'record' ] = $words[ $i + 1 ];
					break;
				}
			}
			continue;
		}
		if ( ereg( '^ *[*]*  Bandwidth Usage ', $data ) ) {
			$words = explode(' ', $data);
			if ( !isset( $this->total[ 'current' ] ) )
				$this->total[ 'current' ] = str_replace( ',', '', $words[ 9 ] );

			for ( $i = 10; isset( $words[ $i ]); $i += 2 ) {
				$words[ $i + 1 ] = str_replace( ',', '', $words[ $i + 1 ] );
				switch ( $words[ $i ] ) {
				case 'Cap:':
					if ( !isset( $this->total[ 'cap' ] ) )
						$this->total[ 'cap' ] = $words[ $i + 1 ];
					break;
				case 'Record:':
					if ( !isset( $this->total[ 'send' ] ) )
						$this->total[ 'send' ] = $words[ $i + 1 ];
					break;
				}
			}
			break;
		}
		continue;
	}
}

function update_group( $gr, $fpacks, $newfile, $tgets, $fsize, $fname ) {
	if ( $fsize <= 0 )
		$fsize = filesize_cache( $fname );

	$ttrans = $fsize * $tgets;
	$this->total[ 'xx_gets' ] += $tgets;
	$this->gruppen[ '*' ][ 'xx_gets' ] += $tgets;
	$this->info[ $fpacks ][ 'xx_gets' ] += $tgets;
	$this->total[ 'trans' ] += $ttrans;
	$this->gruppen[ '*' ][ 'trans' ] += $ttrans;
	$this->info[ $fpacks ][ 'trans' ] += $ttrans;
	$this->info[ $fpacks ][ 'xx_data' ] = $gr;
	if ( !isset( $this->gruppen[ $gr ][ 'packs' ] ) ) {
		$this->gruppen[ $gr ][ 'packs' ] = 0;
		$this->gruppen[ $gr ][ 'size' ] = 0;
		$this->gruppen[ $gr ][ 'xx_gets' ] = 0;
		$this->gruppen[ $gr ][ 'trans' ] = 0;
	}
	$this->gruppen[ $gr ][ 'xx_gets' ] += $tgets;
	$this->gruppen[ $gr ][ 'trans' ] += $ttrans;
	if ( $newfile != 0 ) {
		$this->gruppen[ $gr ][ 'packs' ] ++;
		$this->gruppen[ $gr ][ 'size' ] += $fsize;
		$this->total[ 'size' ] += $fsize;
		$this->gruppen[ '*' ][ 'size' ] += $fsize;
		$this->info[ $fpacks ][ 'size' ] = $fsize;
	}
}

function read_state( )
{
	global $cache_file;

	$this->support_groups = 0;
	$this->total[ 'packs' ] = 0;
	$this->total[ 'size' ] = 0;
	$this->total[ 'downl' ] = 0;
	$this->total[ 'xx_gets' ] = 0;
	$this->total[ 'trans' ] = 0;
	$this->total[ 'uptime' ] = 0;
	$this->total[ 'daily' ] = 0;
	$this->total[ 'weekly' ] = 0;
	$this->total[ 'monthly' ] = 0;
	$this->gruppen[ '*' ][ 'packs' ] = 0;
	$this->gruppen[ '*' ][ 'size' ] = 0;
	$this->gruppen[ '*' ][ 'xx_gets' ] = 0;
	$this->gruppen[ '*' ][ 'trans' ] = 0;

	$packs = 0;
	$fpacks = 0;
	$newfile = 0;
	$nogroup = 0;
	$fname = '';

	read_sizecache( $cache_file );

	# Status aller Bots lesen
	foreach ( $this->filenames as $key => $filename) {
		$this->read_removed( $filename );
		$this->read_status( $filename );

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
				if ( isset( $this->total[ 'version' ] ) )
					break;
				$text = substr( $filedata, $i + 8, $len - 8 );
				$tver = clean_names( $text );
				$tver = ereg_replace( ',.*$', '', $tver );
				$tver = ereg_replace( '\[.*\]', '', $tver );
				$this->total[ 'version' ] = $tver;
				break;
			case 257: # TIMESTAMP
				$text = get_long( substr( $filedata, $i + 8, 4 ) );
				if ( isset( $this->total[ 'time' ] ) ) {
					if ( $this->total[ 'time' ] > $text )
						break;
				}
				$this->total[ 'time' ] = $text;
				break;
			case 514: # TOTAL_SENT
				$text = substr( $filedata, $i + 8, $len - 8 );
				$itotal = get_xlong( $text );
				$this->total[ 'downl' ] += $itotal;
				$packs = 0;
				break;
			case 515: # TOTAL_UPTIME
				$text = substr( $filedata, $i + 8, $len - 8 );
				$itotal = get_long( $text );
				$this->total[ 'uptime' ] += $itotal;
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
							update_group( $default_group, $fpacks, $newfile, $tgets, $fsize, $fname );
						$nogroup = 1;
						$newfile = 0;
						$fsize = 0;
						$packs ++;
						$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
						$fname = $text;
						if ( !isset( $this->seen[ $fname ] ) ) {
							$newfile = 1;
							$this->seen[ $fname ] = $packs;
							$this->total[ 'packs' ] ++;
							$this->gruppen[ '*' ][ 'packs' ] ++;
						}
						$fpacks = $this->seen[ $fname ];
						$this->info[ $fpacks ][ 'pack' ] = $fpacks;
						if ( !isset( $this->info[ $fpacks ][ 'xx_gets' ] ) ) {
							$this->info[ $fpacks ][ 'xx_gets' ] = 0;
							$this->info[ $fpacks ][ 'trans' ] = 0;
						}
						break;
					case 3074: # DESC
						if ( isset( $this->info[ $fpacks ][ 'xx_desc' ] ) )
							break;
						$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
						$this->info[ $fpacks ][ 'xx_desc' ] = clean_names( $text );
						break;
					case 3075: # NOTE
						if ( isset( $this->info[ $fpacks ][ 'xx_note' ] ) )
							break;
						$text = substr( $chunkdata, $j + 8, $jlen - 8 );
						$tnote = clean_names( $text );
						if ( $tnote == "" )
							break;
						$this->info[ $fpacks ][ 'xx_note' ] = $tnote;
						break;
					case 3076: # GETS
						$text = substr( $chunkdata, $j + 8, $jlen - 8 );
						$tgets = get_long( $text );
						break;
					case 3079: # MD5SUM_INFO
						$tmp6 = get_xlong( substr( $chunkdata, $j + 8, 8 ) );
						if ( $tmp6 > 0 )
						$fsize = $tmp6;
						$tmd5a = get_long( substr( $chunkdata, $j + 36, 4 ) );
						$tmd5b = get_long( substr( $chunkdata, $j + 40, 4 ) );
						$tmd5c = get_long( substr( $chunkdata, $j + 44, 4 ) );
						$tmd5d = get_long( substr( $chunkdata, $j + 48, 4 ) );
						$tmd5 = sprintf( '%08lx%08lx%08lx%08lx',
							$tmd5a, $tmd5b, $tmd5c, $tmd5d );
						$this->info[ $fpacks ][ 'xx_md5' ] = $tmd5;
						break;
					case 3080: # GROUP NAME
						$nogroup = 0;
						$this->support_groups = 1;
						$text = get_text( substr( $chunkdata, $j + 8, $jlen - 8 ) );
						$gr = $text;
						$this->update_group( $gr, $fpacks, $newfile, $tgets, $fsize, $fname );
						break;
					case 3081: # GROUP DESC
						if ( isset( $this->gruppen[ $gr ][ 'xx_trno' ] ) )
							break;
						$text = substr( $chunkdata, $j + 8, $jlen - 8 );
						$this->gruppen[ $gr ][ 'xx_trno' ] = clean_names( $text );
						break;
					case 3082: # LOCK
						$this->info[ $fpacks ][ 'xx_lock' ] = 1;
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
				$this->total[ 'daily' ] += $traffic;
				break;
			case 3330: # TLIMIT_WEEKLY_USED
				$text = substr( $filedata, $i + 8, $len - 8 );
				$traffic = get_xlong( $text );
				$this->total[ 'weekly' ] += $traffic;
				break;
			case 3332: # TLIMIT_MONTHLY_USED
				$text = substr( $filedata, $i + 8, $len - 8 );
				$traffic = get_xlong( $text );
				$this->total[ 'monthly' ] += $traffic;
				break;
			}
			$i += $len;
			$r = $len % 4;
			if ( $r > 0 )
				$i += 4 - $r;
		}
	}
	if ( ( $nogroup != 0 ) && ( $this->support_groups != 0 ) )
		update_group( $default_group, $fpacks, $newfile, $tgets, $fsize, $fname );

	write_sizecache( $cache_file );

}

function write_table( )
{
	global $javascript;

	$nick2 = ereg_replace( '[^A-Za-z_0-9]', '', $this->nick );

	if ( $this->support_groups == 0 )
		$_GET[ 'group' ] = '*';

	# Ueberschrift:
	if ( isset( $_GET[ 'group' ] ) ) {
		echo '<h1>'.$this->nick." Datei-Liste</h1>\n";
		echo "\n";
		echo '<p>Download im IRC mit <span class="cmd">/msg '.$this->nick.' xdcc send #nummer</span></p>';
		echo "\n";
	} else {
		echo '<h1>'.$this->nick." Gruppen-Liste</h1>\n";
		echo "\n";
	}

	echo '
<table cellpadding="2" cellspacing="0" summary="list">
<thead>
';
	if ( isset( $_GET[ 'group' ] ) ) {
		$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.$this->make_self_order( '' ).'">PACK</a>';
		$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.$this->make_self_order( 'gets' ).'">DLs</a>';
		$hsize = '<a class="head" title="sortieren nach Göße der Files"
href="'.$this->make_self_order( 'size' ).'">GRÖSSE</a>';

		if ( !isset( $_GET[ 'order' ] ) ) {
			foreach ( $this->info as $key => $data)
				$ausgabe[ $key ] = $key;
			asort( $ausgabe );
			$hpack = 'PACK';
		} else {
			$ofound = 0;
			if ( $_GET[ 'order' ] == 'gets' ) {
				foreach ( $this->info as $key => $data)
					$ausgabe[ $key ] = $this->info[ $key ][ 'xx_gets' ];
				arsort( $ausgabe );
				$hgets = 'DLs';
				$ofound = 1;
			}
			if ( $_GET[ 'order' ] == 'size' ) {
				foreach ( $this->info as $key => $data)
					$ausgabe[ $key ] = $this->info[ $key ][ 'size' ];
				arsort( $ausgabe );
				$hsize = 'GRÖSSE';
				$ofound = 1;
			}
			if ( $ofound == 0 ) {
				foreach ( $this->info as $key => $data)
					$ausgabe[ $key ] = $key;
				asort( $ausgabe );
				$hpack = 'PACK';
			}
		}
		$linkmore = '&nbsp;<a title="zurück" href="'.$this->make_self_back( '' ).'">(zurück)</a>';

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
		$tpacks = $this->gruppen[ $gr ][ 'packs' ];
		$tsize = $this->gruppen[ $gr ][ 'size' ];

		echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$this->gruppen[ $gr ][ 'xx_gets' ].'</th>
<th class="right">'.makesize($tsize).'</th>
<th class="head">['.makesize($this->gruppen[ $gr ][ 'trans' ]).'] vollständig heruntergeladen</th>
</tr>
</tfoot>
<tbody>
';

		foreach ( $ausgabe as $key => $data) {
			if ( $key == '' )
				continue;
			if ( ( $_GET[ 'group' ] != '*' )
			&& ( $this->info[ $key ][ 'xx_data' ] != $_GET[ 'group' ] ) )
				continue;

			$tpack = $this->info[ $key ][ 'pack' ];
			$tname = $this->info[ $key ][ 'xx_desc' ];
			$jsid= $nick2.'_'.$tpack;

			if ( isset( $this->info[ $key ][ 'xx_lock' ] ) )
				$tname .= ' (gesperrt)';
			$tname = htmlspecialchars( $tname);
			if ( $javascript > 0 ) {
				$tname = '<span class="selectable" onclick=javascript:selectThis(\''.
					$jsid.'\');>'.
					$tname."</span>\n".
					'<span id="'.$jsid.'" class="hidden">'.
					'/msg '.$this->nick.' xdcc send #'.$tpack."</span>\n";
			}
			if ( isset( $this->info[ $key ][ 'xx_note' ] ) )
				$tname .= '<br>'.$this->info[ $key ][ 'xx_note' ];

			$label = "Download mit:\n/msg ".$this->nick.' xdcc send #'.$tpack."\n";
			if ( isset( $this->info[ $key ][ 'xx_md5' ] ) )
				$label .= "\nmd5: ".$this->info[ $key ][ 'xx_md5' ];

			echo '
<tr>
<td class="right">#'.$tpack.'</td>
<td class="right">'.$this->info[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($this->info[ $key ][ 'size' ]).'</td>
<td class="content" title="'.$label.'">'.$tname.'</td>
</tr>
';
		}

	} else {
		$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.$this->make_self_order( 'pack' ).'">PACKs</a>';
		$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.$this->make_self_order( 'gets' ).'">DLs</a>';
		$hrget = '<a class="head" title="sortieren nach Downloads per Datei"
href="'.$this->make_self_order( 'rget' ).'">DLs/Pack</a>';
		$hsize = '<a class="head" title="sortieren nach Göße der Files"
href="'.$this->make_self_order( 'size' ).'">GRÖSSE</a>';
		$htvol = '<a class="head" title="sortieren nach Übertragusngsvolumen"
href="'.$this->make_self_order( 'tvol' ).'">Volumen</a>';
		$hname = '<a class="head" title="sortieren nach Gruppe"
href="'.$this->make_self_order( '' ).'">GRUPPE</a>';

		if ( !isset( $_GET[ 'order' ] ) ) {
			foreach ( $this->gruppen as $key => $data)
				$ausgabe[ $key ] = $key;
			asort( $ausgabe );
			$hname = 'GRUPPE';
		} else {
			if ( $_GET[ 'order' ] == 'pack' ) {
				foreach ( $this->gruppen as $key => $data)
					$ausgabe[ $key ] = $this->gruppen[ $key ][ 'packs' ];
				arsort( $ausgabe );
				$hpack = 'PACKs';
			}
			if ( $_GET[ 'order' ] == 'gets' ) {
				foreach ( $this->gruppen as $key => $data)
					$ausgabe[ $key ] = $this->gruppen[ $key ][ 'xx_gets' ];
				arsort( $ausgabe );
				$hgets = 'DLs';
			}
			if ( $_GET[ 'order' ] == 'rget' ) {
				foreach ( $this->gruppen as $key => $data)
					$ausgabe[ $key ] = $this->gruppen[ $key ][ 'xx_gets' ] / $this->gruppen[ $key ][ 'packs' ];
				arsort( $ausgabe );
				$hrget = 'DLs/Pack';
			}
			if ( $_GET[ 'order' ] == 'size' ) {
				foreach ( $this->gruppen as $key => $data)
					$ausgabe[ $key ] = $this->gruppen[ $key ][ 'size' ];
				arsort( $ausgabe );
				$hsize = 'GRÖSSE';
			}
			if ( $_GET[ 'order' ] == 'tvol' ) {
				foreach ( $this->gruppen as $key => $data)
					$ausgabe[ $key ] = $this->gruppen[ $key ][ 'trans' ];
				arsort( $ausgabe );
				$htvol = 'Volumen';
			}
		}

		$tvol1 = '';
		$rget1 = '';
		if ( isset( $_GET[ 'volumen' ] ) ) {
			$tvol1 = '<th class="head">'.$htvol.'</th>';
			$rget1 = '<th class="head">'.$hrget.'</th>';
			$linkmore = '&nbsp;<a title="Volumen ausblenden" href="'.$this->make_self_more().'">(weniger)</a>';
		} else {
			$linkmore = '&nbsp;<a title="Volumen anzeigen" href="'.$this->make_self_more().'">(mehr)</a>';
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

		$tpacks = $this->total[ 'packs' ];
		$tsize = $this->total[ 'size' ];
		$part = $this->total[ 'downl' ] - $this->total[ 'trans' ];
		$tcount = count($this->gruppen) - 1;

		$tvol2 = '';
		$rget2 = '';
		if ( isset( $_GET[ 'volumen' ] ) ) {
			$tvol2 = '<th class="right">'.makesize($this->total[ 'trans' ]).'</th>';
			$getsperpack = sprintf( '%.1f', $this->total[ 'xx_gets' ] / $tpacks );
			$rget2 = '<th class="right">'.$getsperpack.'</th>';
		}

		echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$this->total[ 'xx_gets' ].'</th>
'.$rget2.'
<th class="right">'.makesize($tsize).'</th>
'.$tvol2.'
<th class="head">'.$tcount.'</th>
<th class="head"><a title="alle Packs in einer Liste anzeigen" href="'.$this->make_self_group( '*' ).'">alle Packs</a> ['.makesize($this->total[ 'trans' ]).'] vollständig heruntergeladen, ['.makesize($part).']&nbsp;unvollständig</th>
</tr>
</tfoot>
<tbody>
';

		foreach ( $ausgabe as $key => $data) {
			if ( $key == '' )
				continue;
			if ( $key == '*' )
				continue;

			$tpacks= $this->gruppen[ $key ][ 'packs' ];
			$asize = $this->gruppen[ $key ][ 'size' ];
			$tsize = $this->gruppen[ $key ][ 'trans' ];
			$tname = $key;
			if ( isset( $this->gruppen[ $key ][ 'xx_trno' ] ) )
				$tname = $this->gruppen[ $key ][ 'xx_trno' ];
			$link = $this->make_self_group( $key );

			$tvol3 = '';
			$rget3 = '';
			if ( isset( $_GET[ 'volumen' ] ) ) {
				$tvol3 = '<td class="right">'.makesize($tsize).'</td>';
				$getsperpack = sprintf( '%.1f', $this->gruppen[ $key ][ 'xx_gets' ] / $tpacks );
				$rget3 = '<td class="right">'.$getsperpack.'</td>';
			}
			echo '
<tr>
<td class="right">'.$tpacks.'</td>
<td class="right">'.$this->gruppen[ $key ][ 'xx_gets' ].'</td>
'.$rget3.'
<td class="right">'.makesize($asize).'</td>
'.$tvol3.'
<td class="content">'.htmlspecialchars($key).'</td>
<td class="content"><a title="Liste dieser Packs anzeigen" href="'.$link.'">'.htmlspecialchars($tname).'</a></td>
</tr>
';
		}

	}

	echo '
</tbody>
</table>
<table class="status">
<tbody>
<tr><td>Version</td>
';

	$traffic = array (
		'daily' => "Traffic heute",
		'weekly' => "Traffic diese Woche",
		'monthly' => "Traffic diesem Monat",
	);

	$label = '';
	foreach ( $traffic as $skey => $sdata) {
		if ( !isset( $this->total[ $skey ] ) )
			continue;
		$this->total[ $skey.'text' ] = makesize( $this->total[ $skey ] );
		$label .= sprintf( "%6s %s\n", $this->total[ $skey.'text' ], $sdata );
	}
	echo '<td title="'.$label.'">'.$this->total[ 'version' ]."</td></tr>\n";

	$this->total[ 'uptimetext' ] = seconds_to_text( $this->total[ 'uptime' ] );
	$this->total[ 'timetext' ] = strftime('%d.%m.%Y %H:%M', $this->total[ 'time' ] );

	foreach ( $this->statistik as $skey => $sdata) {
		if ( !isset( $this->total[ $skey ] ) )
			continue;
		echo '<tr><td>'.$sdata."</td>\n";
		echo '<td>'.$this->total[ $skey ]."</td></tr>\n";
	}

	echo '
</tbody>
</table>
';

}

}

?>
