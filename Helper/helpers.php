<?php

declare(strict_types=1);

use Illuminate\Support\Str;

function template($asset = false)
{
    $activeTheme = config('basic.theme');
    if ($asset) {
        return 'assets/themes/'.$activeTheme.'/';
    }

    return 'themes.'.$activeTheme.'.';
}

function recursive_array_replace($find, $replace, $array)
{
    if (!is_array($array)) {
        return str_replace($find, $replace, $array);
    }
    $newArray = [];
    foreach ($array as $key => $value) {
        $newArray[$key] = recursive_array_replace($find, $replace, $value);
    }

    return $newArray;
}

function menuActive($routeName, $type = null)
{
    $class = 'active';
    if (3 == $type) {
        $class = 'selected';
    } elseif (2 == $type) {
        $class = 'has-arrow active';
    } elseif (1 == $type) {
        $class = 'in';
    }
    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    } elseif (request()->routeIs($routeName)) {
        return $class;
    }
}

function getFile($image, $clean = '')
{
    return file_exists($image) && is_file($image) ? asset($image).$clean : asset(config('location.default'));
}

function removeFile($path)
{
    return file_exists($path) && is_file($path) ? @unlink($path) : false;
}

function loopIndex($object)
{
    return ($object->currentPage() - 1) * $object->perPage() + 1;
}

function getAmount($amount, $length = 0)
{
    if (0 < $length) {
        return number_format($amount + 0, $length);
    }

    return $amount + 0;
}

function strRandom($length = 12)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

function diffForHumans($date)
{
    $lang = session()->get('lang');
    \Carbon\Carbon::setlocale($lang);

    return \Carbon\Carbon::parse($date)->diffForHumans();
}

function dateTime($date, $format = 'd M, Y h:i A'): string
{
    return date($format, $date->timestamp);
}

if (!function_exists('putPermanentEnv')) {
    function putPermanentEnv($key, $value): void
    {
        $path = app()->environmentFilePath();
        $escaped = preg_quote('='.env($key), '/');
        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$value}",
            file_get_contents($path)
        ));
    }
}

function checkTo($currencies, $selectedCurrency = 'USD')
{
    foreach ($currencies as $key => $currency) {
        if (property_exists($currency, strtoupper($selectedCurrency))) {
            return $key;
        }
    }
}

function code($length = 6): int
{
    if (0 == $length) {
        return 0;
    }
    $min = 10 ** ($length - 1);
    $max = 0;
    while ($length > 0 && $length--) {
        $max = ($max * 10) + 9;
    }

    return random_int($min, $max);
}

function invoice(): string
{
    return time().code(4);
}

function wordTruncate($string, $offset = 0, $length = null): string
{
    $words = explode(' ', $string);
    isset($length) ? array_splice($words, $offset, $length) : array_splice($words, $offset);

    return implode(' ', $words);
}

function linkToEmbed($string)
{
    if (str_contains($string, 'youtube')) {
        $words = explode('/', $string);
        if (false == strpos($string, 'embed')) {
            array_splice($words, -1, 0, 'embed');
        }
        $words = str_ireplace('watch?v=', '', implode('/', $words));

        return $words;
    }

    return $string;
}

function slug($title): string
{
    return \Illuminate\Support\Str::slug($title);
}

function title2snake($string): string
{
    return Str::title(str_replace(' ', '_', $string));
}

function snake2Title($string): string
{
    return Str::title(str_replace('_', ' ', $string));
}

function kebab2Title($string): string
{
    return Str::title(str_replace('-', ' ', $string));
}

function getLevelUser($id): array
{
    $ussss = new \App\Models\User();

    return $ussss->referralUsers([$id]);
}

function getPercent($total, $current): float
{
    if ($current > 0 && $total > 0) {
        $percent = (($current * 100) / $total) ?: 0;
    } else {
        $percent = 0;
    }

    return round($percent, 0);
}

function flagLanguage($data): string
{
    return '{'.rtrim($data, ',').'}';
}

function getIpInfo(): array
{
    $ip = null;
    $deep_detect = true;

    if (false === filter_var($ip, \FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], \FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], \FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
    }
    $xml = @simplexml_load_file('http://www.geoplugin.net/xml.gp?ip='.$ip);

    $country = @$xml->geoplugin_countryName;
    $city = @$xml->geoplugin_city;
    $area = @$xml->geoplugin_areaCode;
    $code = @$xml->geoplugin_countryCode;
    $long = @$xml->geoplugin_longitude;
    $lat = @$xml->geoplugin_latitude;

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = 'Unknown OS Platform';
    $os_array = [
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i' => 'Mac OS 9',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/iphone/i' => 'iPhone',
        '/ipod/i' => 'iPod',
        '/ipad/i' => 'iPad',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/webos/i' => 'Mobile',
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }
    $browser = 'Unknown Browser';
    $browser_array = [
        '/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/safari/i' => 'Safari',
        '/chrome/i' => 'Chrome',
        '/edge/i' => 'Edge',
        '/opera/i' => 'Opera',
        '/netscape/i' => 'Netscape',
        '/maxthon/i' => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i' => 'Handheld Browser',
    ];
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }

    $data['country'] = $country;
    $data['city'] = $city;
    $data['area'] = $area;
    $data['code'] = $code;
    $data['long'] = $long;
    $data['lat'] = $lat;
    $data['os_platform'] = $os_platform;
    $data['browser'] = $browser;
    $data['ip'] = request()->ip();
    $data['time'] = date('d-m-Y h:i:s A');

    return $data;
}

function resourcePaginate($data, $callback)
{
    return $data->setCollection($data->getCollection()->map($callback));
}

function clean($string): array|string|null
{
    $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
function camelToWord($str): string
{
    $arr = preg_split('/(?=[A-Z])/', $str);

    return trim(implode(' ', $arr));
}
