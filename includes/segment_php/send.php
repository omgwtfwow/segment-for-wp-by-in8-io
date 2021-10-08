<?php

/**
 * Make sure both are set
 */
if (!isset($args["secret"])) die();
if (!isset($args["file"])) die();
if (!isset($args["timeout"])) die();
$file = $args["file"];

/**
 * Rename the file so we don't write the same calls
 * multiple times
 */
$dir = dirname($file);
$old = $file;
$file = $dir . '/analytics-' . rand() . '.log';
if (!file_exists($old)) {
    exit(0);
}
if (!rename($old, $file)) {
    exit(1);
}

/**
 * File contents.
 */
$contents = file_get_contents($file);
$lines = explode("\n", $contents);

/**
 * Initialize the client.
 */
Segment::init($args["secret"], array(
    "consumer" => "socket",
    "timeout" => $args["timeout"],
    "error_handler" => function ($code, $msg) {
        exit(1);
    }
));

/**
 * Payloads
 */
$total = 0;
$successful = 0;
foreach ($lines as $line) {
    if (!trim($line)) continue;
    $payload = json_decode($line, true);
    $dt = new DateTime($payload["timestamp"]);
    $ts = floatval($dt->getTimestamp() . "." . $dt->format("u"));
    $payload["timestamp"] = $ts;
    $type = $payload["type"];
    $ret = call_user_func_array(array("Segment", $type), array($payload));
    if ($ret) $successful++;
    $total++;
    if ($total % 100 === 0) Segment::flush();
}
Segment::flush();
unlink($file);

/**
 * Sent
 */
exit(0);