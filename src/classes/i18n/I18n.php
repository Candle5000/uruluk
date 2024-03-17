<?php

namespace I18n;

class I18n
{
    private $messages;
    private $langCode;
    private $knownLanguages;

    public function __construct(array $messages, string $langCode, array $knownLanguages)
    {
        $this->messages = $messages;
        $this->langCode = $langCode;
        $this->knownLanguages = $knownLanguages;
    }

    public function s(?string $key, ...$args)
    {
        if (!array_key_exists($key, $this->messages)) return '';

        $message = $this->messages[$key];

        foreach ($args as $argKey => $arg) {
            $message = str_replace('{' . $argKey . '}', $arg, $message);
        }

        return htmlspecialchars($message);
    }

    public function getLangCode()
    {
        return $this->langCode;
    }

    public function getKnownLanguages()
    {
        return $this->knownLanguages;
    }
}
