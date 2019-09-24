<?php
require "vendor/autoload.php";

use League\CLImate\CLImate;

$cli = new CLImate();

$cli->flank('ZAIDMIN - NGINX Admin');
$cli->info('a - Add vhost');
$cli->info('d - Remove vhost');
$cli->info('e - Exit');

$input = $cli->input('Select mode:');
$input->accept(['a', 'd', 'e'], true);
$input->strict();
$response = $input->prompt();


if ($response === 'a') {
    $input = $cli->input('Vhost name:');
    $vhost_name = $input->prompt();

    $input = $cli->input('Base path to project folder root [/home/'.get_current_user().'/www/'.$vhost_name.']:');
    $input->defaultTo('/home/'.get_current_user().'/www/');
    $path = $input->prompt();

    $input = $cli->input('Projects public folder [public]:');
    $input->defaultTo('public');
    $path = $input->prompt();


    $template = '
server {
        listen 80;
        listen [::]:80;

        server_name '.$vhost_name.';
        set $base '.$path.';
        root $base/public;

        # index.php
        index index.php;

        # index.php fallback
        location / {
                try_files $uri $uri/ /index.php?$query_string;
        }

        # handle .php
        location ~ \.php$ {
                # 404
                try_files $fastcgi_script_name =404;
                
                # default fastcgi_params
                include fastcgi_params;
                
                # fastcgi settings
                fastcgi_pass			unix:/var/run/php/php7.2-fpm.sock;
                fastcgi_index			index.php;
                fastcgi_buffers			8 16k;
                fastcgi_buffer_size		32k;
                
                # fastcgi params
                fastcgi_param DOCUMENT_ROOT		$realpath_root;
                fastcgi_param SCRIPT_FILENAME	$realpath_root$fastcgi_script_name;
                fastcgi_param PHP_ADMIN_VALUE	"open_basedir=$base/:/usr/lib/php/:/tmp/";
        }
}
';


    file_put_contents($vhost_name.'.conf', $template);
    $from = $vhost_name.'.conf';
    $to = '/etc/nginx/sites-available/' . $vhost_name.'.conf';

    exec('cp ' . $from . ' ' . $to);
    exec('cp -s ' . $to . ' ' . '/etc/nginx/sites-enabled/' . $vhost_name.'.conf');
    exec('echo "127.0.0.1  '.$vhost_name.'" >> /etc/hosts');

    exec('service nginx restart');
    unlink($from);
}

if ($response === 'd') {

    $list = array_filter(scandir('/etc/nginx/sites-enabled/'), function($str)
    {
        return $str !== '.' && $str !== '..';
    });

    $max_index = 0;
    foreach($list as $index => $option) {
        $cli->info('['.$index.'] '. $option);
        $max_index = $index;
    }

    $input = $cli->input('Select which vhost to disable or [e] to exit:');
    $input->accept(array_merge(range(2, $max_index), ['e']), true);
    $input->strict();
    $index = $input->prompt();
    if ((integer)$index < 2)
        exit;

    exec('rm /etc/nginx/sites-enabled/' . $list[$index].'.conf');

    exec('service nginx restart');
}
