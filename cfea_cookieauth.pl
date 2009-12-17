#!/usr/bin/perl

use LWP::Simple;
$| = 1;

while (<STDIN>) {
	chomp($_);
	($url, $cookiestring) = split(/c%%c/, $_, 2);
	@parts = split(/\//, $url);
	my $response = get 'http://oxford-club.local/?cfea_check_user=true&cfea_request_uri=/'.@parts[1].'/'.@parts[2].'/'.@parts[3].'/&cfea_cookie_val='.$cookiestring;
	
	# don't forget the newline character!
	print $response."\n";
}