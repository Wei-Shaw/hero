<?php
/**
 * 	 Search the answer
 * 	 
 *   Author 空城 <694623056@qq.com>
 *   Copyright (C) 2018 空城
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'api/load.php';
class hero
{
	// HTTP URL
	public static $url = [];
	// HTTP USER_AGENT
	public static $user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)';
	// HTTP header
	public static $header = ['Content-Type: application/json'];
	// HTTP data
	public static $data = [];
	// 图片资源句柄
	public static $png = null;
	// 手机分辨率高
	public static $height = 0;
	// 手机分辨率宽
	public static $width = 0;
	// 本程序版本号
	public static $version = 'v1.0内测';
	// 问题
	public static $q = '';
	// 选项
	public static $a = [];
	// 选项索引值
	public static $weight = [];
	// adb驱动目录
	public static $adb_root = 'adb';
	// 模式
	public static $mode = 1;
	// 配置信息
	public static $config = [];
	// 练习模式的图片地址
	public static $test = [];

	// 程序入口
	public static function start()
	{
		exec('title 百万英雄、冲顶大会答题神器');
		print("使用说明：\n");
		print("\t【1】：确保手机已成功连接电脑并开启USB调试\n");
		print("\t【2】：电脑必须连接互联网，否则无法搜索答案\n");
		print("\t【3】：手机打开直播答题页面\n");
		print("\t【4】：本程序遵循GPL开源协议，未经允许禁止商业使用\n");
		print("\t【5】：*参考答案仅跟索引值相关，不能保证100%正确，请根据题目语义作答\n");
		print("\t【6】：*索引值越高则该选项与题目相关度越高\n");
		print("\t【7】：*如果觉得不错，别忘了给作者一个star，问题请提交issues\n");
		print("\t【8】：*QQ交流群请留意Github README页面\n");
		print("\t【9】：确认无误，按Y并回车开始启动\n[y/n] ");
		@strcasecmp(trim(fgets(STDIN)), 'y') && exit;
		self::init();
		if (self::$mode === 0) {
			self::test();
		} else {
			while (true) {
				print("\n题目出现时迅速按回车键开始搜索答案，按r并回车重新启动（按Ctrl+C结束程序）\n");
				if(!strcasecmp(trim(@fgets(STDIN)), 'r')){
					self::start();
					return false;
				}
				$t1 = microtime(true);
				self::screenshot();
				self::cutImg();
				print("搜索中... \n\n");
				self::run();
				$t2 = microtime(true);
				print("\n".'用时:'.sprintf('%.2f', $t2-$t1)." s\n");
				!is_dir('test/'.self::$mode) && mkdir('test/'.self::$mode, 0777, true);
				self::$mode != 4 && copy('ask.png', 'test/'.self::$mode.'/'.uniqid().'.png');
			}
		}
	}

	// 练习模式
	private static function test()
	{
		self::$test = [];
		self::readDir('test');
		self::$test == [] && exit('练习文件夹中无截图内容');
		shuffle(self::$test);
		$count = count(self::$test);
		foreach (self::$test as $key => $file) {
			print("\n练习模式 {$key}/{$count} 按回车进入下一题，按r并回车重新启动（按Ctrl+C结束程序）\n");
			if(!strcasecmp(trim(@fgets(STDIN)), 'r')){
				self::start();
				return false;
			}
			$t1 = microtime(true);
			$fileinfo = pathinfo($file);
			if ($fileinfo['extension'] != 'png') continue;
			self::$mode = substr($fileinfo['dirname'], -1, 1);
			copy($file, 'ask.png');
			self::$png = ImageCreateFromPng('ask.png');
			self::$height = imagesy(self::$png);
			self::$width = imagesx(self::$png);
			imagedestroy(self::$png);
			self::cutImg();
			print("搜索中... \n\n");
			print("\n该题来源于:".self::$config[self::$mode]['platform_name']."\n");
			self::run();
			$t2 = microtime(true);
			print("\n".'用时:'.sprintf('%.2f', $t2-$t1)." s\n");
		}
		print("\n练习模式已结束，按r并回车重新启动（按Ctrl+C结束程序）\n");
		if(!strcasecmp(trim(@fgets(STDIN)), 'r')){
			self::start();
			return false;
		} else {
			exit;
		}

	}

	// 读取test文件夹下的所有文件
	private static function readDir($dir)
	{
		if (!is_dir($dir)) return [];
		$path = opendir($dir);
		while ($file = readdir($path)) {
			if (is_dir($dir.'/'.$file) && $file != '.' && $file != '..') {
				self::readDir($dir.'/'.$file);
			} else {
				$file != '.' && $file != '..' && self::$test[] = $dir.'/'.$file;
			}
		}
		closedir($path);
		unset($file);
	}

	// 运行核心
	private static function run()
	{
		$text = self::textApi();
		isset($text['error_code']) && exit('百度识图API错误信息：'.$text['error_msg']);
		self::$q = '';
		self::$a = [];
		$sign = 0;
		foreach ($text['words_result'] as $key => $value) {
			if ($value['words'] == 'SIGN') {
				$sign++;
				continue;
			} elseif ($sign < 1) {
				self::$q .=  $value['words'];
			} else {
				self::$a[$sign] = $value['words'];
				$sign++;
			}
		}

		self::$q = substr(self::$q, 2);

		self::$url[1] = 'https://www.baidu.com/s?wd='.urlencode(self::$q.implode(' ', self::$a));
		self::$url[10] = 'https://www.baidu.com/s?wd='.urlencode(self::$q);
		self::search();
		$contrary = false;
		// 按语义情况排序
		$list = ['不属于','不是','不在','不能','没有','不对','未在'];
		foreach ($list as $value) {
			if (strpos(self::$q, $value) !== false) {
				$contrary = true;
				break;
			}
		}
		if ($contrary) {
			asort(self::$weight);
		} else {
			arsort(self::$weight);
		}
		print("\nQ:".self::$q."\n");
		$option = false;
		foreach (self::$weight as $key => $value) {
			if (self::$a[$key] == '') {
				continue;
			} elseif ($option === false) {
				print("\n".'参考答案:'.self::$a[$key]."\n");
				print("\n".'索引:'."\n");
				$option = true;
			}
			print(self::$a[$key].':'.$value."\n");
		}
		unset($text, $option, $sign, $list, $contrary);
	}

	// 按相关度进行解析
	public static function search()
	{
		self::$weight = [];
		$response = self::request();
		foreach ($response as $coefficient => $search) {
			$regex ="/<div id=\"content_left\".*?>.*?clear:both;height:0;/ism";
			preg_match($regex, $search, $matches);
			$search = strip_tags($matches[0]);

			$analysis = new PhpAnalysis('utf-8', 'utf-8');
			$analysis->LoadDict();
			foreach (self::$a as $key => $value) {
				$word = self::replace($value);
				if (mb_strlen($word, 'utf-8') <= 4) {
					@self::$weight[$key] += substr_count($search, $word) * $coefficient;
				} else {
					$analysis->SetSource($word);
					$analysis->StartAnalysis(false);
					$result = $analysis->GetFinallyResult(' ');
					$result = explode(' ', $result);
					foreach ($result as $k => $v) {
						@self::$weight[$key] += substr_count($search, $v);
					}

				}
			}
		}
		unset($search, $regex, $analysis, $result, $matches, $response, $coefficient);
	}

	// 剔除标点符号
	private static function replace($subject)
	{
		$words = [',','，','”','“','。','、',' ','《','》','【','】','！','‘','’','；','：'];
		return str_replace($words, '', $subject);
	}

	// 切割问题主区域
	public static function cutImg()
	{
		self::$png = ImageCreateFrompng('ask.png');
		$config = self::$config[self::$mode];
		$height = self::$height - (self::$height * $config['cut_top']) - (self::$height * $config['cut_bottom']);
		$width = self::$width - self::$width * $config['cut_edge'] * 2;
		$newImg = imagecreatetruecolor($width, $height);
		imagecopy($newImg, self::$png, 0, 0, self::$width * $config['cut_edge'], self::$height * $config['cut_top'], $width, $height);
		// 分隔问题与答案选项
		$color = imagecolorallocate($newImg, 0, 0, 0);
		$answerStart = $height * $config['answer_start'];
		imagettftext($newImg, 28, 0, 50, $answerStart, $color, 'lib/font/calibri.ttf', 'SIGN');

		imagejpeg($newImg, 'ask_cut.jpeg');
		imagedestroy(self::$png);
		imagedestroy($newImg);
		unset($height, $width, $config);
	}

	// 百度OCR api请求
	public static function textApi()
	{
		$aipOcr = new AipOcr(self::$config['appid'], self::$config['api_key'], self::$config['secret_key']);
		return $aipOcr->general(file_get_contents('ask_cut.jpeg'));
		unset($aipOcr);
	}

	// HTTP 并行请求
	private static function request()
	{
		$mh = curl_multi_init();
		$active = null;
		$ch = [];
		foreach (self::$url as $key => $value) {
			$ch[$key] = curl_init();
			curl_setopt($ch[$key], CURLOPT_URL, $value);
		    curl_setopt($ch[$key], CURLOPT_TIMEOUT, 60);
		    curl_setopt($ch[$key], CURLOPT_AUTOREFERER, true);
		    curl_setopt($ch[$key], CURLOPT_COOKIESESSION, true);
		    curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch[$key], CURLOPT_SSL_VERIFYHOST, 2);
		    curl_setopt($ch[$key], CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch[$key], CURLOPT_HTTPHEADER, self::$header);
		    curl_setopt($ch[$key], CURLOPT_USERAGENT, self::$user_agent);
			if (!empty(self::$data[$key])) {
				curl_setopt($ch[$key], CURLOPT_POST, 1);
				curl_setopt($ch[$key], CURLOPT_POSTFIELDS, self::$data[$key]);
			}
			curl_multi_add_handle($mh, $ch[$key]);
		}
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($active);

		foreach (self::$url as $key => $value) {
			$response[$key] = curl_multi_getcontent($ch[$key]);
			curl_multi_remove_handle($mh, $ch[$key]);
			curl_close($ch[$key]);
		}
		curl_multi_close($mh);

		unset($mrc, $active);
		return $response;
	}

	// 初始化环境
	private static function init()
	{
		try {
			
			print("\n版本号:" . self::$version."\n");
			print("初始化环境中..." . "\n");
			date_default_timezone_set('PRC');
			if ('cli' !== PHP_SAPI) throw new Exception('请在CLI模式下运行');
			@unlink('ask.png');
			if (file_exists('config.json')) {
				print("\n加载配置文件:config.json\n");
			} else {
				throw new Exception('需要一个配置文件!');
			}
			$config = json_decode(file_get_contents('config.json'), true);
			if (empty($config)){
				throw new Exception('配置文件格式错误');
			} elseif ($config['appid'] == '') {
				throw new Exception('配置文件appid为空');
			} elseif($config['api_key'] == '') {
				throw new Exception('配置文件api_key为空!');
			} elseif($config['secret_key'] == '') {
				throw new Exception('配置文件secret_key为空!');
			}
			self::$config = &$config;
			$sequence = '0/';
			print("请选择答题平台\n");
			print("\t【0】：练习模式\n");
			foreach (self::$config as $key => $value) {
				if (is_array($value)) {
					print("\t【{$key}】：{$value['platform_name']}\n");
					$sequence .= $key.'/';
				}
			}
			print("请输入序号并回车\n[".trim($sequence, '/')."]");
			@$mode = fgets(STDIN);
			self::$mode = (int)$mode ?: 0;
			
			if (self::$mode !== 0) {
				exec('title '.self::$config[self::$mode]['platform_name']);
				self::screenshot();
				if (file_exists('ask.png') !== true) throw new Exception('手机连接异常,无法获取屏幕截图');
				self::$png = ImageCreateFromPng('ask.png');
				self::$height = imagesy(self::$png);
				self::$width = imagesx(self::$png);
				imagedestroy(self::$png);
			} else {
				exec('title 练习模式');
			}
		} catch (Exception $e) {
			exit('error:'.$e->getMessage()."\n");
		}
	}

	// 截图保存到当前目录
	public static function screenshot()
	{
		exec(self::$adb_root.' shell screencap -p /sdcard/ask.png');
		exec(self::$adb_root.' pull /sdcard/ask.png .');
	}
}
hero::start();