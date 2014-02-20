<?php

/**
 * Class CurlMultiQueue
 * @author Daniel Boorn Rapid Digital LLC
 * @contact daniel.boorn@gmail.com (info@rapiddigitalllc.com)
 * @license Apache 2.0 Jan 2004
 */
class CurlMultiQueue {

    private $queue;
    private $current;
    private $active;
    private $mh;
    private $startTime;
    private $endTime;
    private $pipeAmountChange;
    private $pipeOpened;

    public $maxPipes;

    /**
     * @param array $url
     * @param $maxPipes
     * @param null $pipeAmountChange
     * @param null $pipeOpened
     */
    public function __construct(array $url, $maxPipes, $pipeAmountChange = null, $pipeOpened = null) {
        $this->queue = $url;
        $this->maxPipes = $maxPipes;
        $this->total = count($url);
        $this->mh = curl_multi_init();
        $this->pipeAmountChange = $pipeAmountChange;
        $this->pipeOpened = $pipeOpened;
    }

    /**
     * @param array $url
     * @param int $maxPipes
     * @param null $pipeAmountChange
     * @param null $pipeOpened
     * @return CurlMultiQueue
     */
    public static function forge(array $url, $maxPipes = 10, $pipeAmountChange = null, $pipeOpened = null) {
        return new self($url, $maxPipes, $pipeAmountChange, $pipeOpened);
    }

    /**
     *
     */
    public function get_one() {
        $url = array_shift($this->queue);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_multi_add_handle($this->mh, $ch);
        $this->current[] = array(
            'url' => $url,
            'ch'  => $ch,
        );
        if ($this->pipeOpened) {
            call_user_func($this->pipeOpened, count($this->current) - 1, $url, $ch);
        }
    }

    /**
     * @return $this
     */
    public function execute() {
        $this->current = array();
        $this->startTime = microtime(true);

        if (count($this->queue) < $this->maxPipes) {
            $this->maxPipes = count($this->queue);
        }
        $this->active = $this->maxPipes;

        for ($i = 0; $i < $this->maxPipes; $i++) {
            $this->get_one();
        }

        if ($this->pipeAmountChange) {
            call_user_func($this->pipeAmountChange, $this->active, $this->maxPipes);
        }

        do {
            if (count($this->queue) && $this->active < $this->maxPipes) {
                if ($this->pipeAmountChange) {
                    call_user_func($this->pipeAmountChange, ($this->active + 1), $this->maxPipes);
                }
                for ($i = 0; $i <= ($this->maxPipes - $this->active); $i++) {
                    $this->get_one();
                }
            }
            curl_multi_exec($this->mh, $this->active);
            curl_multi_select($this->mh);
        } while ($this->active > 0);

        $this->endTime = microtime(true);

        return $this;

    }

    /**
     * @return mixed
     */
    public function getTotalTime() {
        return $this->endTime - $this->startTime;
    }

    /**
     * @param $index
     * @return string
     */
    public function getResponse($index) {
        return curl_multi_getcontent($this->current[$index]['ch']);
    }

    /**
     *
     */
    public function dumpResponses() {
        foreach ($this->current as $item) {
            var_dump($item['url'], curl_multi_getcontent($item['ch']));
        }
    }


}