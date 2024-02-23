<?php

namespace plugin\smsbao\api;

use plugin\admin\app\model\Option;
use support\exception\BusinessException;
use support\Log;

/**
 * Smsbao
 */
class Smsbao
{
    /**
     * Option 表的name字段值
     */
    const OPTION_NAME = 'smsbao_setting';
    /**
     * Option表模版前缀
     */
    const TEMPLATE_OPTION_PREFIX = 'smsbao_template_';

    private $smsBaoUsername;
    private $smsBaoPassword;
    private $errorMessage;
    private $curlError;
    private $isSSL = true;
    private $smsName;

    /**
     * Smsbao constructor.
     * @param string $smsBaoUsername 短信宝平台用户名
     * @param string $smsBaoPassword 短信宝平台密码
     */
    public function __construct()
    {
        $config = self::getConfig();
        if($config['smtpSecure']) $this->setIsSSL();
        $this->smsBaoUsername = $config['username'];
        $this->smsName = $config['smsname'];
        $this->smsBaoPassword = md5($config['password']);
    }
    /**
     * 发送短信
     * @param string $mobile 手机号码
     * @param string $content 短信内容
     * @param int $type 可选值 1大陸|2國際|3语音 默认为2 國際短信
     * @return bool 是否发送成功
     */
    public function send($mobile, $content, $type = 2)
    {
        $type = (mb_strpos($mobile, "+") !== false) && ($type === 1) ? 2 : $type;
        $content = '【'.$this->smsName.'】'.$content; 
        $url = $this->apiUrl($type) . "?u=" . $this->smsBaoUsername . "&p=" . $this->smsBaoPassword . "&m=" . urlencode($mobile) . "&c=" . urlencode($content);
        $httpCurlGet = file_get_contents($url);
        if ($httpCurlGet === 0 || empty($httpCurlGet)) {
            return true;
        } else {
            $this->errorMessage = empty($this->curlError) ? $this->errorNoToMessage($httpCurlGet) : $this->curlError;
            return false;
        }
    }
    /**
     * 按照模版发送
     * @param string|array $mobile
     * @param $templateName
     * @param $type 1国内|2国外|3语音 默认为1 国内短信
     * @param array $templateData
     * @return void
     * @throws BusinessException
     * @throws Exception
     */
    public  function sendByTemplate($mobile, $templateName,$type=1, array $templateData = [])
    {
        $smsbaoTemplate = Option::where('name', "smsbao_template_$templateName")->value('value');
        $smsbaoTemplate = $smsbaoTemplate ? json_decode($smsbaoTemplate, true) : null;
        if (!$smsbaoTemplate) {
            throw new BusinessException('模版不存在');
        }
        $content = $smsbaoTemplate['content'];
        if ($templateData) {
            $search = [];
            foreach ($templateData as $key => $value) {
                $search[] = '{' . $key . '}';
            }
            $content = str_replace($search, array_values($templateData), $content);
        }
        return $this->send($mobile, $content,$type);
    }
    /**
     * 查询短信平台账户余额
     * @return array|mixed
     */
    public function query()
    {
        $url = $this->apiUrl(4) . "?u=" . $this->smsBaoUsername . "&p=" . $this->smsBaoPassword;
        $httpCurlGet = file_get_contents($url);
        Log::info($httpCurlGet);
        $response = explode("\n", $httpCurlGet);
        if ($response[0] == "0") {
            $info = explode(",", $response[1]);
            return "發送條數：" . $info[0] . "條，剩餘條數：" . $info[1] . "條";
        } else {
            $this->errorMessage = empty($this->curlError) ? $this->errorNoToMessage($response[0]) : $this->curlError;
            return $this->errorMessage;
        }
    }
    /**
     * 设置是否使用SSL协议安全接口
     * @param bool $isSSL true使用SSL协议
     */
    public function setIsSSL($isSSL = true)
    {
        $this->isSSL = $isSSL;
    }
    /**
     * 设置接口
     * @param $type
     * @return string
     */
    private function apiUrl($type)
    {
        $protocol = $this->isSSL ? "https://" : "http://";
        switch ($type) {
            case 1 :
                return $protocol . "api.smsbao.com/sms";//大陆
            case 2 :
                return $protocol . "api.smsbao.com/wsms";//国际
            case 3 :
                return $protocol . "api.smsbao.com/voice";//语音
            case 4 :
                return $protocol . "api.smsbao.com/query";//查询
            default :
                die("介面設定錯誤，請檢查初始化參數");
        }
    }
    /**
     * 获取最近一次发生错误的错误消息
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    /**
     * 将消息编号转换成消息
     * @param $errorNo
     * @return mixed|string
     */
    private function errorNoToMessage($errorNo)
    {
        if (isset($this->errorNo()[$errorNo])) {
            return $this->errorNo()[$errorNo];
        } else {
            return "未知錯誤！";
        }
    }

    /**
     * 错误编号对应的错误信息列表
     * @return array
     */
    private function errorNo()
    {
        return [
            "-1"=>"介面參數有誤！",
            "30"=>"短信平臺的帳號或密碼錯誤！",
            "40"=>"短信平臺的帳號不存在！",
            "41"=>"短信平臺的簡訊餘額不足！",
            "43"=>"服務器IP地址已被限制！",
            "50"=>"短信內容含有敏感詞！",
            "51"=>"手機號碼不正確！"
        ];
    }

    /**
     * 获取配置
     * @return array
     */
    public static function getConfig(): array
    {
        $optionName = static::OPTION_NAME;
        $option = Option::where('name', $optionName)->value('value');
        $config = $option ? json_decode($option, true) : [];
        if (!$config) {
            $config = [
                'smsname' => '',
                'username' => '',
                'password' => '',
                'smtpSecure' => '',
            ];
            $option = $option ?: new Option();
            $option->name = $optionName;
            $option->value = json_encode($config, JSON_UNESCAPED_UNICODE);
        }
        return $config;
    }

}