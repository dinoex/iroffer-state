#!/usr/local/bin/ruby -w
#	$Id$
#	(c) 2005, Dirk Meyer, Im Grund 4, 34317 Habichtswald
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

$chroot = ""

def usage(msg)
	print msg, "\nUsage: #{File.basename($0)} statefile [statefile ...]\n\n"
	print "export iroffer statefile to text.\n"
	exit 64
end

def filesize_cache(key)
	if  ( $size_cache.has_key?( key ) )
		return $size_cache[ key ]
	end
	bytes = File.size( "#{$chroot}#{key}")
	$size_cache[ key ] = bytes
	$size_cache_dirty += 1
	return bytes
end

def makesize(bytes)
	nbytes = bytes
	if ( nbytes < 1000 )
		return sprintf( "%3db", nbytes )
	end
	nbytes = ( nbytes  + 512 ) / 1024
	if ( nbytes < 1000 )
		return sprintf( "%3dk", nbytes )
	end
	nbytes = ( nbytes  + 512 ) / 1024
	if ( nbytes < 1000 )
		return sprintf( "%3dM", nbytes )
	end
	if ( nbytes < 10000 )
		return sprintf( "%3.1fG", ( nbytes.to_f / 1024) )
	end
	nbytes = ( nbytes  + 512 ) / 1024
	if ( nbytes < 1000 )
		return sprintf( "%3dG", nbytes )
	end
	if ( nbytes < 10000 )
		return sprintf( "%3.1fT", ( nbytes.to_f / 1024 ) )
	end
	nbytes = ( nbytes  + 512 ) / 1024
	if ( nbytes < 1000 )
		return sprintf( "%3dT", nbytes )
	end
	return sprintf( "%3dE", nbytes )
end

def ausgabe()
	if $xf.nil?
		return
	end
	$pack += 1
	$all_pack += 1
	printf( "%3s %3dx [%4s] %s\n", "##{$pack}", $xg, $size, $xd )
	$xf = nil
end

def ausgabetotal()
	$sum_size = makesize( $sum_bytes )
	$transfer_size = makesize( $transfer_bytes )
	$partial_bytes = $transfer_bytes - $sum_xg_bytes
	if ( $partial_bytes >= 0 )
		$partial_size = makesize( $partial_bytes )
	else
		$partial_size = '-' <<  makesize( $partial_bytes )
	end
	$total = sprintf( "total in files, [%4s] total downloaded, [%4s] partial",
		$transfer_size, $partial_size )
	printf( "%3s %3dx [%4s] %s\n", "##{$pack}", $sum_xg, $sum_size, $total )
end

def get_long(string)
	return string.unpack('N')[ 0 ]
end

def get_xlong(string)
	ll = string.unpack('NN')
	l = ll[ 0 ] * ( 2 ** 32 )
	l += ll[ 1 ]
	return l
end

def get_text(string)
	l = string.unpack('C')[ 0 ]
	l -= 1
	return string[1, l]
end

