<?php

namespace Controller;

use \Exception;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Model\ShortURLModel;

/**
 * 短縮URL コントローラ.
 */
class ShortURLController extends Controller
{

    public function index(Request $request, Response $response, array $args)
    {
        try {
            $this->db->beginTransaction();

            if (
                !array_key_exists('key', $args)
                || !ctype_alnum($args['key']) || strlen($args['key']) !== 6
            ) {
                throw new HttpNotFoundException($request);
            }

            $shortURL = new ShortURLModel($this->db, $this->logger, $this->i18n);
            $url = $shortURL->getUrlByKey($args['key']);

            if ($url === null) throw new HttpNotFoundException($request);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $url;
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    public function post(Request $request, Response $response)
    {
        $data = [];
        $postParam = $request->getParsedBody();
        $url = array_key_exists('url', $postParam) ? $postParam['url'] : '';
        if (
            !is_string($url) || strlen($url) === 0
            || !filter_var('http://' . $_SERVER['HTTP_HOST'] . $url, FILTER_VALIDATE_URL)
        ) {
            $data['error'] = ['message' => 'ERROR! invalid parameter [url]'];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        }

        try {
            $this->db->beginTransaction();

            $shortURL = new ShortURLModel($this->db, $this->logger, $this->i18n);
            $data['result'] = ['urlKey' => $shortURL->createShortUrl($url)];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
