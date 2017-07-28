#!/usr/bin/perl

use strict;
use warnings;

my $gateway = "example-gateway1";
my ($packets, $bytes) = @ARGV;
my $num = int(rand(922337200000000));
my $saltednum = $num . "secretandlongstring";
my $token = `echo -n $saltednum | sha256sum | head -c 64`;
my $url = "http://example.com/lorastat?lora_gw=" . $gateway . "&packets=" . $packets . "&bytes=" . $bytes . "&num=" . $num . "&token=" . $token;
print `wget -qO- '$url' &> /dev/null`
