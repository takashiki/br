<?php

include __DIR__ . "/vendor/autoload.php";

$html = file_get_contents("index.html");

$regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';

preg_match_all($regex, $html, $ms);

$urls = array_filter(array_unique($ms[0]), function ($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
});

foreach ($urls as $url) {
    $response = \Zttp\Zttp::withoutRedirecting()->get($url);
    if ($response->status() != 301) {
        continue;
    }

    $searches[] = $url;
    $replaces[] = $response->header('Location');
}

if (empty($searches) || empty($replaces)) {
    file_put_contents('info.log', 'nothing to replace' . PHP_EOL, FILE_APPEND);
    exit;
}
file_put_contents('info.log', 'urls: ' . json_encode($searches) . ' replaced by: ' . json_encode($replaces) . PHP_EOL, FILE_APPEND);
$html = str_replace($searches, $replaces, $html);
file_put_contents("index.html", $html);