def parse_buffer(buffer, bsize)
	$xf = nil
	fsize = bsize - 16;
	ipos = 8
	while ipos < fsize
		tag = get_long( buffer[ipos, 4] )
		len = get_long( buffer[ipos + 4, 4] )
		if ( len <= 8 )
			printf( ":tag=%d<br>\n", tag )
			printf( ":len=%d<br>\n", len )
			printf( "Warning: parsing statfile aborted\n" )
			ipos = fsize
			break
		end
		case tag
		when 514 # TOTAL_SENT
			text = buffer[ipos + 8, len - 8]
			$total = get_xlong( text )
			if ( $all_transfer_bytes > 0 )
				print "\n"
			end
			$transfer_bytes += $total
			$all_transfer_bytes += $total
			$pack = 0
		when 3072 # XDCCS
			chunkdata = buffer[ipos, len]
			jpos = 8
			while jpos < len
				jtag = get_long( chunkdata[jpos, 4] )
				jlen = get_long( chunkdata[jpos + 4, 4] )
				if ( len <= 8 )
					printf( ":xtag=%d<br>\n", jtag )
					printf( ":xlen=%d<br>\n", jlen )
					printf( "Warning: parsing statfile aborted\n" )
					jpos = len
					break
				end
				case jtag
				when 0
				jpos = len
			when 3073 # FILE
					ausgabe()
					text = chunkdata[jpos + 7, jlen - 8]
					$xf = get_text( text )
					$bytes = filesize_cache( $xf )
					$sum_bytes += $bytes
					$all_bytes += $bytes
					$size = makesize( $bytes )
					$xd = text.gsub( /^.*\//, '' )
				when 3074 # DESC
					text = chunkdata[jpos + 7, jlen - 8]
					$xd = get_text( text )
				when 3076 # GETS
					text = chunkdata[jpos + 8, jlen - 8]
					$xg = get_long( text )
					$sum_xg += $xg
					$all_xg += $xg
					$xg_bytes = $xg * $bytes
					$sum_xg_bytes += $xg_bytes
					$all_xg_bytes += $xg_bytes
#				when 3080 # GROUP NAME
#					text = chunkdata[jpos + 7, jlen - 8]
#					group = get_text( text )
#				when 3081 # GROUP DESC
#					text = chunkdata[jpos + 7, jlen - 8]
#					groupdesc = get_text( text )
#					printf( "groupdesc %s %s\n", group, groupdesc )
				when 3082 # LOCK
					text = chunkdata[jpos + 7, jlen - 8]
					$lock = get_text( text )
					case $lock
					when 'badcrc', 'old'
						$xd << " (#{$lock})"
					else
						$xd << ' (gesperrt)'
					end
				end
				jpos += jlen
				r = jlen % 4
				if ( r > 0 )
					jpos += 4 - r
				end
			end
		end
		ipos += len;
		r = len % 4;
		if ( r > 0 )
			ipos += 4 - r;
		end
	end
	ausgabe()
	ausgabetotal()
	$pack = 0
	$sum_bytes = 0
	$sum_xg = 0
	$sum_xg_bytes = 0
	$transfer_bytes = 0
end

$size_filename = "size.data"
$size_cache_dirty = 0
$size_cache = Hash.new(0)

if ( FileTest.exist?($size_filename) )
	begin
		File.open($size_filename, 'r').each_line { |line|
			line.delete!( "\n" )
			line.delete!( "\r" )
			words = line.split( ':' )
			i = words[ 1 ].to_i
			if ( i > 0 )
				$size_cache[ words[ 0 ] ] = i
			end
		}
	rescue
		$stderr.print "Failure at #{$size_filename}: #{$!} => Skipping!\n"
	end
end

$all_pack = 0
$all_bytes = 0
$all_xg = 0
$all_xg_bytes = 0
$all_transfer_bytes = 0
$pack = 0
$sum_bytes = 0
$sum_xg = 0
$sum_xg_bytes = 0
$transfer_bytes = 0

if ARGV.size > 0 then
	ARGV.each { |filename|
		File.stat(filename).file? or next
		if ( /removed/.match( filename ) )
			File.open(filename, 'r').each_line { |line|
				line.delete!( "\n" )
				line.delete!( "\r" )
				if ( /^Do Not Edit This File[:] /.match( line ) )
					parts = line.split( ': ', 2 )
					words = parts[ 1 ].split( ' ', 4 )
					$total = words[ 2 ].to_i
					if ( $all_transfer_bytes > 0 )
						print "\n"
					end
					$transfer_bytes += $total
					$all_transfer_bytes += $total
					next
				end
				words = line.split( ' ', 2 )
				case words[ 0 ]
				when 'xx_file'
					$xf = words[ 1 ]
					$bytes = 0
				when 'xx_desc'
					$xd = words[ 1 ]
				when 'xx_size'
					$bytes = words[ 1 ].to_i
				when 'xx_gets'
					if $bytes == 0
						$bytes = filesize_cache( $xf )
					end
					$sum_bytes += $bytes
					$all_bytes += $bytes
					$size = makesize( $bytes )
					$xg = words[ 1 ].to_i
					$sum_xg += $xg
					$all_xg += $xg
					$xg_bytes = $xg * $bytes
					$sum_xg_bytes += $xg_bytes
					$all_xg_bytes += $xg_bytes
					ausgabe()
				end
			}
		else
			bsize = File.size(filename)
			begin
				buffer = File.open(filename, 'r').read
				parse_buffer( buffer, bsize )
			rescue
				$stderr.print "Failure at #{filename}: #{$!} => Skipping!\n"
			end
		end
	}
else
	usage('State-file not given!')
end

if ( $sum_bytes != $all_bytes )
	print "\n"
	$pack = $all_pack
	$sum_bytes = $all_bytes
	$sum_xg = $all_xg
	$sum_xg_bytes = $all_xg_bytes
	$transfer_bytes = $all_transfer_bytes
	ausgabetotal()
end

if ( $size_cache_dirty > 0 )
	f = File.new($size_filename, 'w')
	if ( f.nil? )
		$stderr.print "Failure to save cache #{$size_filename}\n"
		exit 1
	end
	$size_cache.each {|key, value|
		f.write( "#{key}:#{value}\n" )
	}
	f.close
end

exit 0
# 
