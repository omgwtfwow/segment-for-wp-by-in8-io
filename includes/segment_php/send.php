<?php

declare(strict_types=1);

/**
 * Make sure both are set
 */

if (!isset($args['secret'])) {
    die('--secret must be given');
}
if (!isset($args['file'])) {
    die('--file must be given');
}
if (!isset($args["timeout"])) {
	die('--timeout must be given');
};
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
    if (!trim($line)) {
        continue;
    }
    $total++;
    $payload = json_decode($line, true);
    $dt = new DateTime($payload['timestamp']);
    $ts = (float)($dt->getTimestamp() . '.' . $dt->format('u'));
    $payload['timestamp'] = date('c', (int)$ts);
    $type = $payload['type'];
    $currentBatch[] = $payload;
    // flush before batch gets too big
    if (mb_strlen((json_encode(['batch' => $currentBatch, 'sentAt' => date('c')])), '8bit') >= 512000) {
        $libCurlResponse = Segment::flush();
        if ($libCurlResponse) {
            $successful += count($currentBatch) - 1;
        //} else {
            // todo: maybe write batch to analytics-error.log for more controlled errorhandling
        }
        $currentBatch = [];
    }
    $payload['timestamp'] = $ts;
    call_user_func([Segment::class, $type], $payload);
}

unlink($file);

/**
 * Sent
 */
exit(0);