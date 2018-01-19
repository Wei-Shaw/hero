# 『直播答题』助手
>适用于Android-Win用户。声明：免费试用，答案仅供参考。

## 环境部署
- 一键启动**快速方式**（无须配置环境，**强烈推荐**）请转移至 [一键启动『直播答题』助手](https://github.com/phpxiaowei/hero/releases)，经典方式接往下阅读。
- [ADB](http://img.wm07.cn/UniversalAdbDriverSetup.msi) 驱动安装，直接下载安装即可，然后把adb.exe的目录添加至环境变量，或者把hero.php中静态变量 $adb_root 改为adb.exe的绝对路径，如我的adb.exe的路径为 D:\toos\adb\adb.exe
```php
    public static $adb_root = 'D:\toos\adb\adb.exe'; 
```
- PHP环境需要开启CURL、mbstring、gd等拓展。
- 申请百度ORC识图API [申请地址](https://ai.baidu.com/tech/ocr/general) 登录百度账号账号或者注册进去后 [创建应用](https://console.bce.baidu.com/ai/#/ai/speech/app/create)（此链接失效请[点击此处](https://console.bce.baidu.com/ai/)然后点击创建应用即可进入以下页面）

![orc_create](https://cdn.wm07.cn/orc_create.png)

- 创建成功后点击进入应用列表获取appid、app_key、secret_key。如下图

![orc_appid](https://cdn.wm07.cn/orc_appid.png)


## 使用说明
- 打开根目录下的config.json文件，将上面获取的三个值分别填入对应位置。
- 手机连接电脑并开启USB调试，选择MTP模式，在开发者选项中如果有**允许模拟点击**请勾选。（建议连接在主机后USB接口，线也能影响是否能正常连接电脑）。
- 在项目根目录 左手按住shift+右手鼠标右键(选择在此处打开命令窗口..) 键入命令 php hero.php 按照提示开始即可。

## 关于调试
- 在config.json中可调整不同平台的配置，不同分辨率的手机可能存在剪裁位置不对而导致识图不准。该配置为1920x1080的通用配置。如果不适用你的手机请加群 687141561 联系管理员。
- 会一点的PHP的同学可在config.json继续拓展其他平台的配置。
- 练习模式为获取历史的题目截图，模拟整个流程搜索答案，可根据答案进行二次开发提供答案精准度。
- 该算法很简便，通过百度然后归纳出答案在百度的内容中出现频次进行排序。仅仅提供参考，正确率在75%，经过测试，考验死记硬背型的题目是很准确。
- 欢迎star和issue

## 联系方式
- QQ群：687141561

## ADB安装的啰嗦
- 安装完成并加入了环境变量之后
- 手机连接（同上）
- 打开cmd.exe输入命令：  ``` adb devices ```
- 如果出现 类似下面信息表示连接成功
```bash
List of devices attached
9ab8ee5a       device
```
- 如果没有说明设备未正常连接，数据线与USB接口也可能导致该问题，如果5037端口被占用可能导致adb启动失败，请完全关闭手机助手等软件

## 版权说明
- 遵循GPL-3.0 协议
- 未经允许严禁用于商业用途
