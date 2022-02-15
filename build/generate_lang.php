<?php

/**
 *  ________  ________  ________   ___  __    _______   ________
 * |\   __  \|\   __  \|\   ___  \|\  \|\  \ |\  ___ \ |\   ___ \
 * \ \  \|\  \ \  \|\  \ \  \\ \  \ \  \/  /|\ \   __/|\ \  \_|\ \
 *  \ \   _  _\ \   __  \ \  \\ \  \ \   ___  \ \  \_|/_\ \  \ \\ \
 *   \ \  \\  \\ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \_|\ \ \  \_\\ \
 *    \ \__\\ _\\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \_______\
 *     \|__|\|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|_______|.
 *
 *    Copyright [2022] [DiamondStrider1]
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

declare(strict_types=1);

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler('exception_error_handler');

function keyToCamelCase(string $keyName): string
{
    return str_replace('.', '_', $keyName);
}

$diff = false;
for ($i = 1; $i < $argc; ++$i) {
    if ('--diff' === $argv[$i]) {
        $diff = true;
    }
}

$data = <<<'EOT'
<?php

/**
 *  ________  ________  ________   ___  __    _______   ________
 * |\   __  \|\   __  \|\   ___  \|\  \|\  \ |\  ___ \ |\   ___ \
 * \ \  \|\  \ \  \|\  \ \  \\ \  \ \  \/  /|\ \   __/|\ \  \_|\ \
 *  \ \   _  _\ \   __  \ \  \\ \  \ \   ___  \ \  \_|/_\ \  \ \\ \
 *   \ \  \\  \\ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \_|\ \ \  \_\\ \
 *    \ \__\\ _\\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \_______\
 *     \|__|\|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|_______|.
 *
 *    Copyright [2022] [DiamondStrider1]
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

declare(strict_types=1);

namespace DiamondStrider1\Ranked\language;

class Language
{
    /** @var string[] */
    private array $texts = [];

    /**
     * @param resource $langFile
     */
    public function __construct($langFile)
    {
        while (($line = fgets($langFile)) !== false) {
            $explosion = explode('=', rtrim($line));
            $key = $explosion[0] ?? null;
            $value = $explosion[1] ?? null;
            if (null === $key || null === $value) {
                continue;
            }
            $this->texts[$key] = $value;
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function getRaw(string $key, array $params): string
    {
        $text = $this->texts[$key] ?? null;
        if (null === $text) {
            return $key;
        }
        foreach ($params as $name => $param) {
            $text = str_replace("%{$name}%", $param, $text);
        }

        return $text;
    }
EOT;

// Use en_US as a reference
$texts = explode("\n", file_get_contents(__DIR__.'/../resources/langs/en_US.ini'));
foreach ($texts as $text) {
    $explosion = explode('=', $text);
    $key = $explosion[0] ?? null;
    $value = $explosion[1] ?? null;
    if (null === $key || null === $value) {
        continue;
    }

    if (false === preg_match_all('/%(\\w*)%/', $value, $params)) {
        throw new AssertionError('preg_match() failed!');
    }
    $params = array_unique($params[1]);
    $typedParams = implode(', ', array_map(fn ($p) => "string \${$p}", $params));
    $arrayParams = implode(",\n            ", array_map(fn ($p) => "'{$p}' => \${$p}", $params));

    $methodName = keyToCamelCase($key);
    $data .= <<<EOT


    public function {$methodName}({$typedParams}): string
    {
        return \$this->getRaw("{$key}", [
            {$arrayParams}
        ]);
    }
EOT;
}
$data .= "\n}\n";

if (!$diff) {
    $f = fopen(__DIR__.'/../src/DiamondStrider1/Ranked/language/Language.php', 'w');
    fwrite($f, $data);
    fclose($f);
} else {
    $cmp = file_get_contents(__DIR__.'/../src/DiamondStrider1/Ranked/language/Language.php');
    if ($data !== $cmp) {
        exit(1);
    }
}
