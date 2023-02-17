<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable

use Dogma\Re;

set_time_limit(0);

$options = [
    'size' => 'range',
    'time' => 'range',
    'redir' => 'range',
    'status' => 'choose',
];


$request = [];

foreach ($options as $option => $type) {
    if (!isset($_GET[$option])) {
        continue;
    }

    // select random value within range. eg: "100K-20M" for response between 100 kB and 20 MB
    if ($type === 'range') {
        $mm = explode('-', strtoupper($_GET[$option]));
        if (count($mm) > 1) {
            $min = abs((int) $mm[0]);
            if (strpos($mm[0], 'K') !== false) {
                $min *= 1000;
            }
            if (strpos($mm[0], 'M') !== false) {
                $min *= 1000000;
            }

            $max = abs((int) $mm[1]);
            if (strpos($mm[1], 'K') !== false) {
                $max *= 1000;
            }
            if (strpos($mm[1], 'M') !== false) {
                $max *= 1000000;
            }

            if ($min > $max) {
                [$min, $max] = [$max, $min];
            }

            $request[$option] = random_int($min, $max); // @phpstan-ignore-line
        } else {
            $size = abs((int) $_GET[$option]);
            if (strpos($_GET[$option], 'K') !== false) {
                $size *= 1000;
            }
            if (strpos($_GET[$option], 'M') !== false) {
                $size *= 1000000;
            }

            $request[$option] = $size;
        }
    }

    // select random value from given options. every minus sign after value means 10x lover probability
    if ($type === 'choose') {
        $arr = explode(',', $_GET[$option]);

        if (count($arr) > 1) {
            $rats = [];
            $sum = 0.0;
            foreach ($arr as $st) {
                $ratio = 1 / (10 ** strlen(Re::replace($st, '/[^-]/', '')));
                $status = (int) $st;
                $rats[$status] = $ratio;
                $sum += $ratio;
            }

            $rand = random_int(0, intval($sum * 1000000000)) / 1000000000;
            $selected = 0;
            foreach ($rats as $status => $ratio) {
                if ($rand < $ratio) {
                    $selected = $status;
                    break;
                }
                $rand -= $ratio;
            }

            $request['status'] = $selected ?: key($rats);
        } else {
            $request[$option] = (int) $_GET[$option];
        }
    }
}


$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';


// redirect
if (!empty($request['redir'])) {
    $request['redir']--;
    if ($request['status'] === 200) {
        unset($request['status']);
    }
    if ($request['redir'] < 1) {
        unset($request['redir']);
    }

    $urlParts = explode('?', $_SERVER['REQUEST_URI']);
    $url = $urlParts[0] . '?' . http_build_query($request);
    header($protocol . ' ' . 301, true, 301);
    header('Location: ' . $url);
    exit;
}


// defaults
if (empty($request['size'])) {
    $request['size'] = 20000;
}
if (empty($request['time'])) {
    $request['time'] = 0;
}
if (empty($request['status'])) {
    $request['status'] = 200;
}


// response status
if ($request['status'] !== 200) {
    header($protocol . ' ' . $request['status'], true, $request['status']);
}


echo "<!DOCTYPE HTML>\n";
echo "<html lang='en'>\n<head><title></title></head>\n<body><pre>\n";
echo "<h1>Testing response:</h1>\n";
echo sprintf("Size: %s\n", number_format($request['size']));
echo sprintf("Time: %s\n", $request['time']);
echo sprintf("Status: %s\n", $request['status']);

$chars = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklxcvbnm1234567890[](){}+|\,.-~@#$%^&*\'"=/`;:_?!';
$chars = str_split($chars);

if ($request['time']) {
    $chunkSize = (int) ($request['size'] / $request['time']);
    for ($n = 0; $n < $request['size']; $n++) {
        if ($n % $chunkSize === 0) {
            sleep(1);
        }
        if ($n % 100 === 0) {
            echo "\n";
            if ($n % 1000 === 0) {
                echo "\n";
            }
        }
        echo $chars[random_int(0, 90)];
    }
} else {
    for ($n = 0; $n < $request['size']; $n++) {
        if ($n % 100 === 0) {
            echo "\n";
            if ($n % 1000 === 0) {
                echo "\n";
            }
        }
        echo $chars[random_int(0, 90)];
    }
}


echo '<h2>Done!</h2>';
echo "<pre>\n</body>\n</html>";
