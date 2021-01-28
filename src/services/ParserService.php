<?php

class ParserService {

    protected $views           = 0;
    protected $statusCodes     = [];
    protected $traffic         = 0;
    protected $urls            = [];
    protected $crawlers        = [
        'Google' => 0,
        'Bing'   => 0,
        'Baidu'  => 0,
        'Yandex' => 0,
    ];

    /**
     * @param string $rawData
     * @return false|string
     */
    public function parseAccessLog(): string {
        $accessData  = fopen(CONFIG_DIR . '/../logs/access.log', "r+");
        while ($row  = stream_get_line($accessData, 1024 * 1024, PHP_EOL))
        {
            $this->_getParsedData($row);
            $this->views++;
        }
        fclose($accessData);

        $result     = [
            'views'       => $this->views,
            'urls'        => count($this->urls),
            'traffic'     => $this->traffic,
            'crawlers'    => $this->crawlers,
            'statusCodes' => $this->statusCodes,
        ];
        $output = json_encode($result, JSON_PRETTY_PRINT);
        return $output;
    }

    /**
     * Для разделения строки на фрагменты я решила использовать explode основываясь на том,
     * что access.log, как правило, имеет одинаковый формат
     *
     * @param array $rows
     */
    protected function _getParsedData(string $row): void {
        $rowChunks          = explode('"', $row);
        $statusCodeNTraffic = trim($rowChunks[2]);
        $this->_isUrlUnique($rowChunks[1]);
        $this->_getStatusCodeAndTraffic($statusCodeNTraffic);
        $this->_getCrawler($rowChunks[5]);
    }

    /**
     * @param $row
     */
    protected function _isUrlUnique(string $url): void {
        if (!in_array($url, $this->urls, true)) {
            $this->urls[] = $url;
        }
    }

    /**
     * Объединила получение этих параметров, т.к они оба расположены в одном фрагменте строки
     *
     * @param string $row
     */
    protected function _getStatusCodeAndTraffic(string $row): void {
        $rawData    = explode(' ', $row);
        $statusCode = count($rawData) > 0 ? (int) $rawData[0] : null;

        if (!is_null($statusCode)) {
            $this->_isStatusCodeARedirect($statusCode) ? $this->traffic += 0 : $this->traffic += (int) $rawData[1];
        }

        if (array_key_exists($statusCode, $this->statusCodes) && $statusCode !== null) {
            $this->statusCodes[$statusCode]++;
        } else {
            $this->statusCodes[$statusCode] = 1;
        }
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    protected function _isStatusCodeARedirect(int $statusCode): bool {
        if ($statusCode === 301) {
            return true;
        }
        return false;
    }

    /**
     * Т.к в задании были перечислены конкретно эти четыре кроулера, решила выполнить поиск по соответствию названия
     *
     * @param $row
     */
    protected function _getCrawler(string $row): void {
        try {
            $crawler = $this->_findByRegExp('/(Google)|(Bing)|(Baidu)|(Yandex)/', $row, 'Crawler');
        } catch (Exception $exception) {
            $crawler = null;
        }

        if (!is_null($crawler)) {
            $this->crawlers[$crawler]++;
        }
    }

    /**
     * Не знаю как в таком случае указывать, что возвращает функция (: mixed) (?)
     *
     * @param $pattern
     * @param $row
     * @param $type
     * @return mixed
     * @throws Exception
     */
    protected function _findByRegExp(string $pattern, string $row, string $type) {
        $result = preg_match($pattern, $row, $matches);
        if ($result) {
            return $matches[0];
        } else {
            throw new Exception("{$type} not found in {$row}");
        }
    }

}

