<?php

namespace I18n;

class I18n
{
    private $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function s(string $key, ...$args)
    {
        if (!array_key_exists($key, $this->messages)) return '';

        $message = $this->messages[$key];

        foreach ($args as $argKey => $arg) {
            $message = str_replace('{' . $argKey . '}', $arg, $message);
        }

        return htmlspecialchars($message);
    }
}
