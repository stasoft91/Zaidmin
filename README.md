# Zaidmin

Dead simple CLI NGINX vhost manager for PHP

## Getting Started

__Disclaimer:__ _Zaidmin works best on Ubuntu and PHP7.2, some modifications might be needed otherwise._

### Basic Installation

* Zaidmin itself shall install composer and its requirees, just run ```./zaidmin```
* If Thou wants to run it from everywhere, do not fret to use ```ln -s ./zaidmin /usr/bin/zaidmin```

### What it does

* Creates conf file in sites-available
* Creates symlink of it to sites-enabled
* Add necessary line to /etc/hosts
* Restart nginx


* Removes conf file link from sites-enabled if asked
* Exits :)
