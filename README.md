# webman短信宝插件
基于webman开发的短信宝插件smsbao.com
# 说明
## 开发说明
按照webman基础插件开发规范开发，包括短信模板管理、短信账号配置、短信测试、余额查询功能；
## 支持类型
支持短信宝国内、国际、语音短信发送；支持ssl配置
## 使用方法：
`
use plugin\smsbao\api\Smsbao;
$sms = new Smsbao();
$result = $sms->sendByTemplate($phone,'captcha',$type,['code'=>$code]);
`
### sendByTemplate参数说明
1、四个参数分别是：手机号、模板名、短信类型、参数
2、国内手机号无需区号，国际手机号格式+866999999999；
3、模板名：后台配置的模板名称，只能是字母；
4、短信类型：1国内、2国际、3语音
5、短信内容里的参数，数组格式


![Image](https://github.com/users/yucheng0610/projects/2/assets/25238810/df6eb4e4-5b1e-4358-abbc-ac6cf1c1429e)



![Image](https://github.com/users/yucheng0610/projects/2/assets/25238810/9c058a9c-fef8-41a4-968a-a211d9c43f16)



![Image](https://github.com/users/yucheng0610/projects/2/assets/25238810/ba2cb848-2f1d-4f06-bf64-adbc6ef3d90c)



![Image](https://github.com/users/yucheng0610/projects/2/assets/25238810/28b20afc-7ddc-4cfc-96cd-66383624953f)
