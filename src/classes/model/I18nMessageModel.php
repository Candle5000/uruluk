<?php

namespace Model;

use \PDO;

class I18nMessageModel extends Model
{

    public function getMessagesByLanguageCode(string $languageCode)
    {
        $sql = <<<SQL
            SELECT
                category
                , message_key
                , message
            FROM
                i18n_message
            WHERE language_code = :languageCode
            ORDER BY
                category
                , message_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':languageCode', $languageCode, PDO::PARAM_STR);
        $stmt->execute();
        $messages = [];
        while ($result = $stmt->fetch()) {
            $messages[$result['category'] . '.' . $result['message_key']] = $result['message'];
        }
        return $messages;
    }
}
