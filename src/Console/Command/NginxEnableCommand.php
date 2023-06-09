<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Triangle\Engine\Console\Command;

use Triangle\Engine\Console\Input\InputInterface;
use Triangle\Engine\Console\Output\OutputInterface;

class NginxEnableCommand extends Command
{
    protected static ?string $defaultName = 'nginx:enable';
    protected static ?string $defaultDescription = 'Добавить сайт в Nginx';

    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = config('nginx.path', "/etc/nginx/sites-enabled");

        if ($path !== false && !is_dir($path)) {
            $output->writeln("<error>Папка $path не существует</>");
            return self::FAILURE;
        }

        if (empty(config('app.domain'))) {
            $output->writeln("<error>Не задан app.domain</>");
            return self::FAILURE;
        }

        $domain = config('app.domain');
        $directory = base_path();
        $file = $directory . "/resources/nginx.conf";

        if (!is_file($file)) {
            // Внутренний IP
            $listen = explode('://', config('server.listen'))[1];
            $port = explode(':', $listen)[1];

            // Внешние IP 
            $listen_http = '';
            $listen_https = '';

            // Парсинг IPv4
            if (is_array(config('nginx.ipv4'))) {
                foreach (config('nginx.ipv4') as $ipv4) {
                    $listen_https .= <<<EOF
                        listen $ipv4:443 ssl http2;

                    EOF;
                    $listen_http .= <<<EOF
                        listen $ipv4:80;

                    EOF;
                }
            } else {
                $listen_https .= <<<EOF
                    listen 443 ssl http2;

                EOF;
                $listen_http .= <<<EOF
                    listen 80;

                EOF;
            }

            // Парсинг IPv6
            if (is_array(config('nginx.ipv6'))) {
                foreach (config('nginx.ipv6') as $ipv6) {
                    $listen_https .= <<<EOF
                        listen [$ipv6]:443 ssl http2;

                    EOF;
                    $listen_http .= <<<EOF
                        listen [$ipv6]:80;

                    EOF;
                }
            }

            // Доп. вставка
            $includes = config('nginx.includes', '');

            // Логи
            $error_log = !empty(config('nginx.error_log')) ? "error_log " . config('nginx.error_log') . ";" : '';
            $access_log = !empty(config('nginx.access_log')) ? "access_log " . config('nginx.access_log') . ";" : '';

            // SSL
            $ssl_certificate = !empty(config('nginx.ssl_certificate')) ? "ssl_certificate \"" . config('nginx.ssl_certificate') . "\";" : '';
            $ssl_certificate_key = !empty(config('nginx.ssl_certificate_key')) ? "ssl_certificate_key \"" . config('nginx.ssl_certificate_key') . "\";" : '';

            // Основная конфигурация
            $conf_content = <<<EOF
                set \$root_path $directory/public;
                root \$root_path;
                disable_symlinks if_not_owner from=\$root_path;
            
                location / {
                    proxy_pass          http://Triangle$port;
                    proxy_pass_header   Server;
                    
                    proxy_set_header    Host               \$host;
                    proxy_set_header    Connection          "";

                    proxy_set_header    X-Real-IP          \$remote_addr;
                    proxy_set_header    X-Forwarded-For    \$proxy_add_x_forwarded_for;
                    proxy_set_header    X-Forwarded-Port   \$server_port;
                    proxy_set_header    X-Forwarded-Proto  \$scheme;

                    proxy_set_header    QUERY_STRING       \$query_string;
                    proxy_set_header    REQUEST_METHOD     \$request_method;
                    proxy_set_header    CONTENT_TYPE       \$content_type;
                    proxy_set_header    CONTENT_LENGTH     \$content_length;

                    proxy_set_header    REQUEST_URI        \$request_uri;
                    proxy_set_header    PATH_INFO          \$document_uri;
                    proxy_set_header    DOCUMENT_ROOT      \$document_root;
                    proxy_set_header    SERVER_PROTOCOL    \$server_protocol;
                    proxy_set_header    REQUEST_SCHEME     \$scheme;
                    proxy_set_header    HTTPS              \$https;

                    proxy_set_header    REMOTE_ADDR        \$remote_addr;
                    proxy_set_header    REMOTE_PORT        \$remote_port;
                    proxy_set_header    SERVER_PORT        \$server_port;
                    proxy_set_header    SERVER_NAME        \$server_name;
                }
            EOF;

            if (!empty($ssl_certificate)) {
                // Конфигурация для HTTPS
                $conf = <<<EOF
                upstream Triangle$port {
                    server $listen;
                    keepalive 10240;
                }
                server {
                    server_name $domain;
                    
                    charset utf-8;
                $listen_https
                    $ssl_certificate
                    $ssl_certificate_key
                    add_header Strict-Transport-Security "max-age=31536000" always;
                    
                $conf_content
                
                    $includes
                    $error_log
                    $access_log
                }
                
                server {
                    server_name $domain;

                    charset utf-8;
                $listen_http
                
                    return 301 https://\$host\$request_uri;
                
                    $error_log
                    $access_log
                }
                EOF;
            } else {
                // Конфигурация для HTTP
                $conf = <<<EOF
                upstream Triangle$port {
                    server $listen;
                    keepalive 10240;
                }
                server {
                    server_name $domain;

                    charset utf-8;
                $listen_http
                
                $conf_content
                
                    $includes
                    $error_log
                    $access_log
                }
                EOF;
            }

            $fstream = fopen($file, 'w');
            fwrite($fstream, $conf);
            fclose($fstream);

            $output->writeln("<comment>Конфигурация создана</>");
        }

        if ($path !== false) {
            exec("ln -sf $file $path/$domain.conf");
            $output->writeln("<info>Ссылка создана</>");

            $output->writeln("<info>Проверка конфигурации:</>");
            exec("nginx -t");

            exec("service nginx restart");
            $output->writeln("<info>Nginx перезагружен</>");
        }

        return self::SUCCESS;
    }
}
