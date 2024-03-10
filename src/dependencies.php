<?php

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // pdo mysql
    $container['db'] = function ($c) {
        $db = $c['settings']['db'];
        $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    };

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // i18n
    $container['i18n'] = function ($c) {
        $defaultLanguage = 'en';
        $knownLanguages = ['en', 'ja'];
        $language = array_key_exists('language', $_COOKIE) ? $_COOKIE['language'] : null;
        $strategy = function (array $locale) use ($knownLanguages) {
            $is_wildcard = isset($locale['language']) && $locale['language'] === '*';
            if (empty($locale['language']) && !$is_wildcard) return null;
            if ($is_wildcard || $locale['language'] === 'zh') {
                if (!empty($locale['region']) && $locale['region'] == 'TW') return 'zh_tw';
                if (!empty($locale['script']) && $locale['script'] == 'Hant') return 'zh_tw';
                if ($locale['language'] === 'zh') return 'zh_cn';
            }
            if (in_array($locale['language'], $knownLanguages)) return $locale['language'];
            return null;
        };
        if (!in_array($language, $knownLanguages)) {
            $language = \Teto\HTTP\AcceptLanguage::detect($strategy, $defaultLanguage);
        }

        $parser = new \I18n\YamlFileParser();
        return new \I18n\I18n($parser->parse($language . '.yaml', $c->get('logger')));
    };

    // csrf guard
    $container['csrf'] = function ($c) {
        $csrf = new \Slim\Csrf\Guard();
        $csrf->setPersistentTokenMode(true);
        return $csrf;
    };

    // Google Service
    $container['google'] = function ($c) {
        $settings = $c->get('settings')['google'];
        $google = [
            'analytics_id' => $settings['analytics_id'],
            'adsense_id' => $settings['adsense_id'],
            'adsense_unit' => $settings['adsense_unit'],
            'adsense_enabled' => $settings['adsense_enabled'],
        ];
        return $google;
    };
};
