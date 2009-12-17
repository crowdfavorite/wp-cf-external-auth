#!/usr/bin/perl
use Digest::MD5  qw(md5_hex);
$| = 1;

while (<STDIN>) {
	chomp($_);
	($url, $cookiestring) = split(/c%%c/, $_, 2);
	
	#($base, $dir1, $dir2, $remainder) = split(/\//, $url, 4);
	@parts = split(/\//, $url);
	
	@cookies = split(/;/, $cookiestring);
	$valid = 0;
	foreach $cookie (@cookies) {
		$cookie =~ s/\s+//;
		($name, $val) = split(/=/, $cookie, 2);
		if ($name eq "cfea_auth") {
			($salt, $hash) = split(/\|/, $val, 2);
			if (md5_hex($salt.'/'.@parts[1].'/'.@parts[2].'/'.@parts[3].'/') eq $hash) {
				$valid = 1;
				break;
			}
		}
	}
	if ($valid == 1) {
		print "allowed\n";
	}
	else {
		print "denied\n";
	}
}