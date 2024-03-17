<?php

namespace Model;

use \PDO;

class NewsModel extends Model
{

    const LATEST_NEWS_COUNT = 4;
    const PAGE_NEWS_COUNT = 10;

    public function getLatestNews()
    {
        return $this->select(0, NewsModel::LATEST_NEWS_COUNT);
    }

    public function getNews(int $page)
    {
        return $this->select($page * NewsModel::PAGE_NEWS_COUNT, NewsModel::PAGE_NEWS_COUNT);
    }

    private function select(int $offset, int $rowCount)
    {
        $sql = <<<SQL
            SELECT
                SQL_CALC_FOUND_ROWS
                post_date
                , subject
                , subject_en
                , content
                , content_en
            FROM
                news
            ORDER BY
                post_date DESC
            LIMIT
                :offset, :rowCount
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':rowCount', $rowCount, PDO::PARAM_INT);
        $stmt->execute();
        $newsList = [];
        while ($result = $stmt->fetch()) {
            switch ($this->i18n->getLangCode()) {
                case 'en':
                    $subject = empty($result['subject_en']) ? $result['subject'] : $result['subject_en'];
                    $content = empty($result['content_en']) ? $result['content'] : $result['content_en'];
                    break;
                default:
                    $subject = $result['subject'];
                    $content = $result['content'];
            }
            $newsList[] = [
                'post_date' => $result['post_date'],
                'subject' => $subject,
                'content' => $content
            ];
        }
        $sql = <<<SQL
            SELECT FOUND_ROWS() all_count
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $maxPage = ceil($stmt->fetch()['all_count'] / NewsModel::PAGE_NEWS_COUNT) - 1;
        return ['list' => $newsList, 'max_page' => $maxPage];
    }
}
