#!/usr/local/bin/ruby -w
#	$Id$
#	(c) 2005, Dirk Meyer, Im Grund 4, 34317 Habichtswald
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

require 'getoptlong'

def usage(msg, options)
	print msg, "\nUsage: #{File.basename($0)} statefile [statefile ...]\n\n"
	print msg, "export iroffer statefile to text.\n"
	options.each { |o|
		print "  " + o[1] + ", " + o[0] + " " +
			(o[2] == GetoptLong::REQUIRED_ARGUMENT ? 'ARGUMENT' : '') + "\n"
	}
	exit 64
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
	seen_group = Hash.new(0)
	group = '';
	fsize = bsize - 16
	ipos = 8
	final = 0
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
					if ( final != 0 )
						printf( "xx_trno %s\n", seen_group[ group ] )
						printf( "\n" )
					end
					final = 1
					text = chunkdata[jpos + 7, jlen - 8]
					file = get_text( text )
					printf( "xx_file %s\n", file )
				when 3074 # DESC
					text = chunkdata[jpos + 7, jlen - 8]
					desc = get_text( text )
					printf( "xx_desc %s\n", desc )
				when 3075 # NOTE
					text = chunkdata[jpos + 7, jlen - 8]
					note = get_text( text )
					printf( "xx_note %s\n", note )
				when 3076 # GETS
					gets =  get_long( chunkdata[jpos + 8, 4 ] )
					printf( "xx_gets %d\n",  gets )
					printf( "xx_mins \n" )
					printf( "xx_maxs \n" )
				when 3080 # GROUP NAME
					text = chunkdata[jpos + 7, jlen - 8]
					group = get_text( text )
					printf( "xx_data %s\n", group )
					printf( "xx_trig \n" )
				when 3081 # GROUP DESC
					text = chunkdata[jpos + 7, jlen - 8]
					groupdesc = get_text( text )
					printf( "xx_trno %s\n", groupdesc )
					printf( "\n" )
					seen_group[ group ] = groupdesc
					final = 0
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
	if ( final != 0 )
		printf( "xx_trno %s\n", seen_group[ group ] )
		printf( "\n" )
	end
end

if ARGV.size > 0 then
	ARGV.each { |filename|
		File.stat(filename).file? or next
		bsize = File.size(filename)
		begin
			buffer = File.open(filename, 'r').read
			parse_buffer( buffer, bsize )
		rescue
			$stderr.print "Failure at #{filename}: #{$!} => Skipping!\n"
		end
	}
else
	usage('State-file not given!', options)
end

exit 0
# 
