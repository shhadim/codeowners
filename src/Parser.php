<?php
declare(strict_types=1);

namespace CodeOwners;

use CodeOwners\Exception\UnableToParseException;

final class Parser
{
    /**
     * @param string $file
     * @return Pattern[]
     * @throws UnableToParseException
     */
    public function parse(string $file): array
    {
        $patterns = [];

        $handle = $this->getReadHandle($file);
        while ($line = fgets($handle)) {
            $line = trim($line);

            if (mb_substr($line, 0, 1) === '#') {
                // comment
                continue;
            }

            if ($line === '') {
                // empty line
                continue;
            }

            if (preg_match('/^(?P<file_pattern>[^\s]+)\s+(?P<owners>.+)$/si', $line, $matches) === 0) {
                // unable to read line, might be empty
                // todo Maybe this deserves an exception
                continue;
            }

            $owners = array_map('trim', preg_split('/\s+/', $matches['owners']));
            $patterns[] = new Pattern($matches['file_pattern'], $owners);
        }
        fclose($handle);

        return $patterns;
    }

    private function getReadHandle(string $file)
    {
        if (file_exists($file) === false) {
            throw new UnableToParseException("File {$file} does not exist");
        }

        if (is_readable($file) === false) {
            throw new UnableToParseException("File {$file} is not readable");
        }

        $handle = fopen($file, 'rb');
        if (is_resource($handle) === false) {
            throw new UnableToParseException("Unable to create a reading resource for {$file}");
        }

        return $handle;
    }
}