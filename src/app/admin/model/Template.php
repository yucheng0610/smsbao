<?php

namespace plugin\smsbao\app\admin\model;


use plugin\admin\app\model\Option;
use plugin\smsbao\api\Smsbao;

/**
 * 短信模版相关
 */
class Template
{
    /**
     * 获取模版
     * @param $templateName
     * @return mixed|null
     */
    public static function get($templateName)
    {
        $value = Option::where('name', static::templateNameToOptionName($templateName))->value('value');
        return $value ? json_decode($value, true) : null;
    }

    /**
     * 保存模版
     * @param $templateName
     * @param $from
     * @param $subject
     * @param $content
     * @return void
     */
    public static function save($templateName, $subject, $content)
    {
        $data = ['subject' => $subject, 'content' => $content];
        $optionName = static::templateNameToOptionName($templateName);
        if (!$option = Option::where('name', $optionName)->first()) {
            $option = new Option;
        }
        $option->name = $optionName;
        $option->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $option->save();
    }

    /**
     * 删除模版
     * @param array $templateNames
     * @return void
     */
    public static function delete(array $templateNames)
    {
        foreach ($templateNames as $index => $templateName) {
            $templateNames[$index] = static::templateNameToOptionName($templateName);
        }
        Option::whereIn('name', $templateNames)->delete();
    }

    /**
     * 模版名到option名转换
     * @param string $templateName
     * @return string
     */
    public static function templateNameToOptionName(string $templateName): string
    {
        return Smsbao::TEMPLATE_OPTION_PREFIX . $templateName;
    }

    /**
     * option名到模版名转换
     * @param string $optionName
     * @return string
     */
    public static function optionNameToTemplateName(string $optionName): string
    {
        return substr($optionName, strlen(Smsbao::TEMPLATE_OPTION_PREFIX));
    }

}
