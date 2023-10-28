<?php

use support\Request;
use Triangle\Engine\App;
use Triangle\Engine\Http\Request as TriangleRequest;
use Triangle\Engine\Route;

/**
 * @return TriangleRequest|Request|null
 */
function request(): TriangleRequest|Request|null
{
    return App::request();
}

/**
 * @param string $name
 * @param ...$parameters
 * @return string
 */
function route(string $name, ...$parameters): string
{
    $route = Route::getByName($name);
    if (!$route) {
        return '';
    }

    if (!$parameters) {
        return $route->url();
    }

    if (is_array(current($parameters))) {
        $parameters = current($parameters);
    }

    return $route->url($parameters);
}

/**
 * @param mixed|null $key
 * @param mixed|null $default
 * @return mixed
 * @throws Exception
 */
function session(mixed $key = null, mixed $default = null): mixed
{
    $session = request()->session();
    if (null === $key) {
        return $session;
    }
    if (is_array($key)) {
        $session->put($key);
        return null;
    }
    if (strpos($key, '.')) {
        $keyArray = explode('.', $key);
        $value = $session->all();
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
    return $session->get($key, $default);
}

/**
 * Получение IP-адреса
 *
 * @return string|null IP-адрес
 */
function getRequestIp(): ?string
{
    $ip = request()->header(
        'x-real-ip',
        request()->header(
            'x-forwarded-for',
            request()->header(
                'client-ip',
                request()->header(
                    'x-client-ip',
                    request()->header(
                        'remote-addr',
                        request()->header(
                            'via',
                            request()->getRealIp()
                        )
                    )
                )
            )
        )
    );
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : (request()->getRealIp() ?? null);
}

/**
 * Валидация IP-адреса
 *
 * @param string $ip IP-адрес
 *
 * @return boolean
 */
function validateIp(string $ip): bool
{
    if (strtolower($ip) === 'unknown')
        return false;
    $ip = ip2long($ip);
    if ($ip !== false && $ip !== -1) {
        $ip = sprintf('%u', $ip);
        if ($ip >= 0 && $ip <= 50331647)
            return false;
        if ($ip >= 167772160 && $ip <= 184549375)
            return false;
        if ($ip >= 2130706432 && $ip <= 2147483647)
            return false;
        if ($ip >= 2851995648 && $ip <= 2852061183)
            return false;
        if ($ip >= 2886729728 && $ip <= 2887778303)
            return false;
        if ($ip >= 3221225984 && $ip <= 3221226239)
            return false;
        if ($ip >= 3232235520 && $ip <= 3232301055)
            return false;
        if ($ip >= 4294967040)
            return false;
    }
    return true;
}

/**
 * @param string $ip
 * @return bool
 */
function isIntranetIp(string $ip): bool
{
    // Не IP.
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    // Точно ip Интранета? Для IPv4 FALSE может быть не точным, поэтому нам нужно проверить его вручную ниже.
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return true;
    }
    // Ручная проверка IPv4.
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }

    // Ручная проверка
    // $reservedIps = [
    //     '167772160'  => 184549375,  // 10.0.0.0 -  10.255.255.255
    //     '3232235520' => 3232301055, // 192.168.0.0 - 192.168.255.255
    //     '2130706432' => 2147483647, // 127.0.0.0 - 127.255.255.255
    //     '2886729728' => 2887778303, // 172.16.0.0 -  172.31.255.255
    // ];
    $reservedIps = [
        1681915904 => 1686110207,   // 100.64.0.0 -  100.127.255.255
        3221225472 => 3221225727,   // 192.0.0.0 - 192.0.0.255
        3221225984 => 3221226239,   // 192.0.2.0 - 192.0.2.255
        3227017984 => 3227018239,   // 192.88.99.0 - 192.88.99.255
        3323068416 => 3323199487,   // 198.18.0.0 - 198.19.255.255
        3325256704 => 3325256959,   // 198.51.100.0 - 198.51.100.255
        3405803776 => 3405804031,   // 203.0.113.0 - 203.0.113.255
        3758096384 => 4026531839,   // 224.0.0.0 - 239.255.255.255
    ];

    $ipLong = ip2long($ip);

    foreach ($reservedIps as $ipStart => $ipEnd) {
        if (($ipLong >= $ipStart) && ($ipLong <= $ipEnd)) {
            return true;
        }
    }
    return false;
}

/**
 * Получение данных
 *
 * @return array(
 *      'userAgent',
 *      'name',
 *      'version',
 *      'platform'
 *  )
 */
function getBrowser(): array
{
    $u_agent = request()->header('user-agent');
    // echo $u_agent;
    $bname = 'Неизвестно';
    $ub = "Неизвестно";
    $platform = 'Неизвестно';

    if (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    } elseif (preg_match('/iphone|IPhone/i', $u_agent)) {
        $platform = 'IPhone Web';
    } elseif (preg_match('/android|Android/i', $u_agent)) {
        $platform = 'Android Web';
    } else if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $u_agent)) {
        $platform = 'Mobile';
    } else if (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }

    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    preg_match_all($pattern, $u_agent, $matches);

    // if (!empty($matches['browser'])) {
    $i = count($matches['browser']);
    // }
    // if (!empty($matches['version'])) {
    if ($i != 1) {
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }
    // }

    if ($version == null || $version == "") {
        $version = "?";
    }
    return array(
        'userAgent' => $u_agent,
        'name' => $bname,
        'version' => $version,
        'platform' => $platform
    );
}