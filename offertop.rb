#!/usr/local/bin/ruby -w
#
#	$Id$
#	(c) 2005, Dirk Meyer, Im Grund 4, 34317 Habichtswald
#	based on wirk from:;
#	(C) 2002 by dpunkt.de, Armin Roehrl, Stefan Schmiedl, Clemens Wyss 2002-01-20
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

class LogHash < Hash
	attr_reader :re
	attr_reader :title
	attr_reader :art

	# Hash mit Standardwert 0
	def initialize( re, title, art )
		super(0)
		@re = re
		@title = title
		@art = art
	end

	# absteigend sortieren nach Werten
	# und abschneiden
	def populaerste(n) 
		pop = sort { |a, b| b[1] <=> a[1] }
		pop[1...n]
	end

	def sum
		total = 0
		each { |b, a|
			total += a
		}
		total
	end

	def printtop(n)
		print sum, " #{@art}\n"
		print size, " verschiedene #{@title}s\n"
		print "Die beliebtesten #{@title}s: Zahl der #{@art}, #{@title}\n"
		pop = populaerste(n)
		pop.each { |b, a|
			printf "%7d\t%s\n", a, b
		}
		print "\n"
	end
end

# ** 2006-02-10-06:17:25: XDCC SEND #29 requested: ihs (euirc-13824fe7.bpool.celox.de)
# ** 2006-02-10-17:48:48: XDCC SEND 15 Queued (slot): Spaghetti (euirc-6ab9fef9.adsl.alicedsl.de)
request_pack = / XDCC SEND #*([0-9]*) (?:requested|Queued .slot.): [^ ]* /
request_nick = / XDCC SEND #*[0-9]* (?:requested|Queued .slot.): ([^ ]*) /
# ** 2006-02-10-06:17:26: XDCC [515:ihs]: Connection established (84.245.180.163:1027 -> 213.239.196.229:53686)
connected_nick = / XDCC [\[][0-9]*[:]([^\]]*)[\]]: Connection established /
# ** 2006-02-10-06:39:30: XDCC [515:ihs]: Transfer Completed (383302 KB, 22 min 3.865 sec, 289.5 KB/sec)
completed_nick = / XDCC [\[][0-9]*[:]([^\]]*)[\]]: Transfer Completed /
#completed_speed = / XDCC [\[][0-9]*[:][^\]]*[\]]: Transfer Completed .[0-9]* KB, [^,]*, ([0-9]*[.][0-9]) /
# ** 2006-02-10-06:18:02: Stat: 1/20 Sls, 0/20 Q, 2464.8K/s Rcd, 0 SrQ (Bdw: 8100K, 67.5K/s, 2473.1K/s Rcd)

# Die Gruppierungen im regulären Ausdruck
# werden in den LogHashtabellen gezählt
def log_search( *hash )
	# String-Objekt aus der Schleife ziehen
	split = "\n"
	# hash-Indizes an MatchData angleichen
	hash.unshift nil

	loop {
		# nächster Block und der Rest der Zeile
		data = STDIN.read(4095) or break
		data += (STDIN.gets || "")

		for line in data.split(split)
			for i in 1...hash.length
				if md = hash[i].re.match(line)
					hash[i][md[1]] += 1
					if not $Debug.nil?
						$Debug << "#{line}\n"
					end
				end
			end
		end
	}

	# Karteileichen beseitigen
	hash.each { |h| h.delete(nil) if h }
	hash[1..-1]
end

$Debug = File.open( 'Debug.log', 'w')

list = log_search(
	LogHash.new( request_pack, 'Pack', 'Anfragen' ),
	LogHash.new( request_nick, 'Nick', 'Anfragen' ),
	LogHash.new( connected_nick, 'Nick', 'Verbindungen' ),
	LogHash.new( completed_nick, 'Nick', 'Downloads' )
	)

$Debug.close

list.each { |top|
	top.printtop( 10 )
}

exit 0
# eof
