#!/usr/local/bin/ruby -w
#	$Id: lock-state.rb,v 1.2 2006/03/18 08:45:36 dm Exp $
#	(c) 2005, Dirk Meyer, Im Grund 4, 34317 Habichtswald
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

def usage(msg)
	STDERR.print msg, "\n\n"
	STDERR.print "Usage: #{File.basename($0)} statefile [statefile ...]\n\n"
	STDERR.print "export iroffer statefile to text.\n"
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
	lock_entry = 0
	nr = 0
	entry = '';
	group = '';
	job = '';
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
						entry = "#{entry}xx_trno #{seen_group[ group ]}\n"
					end
					if ( lock_entry != 0 )
						printf( "#{entry}\n" )
						job = "remove #{nr}\n#{job}"
					end
					nr = nr + 1
					final = 1
					lock_entry = 0
					text = chunkdata[jpos + 7, jlen - 8]
					file = get_text( text )
					entry = "xx_file #{file}\n"
				when 3074 # DESC
					text = chunkdata[jpos + 7, jlen - 8]
					desc = get_text( text )
					entry = "#{entry}xx_desc #{desc}\n"
				when 3075 # NOTE
					text = chunkdata[jpos + 7, jlen - 8]
					note = get_text( text )
					entry = "#{entry}xx_note #{note}\n"
				when 3076 # GETS
					gets =  get_long( chunkdata[jpos + 8, 4 ] )
					entry = "#{entry}xx_gets #{gets}\n"
					entry = "#{entry}xx_mins \n"
					entry = "#{entry}xx_maxs \n"
				when 3080 # GROUP NAME
					text = chunkdata[jpos + 7, jlen - 8]
					group = get_text( text )
					entry = "#{entry}xx_data #{group}\n"
					entry = "#{entry}xx_trig \n"
				when 3081 # GROUP DESC
					text = chunkdata[jpos + 7, jlen - 8]
					groupdesc = get_text( text )
					tmp = sprintf( "xx_trno %s\n", groupdesc )
					entry = "#{entry}#{tmp}"
					seen_group[ group ] = groupdesc
					final = 0
				when 3082 # LOCK
					lock_entry = 1
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
		entry = "#{entry}xx_trno #{seen_group[ group ]}\n"
	end
	if ( lock_entry != 0 )
		printf( "#{entry}\n" )
	end
	printf( "#{job}" )
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
	usage('State-file not given!')
end

exit 0
# 
