<?php

namespace CrazyInventor;

/**
 * Class Mailtrapper
 * @package CrazyInventor
 */
class Mailtrapper
{
    /**
     * Curl constant prefix
     * @var stirng
     */
    const CURL_PRE = 'CURLOPT_';

    /**
     * Token to authorize access
     * @var string
     */
    protected $token;

    /**
     * URL of the Mailtrap API
     * @var string
     */
    protected $url = 'https://mailtrap.io/api/v1/';

    /**
     * Desired format of response, accepted values are 'json' and 'xml'
     * @var string
     */
    protected $format = '';

    /**
     * Request settings to be passed along with curl call. use like  [CURL_FLAG => 'value'] without the 'CURLOPT_' prefix
     * @var array
     */
    protected $requestSettings = [];

    /**
     * Mailtrapper constructor
     * @param $api_token
     * @param bool $format
     */
    public function __construct($api_token, $format = false, array $requestSettings = [])
    {
        $this->token = $api_token;
        $this->setRequestSettings($requestSettings);
        if ($format && in_array($format, ['json', 'xml'])) {
            $this->setFormat('.' . $format);
        }
    }

    /**
     * Set format
     * @param null $format
     */
    protected function setFormat($format = null)
    {
        $this->format = '.' . $format;
    }

    /**
     * Get format
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set requestSettings
     * @param array $requestSettings
     */
    protected function setRequestSettings(array $requestSettings = [])
    {
        $this->requestSettings = $requestSettings;
    }

    /**
     * get requestSettings
     * @return array
     */
    public function getRequestSettings()
    {
        return $this->requestSettings;
    }

    /**
     * get url
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get list of inboxes
     * @return string
     */
    public function getInboxes()
    {
        $url = $this->buildUrl('inboxes');
        return $this->process($url);
    }

    /**
     * Get mails by inbox ID
     * @param mixed $inbox_id
     * @return string
     */
    public function getMails($inbox_id)
    {
        $path = 'inboxes/' . $inbox_id . '/messages';
        $url = $this->buildUrl($path);
        return $this->process($url);
    }

    /**
     * Delete all mails by inbox ID
     * @param mixed $inbox_id
     * @return string
     */
    public function clearInbox($inbox_id)
    {
        $path = 'inboxes/' . $inbox_id . '/clean';
        $url = $this->buildUrl($path);
        $context = ['CUSTOMREQUEST' => "PATCH"];

        return $this->process($url, $context);
    }

    /**
     * Delete mail by inbox ID and mail ID
     * @param mixed $inbox_id
     * @param mixed $message_id
     * @return string
     */
    public function deleteMessage($inbox_id, $message_id)
    {
        $path = 'inboxes/' . $inbox_id . '/messages/' . $message_id;
        $url = $this->buildUrl($path);
        $context = ['CUSTOMREQUEST' => "DELETE"];
        return $this->process($url, $context);
    }

    /**
     * Build an URL to send request to
     * @param string $path
     * @return string
     */
    protected function buildUrl($path)
    {
        return $this->getUrl() . $path . $this->getFormat();
    }

    /**
     * Send request to Mailtrap and return response
     * @param string $url
     * @param array $context
     * @return string|bool
     */
    protected function process($url, array $context = [])
    {
        // make basic context if non was provided
        if (empty($context)) {
            $context = [
                'CUSTOMREQUEST' => "GET"
            ];
        }
        // set up headers, url and return transfer
        $context['HTTPHEADER'] = $this->buildHttpHeaders($context);
        $context['URL'] = $url;
        $context['RETURNTRANSFER'] = true;

        // start call
        $ch = curl_init();

        $data_conf = [];
        foreach (array_merge($this->getRequestSettings(), $context) as $key => $value) {
            $name = constant(self::CURL_PRE . strtoupper($key));
            $val = $value;
            $data_conf[$name] = $val;
        }
        curl_setopt_array($ch, $data_conf);

        // get result and close
        $return = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $returnCode === 200 ? $return : false;
    }

    /**
     * Build API token header
     * @return array
     */
    protected function apiTokenHeader()
    {
        return ['Api-Token: ' . $this->token];
    }

    /**
     * Build http headers
     * @return array
     */
    protected function buildHttpHeaders(array $context = [])
    {
        $headers = array_merge(
            array_key_exists('HTTPHEADER',
                $this->getRequestSettings()) ? $this->getRequestSettings()['HTTPHEADER'] : [],
            array_key_exists('HTTPHEADER', $context) ? $context['HTTPHEADER'] : [],
            $this->apiTokenHeader()
        );
        return $headers;
    }
}
