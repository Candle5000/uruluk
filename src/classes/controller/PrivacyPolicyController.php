<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * プライバシーポリシー コントローラ.
 */
class PrivacyPolicyController extends Controller
{

    const PAGE_ID = 1;

    public function index(Request $request, Response $response)
    {
        $this->title = 'プライバシーポリシー';

        try {
            $this->db->beginTransaction();

            $args = [
                'header' => $this->getHeaderInfo(),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'privacy/index.phtml', $args);
    }
}
