<?php

namespace Metafrastis\YandexTranslate;

class YandexTranslate {

    protected $sid;
    protected $queue = [];
    protected $response;
    protected $responses;

    public function translate($args = [], $opts = []) {
        $args['from'] = isset($args['from']) ? $args['from'] : null;
        $args['to'] = isset($args['to']) ? $args['to'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        $args['sid'] = isset($args['sid']) ? $args['sid'] : $this->sid([], $opts);
        if (!$args['from']) {
            return false;
        }
        if (!$args['to']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        if (!$args['sid']) {
            return false;
        }
        $url = sprintf('https://translate.yandex.net/api/v1/tr.json/translate?id=%s-0-0&srv=tr-text&lang=%s-%s&reason=paste&format=text', rawurlencode($args['sid']), rawurlencode($args['from']), rawurlencode($args['to']));
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: https://translate.yandex.com',
            'Referer: https://translate.yandex.com/',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0',
        ];
        $params = ['text' => $args['text'], 'options' => '4'];
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang']) || !isset($json['text'])) {
            return false;
        }
        if ($args['sid']) {
            $this->sid = $args['sid'];
        }
        return is_array($json['text']) && isset($json['text'][0]) ? $json['text'][0] : $json['text'];
    }

    public function detect($args = [], $opts = []) {
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        $args['sid'] = isset($args['sid']) ? $args['sid'] : $this->sid([], $opts);
        if (!$args['text']) {
            return false;
        }
        if (!$args['sid']) {
            return false;
        }
        $hint = isset($args['from'], $args['to']) ? rawurlencode($args['from'].','.$args['to']) : '';
        $url = sprintf('https://translate.yandex.net/api/v1/tr.json/detect?sid=%s&srv=tr-text&text=%s&hint=%s&options=1', rawurlencode($args['sid']), rawurlencode($args['text']), $hint);
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Origin: https://translate.yandex.com',
            'Referer: https://translate.yandex.com/',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0',
        ];
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->get($url, $headers, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang'])) {
            return false;
        }
        if ($args['sid']) {
            $this->sid = $args['sid'];
        }
        return is_array($json['lang']) && isset($json['lang'][0]) ? $json['lang'][0] : $json['lang'];
    }

    public function sid($args = [], $opts = []) {
        $args['force'] = isset($args['force']) ? $args['force'] : false;
        if ($this->sid && !$args['force']) {
            return $this->sid;
        }
        $url = 'https://translate.yandex.com/?';
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,'.'*'.'/'.'*'.';q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Referer: https://translate.yandex.com/',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0',
        ];
        $options = $opts;
        $response = $this->get($url, $headers, $options);
        if (!$queue) {
            $this->response = $response;
        }
        if (preg_match('`SID[\x00-\x20\x7f]*\:[\x00-\x20\x7f]*[\x27]([^\x27]{8}\.[^\x27]{8}\.[^\x27]{8})[\x27]`', $response['body'], $match)) {
            $sid = strrev($match[1]);
        } elseif (preg_match('`SID[\x00-\x20\x7f]*\:[\x00-\x20\x7f]*[\x27]([^\x27]{26})[\x27]`', $response['body'], $match)) {
            $sid = strrev($match[1]);
        } elseif (preg_match('`SID[\x00-\x20\x7f]*\:[\x00-\x20\x7f]*[\x27]([^\x27]+)[\x27]`', $response['body'], $match)) {
            $sid = strrev($match[1]);
        } else {
            return false;
        }
        $this->sid = $sid;
        return $sid;
    }

    public function get($url, $headers = [], $options = [], $queue = false) {
        $opts = [];
        $opts[CURLINFO_HEADER_OUT] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 5;
        $opts[CURLOPT_ENCODING] = '';
        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 10;
        $opts[CURLOPT_URL] = $url;
        foreach ($opts as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
        if ($queue) {
            $this->queue[] = ['options' => $options];
            return;
        }
        $follow = false;
        if ($options[CURLOPT_FOLLOWLOCATION]) {
            $follow = true;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        $errors = 2;
        $redirects = isset($options[CURLOPT_MAXREDIRS]) ? $options[CURLOPT_MAXREDIRS] : 5;
        while (true) {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            if ($error || $errno) {
                if ($errors > 0) {
                    $errors--;
                    continue;
                }
            } elseif ($info['redirect_url'] && $follow) {
                if ($redirects > 0) {
                    $redirects--;
                    $options[CURLOPT_URL] = $info['redirect_url'];
                    continue;
                }
            }
            break;
        }
        return $response;
    }

    public function post($url, $headers = [], $params = [], $options = [], $queue = false) {
        $opts = [];
        $opts[CURLINFO_HEADER_OUT] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 5;
        $opts[CURLOPT_ENCODING] = '';
        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 10;
        $opts[CURLOPT_URL] = $url;
        foreach ($opts as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
        if ($queue) {
            $this->queue[] = ['options' => $options];
            return;
        }
        $follow = false;
        if ($options[CURLOPT_FOLLOWLOCATION]) {
            $follow = true;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        $errors = 2;
        $redirects = isset($options[CURLOPT_MAXREDIRS]) ? $options[CURLOPT_MAXREDIRS] : 5;
        while (true) {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            if ($error || $errno) {
                if ($errors > 0) {
                    $errors--;
                    continue;
                }
            } elseif ($info['redirect_url'] && $follow) {
                if ($redirects > 0) {
                    $redirects--;
                    $options[CURLOPT_URL] = $info['redirect_url'];
                    continue;
                }
            }
            break;
        }
        return $response;
    }

    public function multi($args = []) {
        if (!$this->queue) {
            return [];
        }
        $mh = curl_multi_init();
        $chs = [];
        foreach ($this->queue as $key => $request) {
            $ch = curl_init();
            $chs[$key] = $ch;
            curl_setopt_array($ch, $request['options']);
            curl_multi_add_handle($mh, $ch);
        }
        $running = 1;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        $responses = [];
        foreach ($chs as $key => $ch) {
            curl_multi_remove_handle($mh, $ch);
            $body = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            $this->responses[$key] = $response;
            $options = $this->queue[$key]['options'];
            if (strpos($options[CURLOPT_URL], 'tr.json/translate') !== false) {
                $json = json_decode($body, true);
                if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang']) || !isset($json['text'])) {
                    $responses[$key] = false;
                    continue;
                }
                $responses[$key] = is_array($json['text']) && isset($json['text'][0]) ? $json['text'][0] : $json['text'];
            } elseif (strpos($options[CURLOPT_URL], 'tr.json/detect') !== false) {
                $json = json_decode($body, true);
                if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang'])) {
                    $responses[$key] = false;
                    continue;
                }
                $responses[$key] = is_array($json['lang']) && isset($json['lang'][0]) ? $json['lang'][0] : $json['lang'];
            } else {
                $responses[$key] = $body;
            }
        }
        curl_multi_close($mh);
        $this->queue = [];
        return $responses;
    }

}
