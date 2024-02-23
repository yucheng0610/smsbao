<?php

namespace plugin\smsbao\app\admin\controller;

use plugin\admin\app\model\Option;
use plugin\smsbao\api\Smsbao;
use plugin\smsbao\app\admin\model\Tag;
use plugin\smsbao\app\admin\model\Template;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;
use function view;

/**
 * 短信设置
 */
class SettingController
{

    /**
     * 短信设置页
     * @return Response
     */
    public function index()
    {
        return view('setting/index');
    }

    /**
     * 获取设置
     * @return Response
     */
    public function get(): Response
    {
        $config = Smsbao::getConfig();
        $config['amount'] = '';
        if($config['username'] && $config['password']){
            $config['amount'] = (new Smsbao())->query();
        }
        return json(['code' => 0, 'msg' => 'ok', 'data' => $config]);
    }

    /**
     * 更改设置
     * @param Request $request
     * @return Response
     */
    public function save(Request $request): Response
    {
        $data =  $request->post();     
        $name = Smsbao::OPTION_NAME;
        $value = json_encode($data);
        $option = Option::where('name', $name)->first();
        if ($option) {
            Option::where('name', $name)->update(['value' => $value]);
        } else {
            $option = new Option();
            $option->name = $name;
            $option->value = $value;
            $option->save();
        }
        return json(['code' => 0, 'msg' => 'ok']);
    }
    /**
     * 获取模版
     * @param Request $request
     * @return Response
     */
    public function selectTemplate(Request $request): Response
    {
        $name = $request->get('name', '');
        $prefix = Smsbao::TEMPLATE_OPTION_PREFIX;
        if ($name && is_string($name)) {
            $items = Option::where('name', 'like', "{$prefix}$name%")->get()->toArray();
        } else {
            $items = Option::where('name', 'like', "$prefix%")->get()->toArray();
        }
        foreach ($items as &$item) {
            $item['name'] = Template::optionNameToTemplateName($item['name']);
            [$item['subject'], $item['content']] = array_values(json_decode($item['value'], true));
        }
        return json(['code' => 0, 'msg' => 'ok', 'data' => $items]);
    }

    /**
     * 短信测试
     * @param Request $request
     * @return Response
     * @throws Exception|BusinessException
     */
    public function test(Request $request): Response
    {
        $mobile = $request->post('mobile');
        (new Smsbao())->sendByTemplate($mobile,'captcha',1, ['code'=>rand(1000,9999)]);
        return json(['code' => 0, 'msg' => 'ok']);
    }

    /**
     * 短信模版测试
     * @param Request $request
     * @return Response
     * @throws Exception|BusinessException
     */
    public function testTemplate(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('template/test');
        }
        $name = $request->post('name');
        $mobile = $request->post('mobile');
        $data = $request->post('data');
        $data = $data ? json_decode($data, true) : [];
        (new Smsbao())->sendByTemplate($mobile, $name,1, $data);
        return json(['code' => 0, 'msg' => 'ok']);
    }
    /**
     * 插入
     * @param Request $request
     * @return Response
     */
    public function insertTemplate(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $name = $request->post('name');
            if (Template::get($name)) {
                return json(['code' => 1, 'msg' => '模版已经存在']);
            }
            $subject = $request->post('subject');
            $content = $request->post('content');
            Template::save($name, $subject, $content);
        }
        return view('template/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     */
    public function updateTemplate(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $name = $request->post('name');
            $newName = $request->post('new_name');
            if (!Template::get($name)) {
                return json(['code' => 1, 'msg' => '模版不存在']);
            }
            if ($name != $newName) {
                Template::delete([$name]);
            }
            $from = $request->post('from');
            $subject = $request->post('subject');
            $content = $request->post('content');
            Template::save($newName, $from, $subject, $content);
            return json(['code' => 0, 'msg' => 'ok']);
        }
        return view('template/update');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function deleteTemplate(Request $request): Response
    {
        $names = (array)$request->post('name');
        Template::delete($names);
        return json(['code' => 0, 'msg' => 'ok']);
    }

}
