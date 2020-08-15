<?php
/**
 * PanDownload 网页复刻版，PHP 语言版函数文件
 *
 * 务必要保证此文件存在，否则整个服务将会不可使用！
 *
 * 请勿随意修改此文件！如需更改相关配置请到 config.php ！
 *
 * @version 1.2.3
 *
 * @author Yuan_Tuo <yuantuo666@gmail.com>
 * @link https://imwcr.cn/
 * @link https://space.bilibili.com/88197958
 *
 * @author LC <lc@lcwebsite.cn>
 * @link https://lcwebsite.cn/
 * @link https://space.bilibili.com/52618445
 */
if (!defined('init')){ http_response_code(403); header('Content-Type: text/plain; charset=utf-8'); header('Refresh: 3;url=./'); die("想啥呢？\r\n直接访问这个文件？"); } // 直接访问处理程序

// main
function setCurl(&$ch, array $header) { // 批处理 curl
	$a = curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略证书
	$b = curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 不检查证书与域名是否匹配（2为检查）
	$c = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 以字符串返回结果而非输出
	$d = curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 请求头
	return($a&&$b&&$c&&$d);
}
function post(string $url, $data, array $header) { // POST 发送数据
	$ch = curl_init($url);
	setCurl($ch, $header);
	curl_setopt($ch, CURLOPT_POST, true); // POST 方法
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // POST 的数据
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function get(string $url, array $header) { // GET 请求数据
	$ch = curl_init($url);
	setCurl($ch, $header);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function head(string $url, array $header) { // 获取响应头
	$ch = curl_init($url);
	setCurl($ch, $header);
	curl_setopt($ch, CURLOPT_HEADER, true); // 返回响应头
	curl_setopt($ch, CURLOPT_NOBODY, true); // 只要响应头
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	$response = curl_exec($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // 获得响应头大小
	$result = substr($response, 0, $header_size); // 根据头大小获取头信息
	curl_close($ch);
	return $result;
}
function getSubstr(string $str, string $leftStr, string $rightStr) {
	$left = strpos($str, $leftStr); // echo '左边:'.$left;
	$right = strpos($str, $rightStr, $left); // echo '<br>右边:'.$right;
	if ($left < 0 or $right < $left) return '';
	$left += strlen($leftStr);
	return substr($str, $left, $right - $left);
}
function formatSize(float $size, int $times = 0) { // 格式化size显示
	if ($size > 1024) {
		$size /= 1024;
		return formatSize($size, $times + 1); // 递归处理
	} else {
		switch ($times) {
			case '0':
				$unit = ($size == 1) ? 'Byte' : 'Bytes'; break;
			case '1':
				$unit = 'KB'; break;
			case '2':
				$unit = 'MB'; break;
			case '3':
				$unit = 'GB'; break;
			case '4':
				$unit = 'TB'; break;
			case '5':
				$unit = 'PB'; break;
			case '6':
				$unit = 'EB'; break;
			case '7':
				$unit = 'ZB'; break;
			default:
				$unit = '单位未知';
		}
		return sprintf('%.3f', $size) . $unit;
	}
}
function CheckPassword() { // 校验密码
	if (IsCheckPassword) {
		if ((!isset($_POST["password"])) || $_POST["password"] !== Password) die('<div class="row justify-content-center"><div class="col-md-7 col-sm-8 col-11">
		<div class="alert alert-danger" role="alert"><h5 class="alert-heading">错误</h5><hr><p class="card-text">密码错误！</p></div></div>
		</div></div><script>sweetAlert("错误","密码错误！","error");</script></body></html>');
		else echo '<script>sweetAlert("重要提示","请勿将密码告诉他人！此项目仅供测试使用！\r\n——Yuan_Tuo","info");</script>';
	}
}
// 解析分享链接
function verifyPwd(string $surl_1, string $pwd) { // 验证提取码
	$url = 'https://pan.baidu.com/share/verify?channel=chunlei&clienttype=0&web=1&app_id=250528&surl=' . $surl_1;
	$data = "pwd=$pwd";
	$header = array("User-Agent: netdisk", "Referer: https://pan.baidu.com/disk/home");
	$result = json_decode(post($url, $data, $header), true); // -12 提取码错误
	if ($result["errno"] === 0) return $result["randsk"];
	else return 1;
}
function getSign(string $surl, $randsk) {
	if ($randsk === 1) return 1;
	$url = 'https://pan.baidu.com/s/1' . $surl;
	$header = array(
		"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.514.1919.810 Safari/537.36",
		"Cookie: BDUSS=" . BDUSS . ";STOKEN=" . STOKEN . ";BDCLND=" . $randsk . ";"
	);
	if (preg_match('/yunData.setData\((\{.*?\})\);/', get($url, $header), $matches)) return json_decode($matches[1], true);
	else return 1;
}
function getFileList(string $shareid, string $uk, string $randsk) {
	$url = 'https://pan.baidu.com/share/list?app_id=250528&channel=chunlei&clienttype=0&desc=0&num=100&order=name&page=1&root=1&shareid='.$shareid.'&showempty=0&uk='.$uk.'&web=1';
	$header = array(
		"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.514.1919.810 Safari/537.36",
		"Cookie: BDUSS=" . BDUSS . ";STOKEN=" . STOKEN . ";BDCLND=" . $randsk . ";",
		"Referer: https://pan.baidu.com/disk/home"
	);
	return json_decode(get($url, $header), true);
}
function FileList($sign) {
	if ($sign === 1) return 1;
	return $sign['file_list'];
}
function FileInfo(string $filename, float $size, string $md5, int $server_ctime) { // 输出 HTML 字符串
	return '<p class="card-text">文件名：<b>' . $filename . '</b></p><p class="card-text">文件大小：<b>' . formatSize($size) . '</b></p><p class="card-text">文件MD5：<b>' . $md5
		. '</b></p><p class="card-text">上传时间：<b>' . date("Y年m月d日 H:i:s", $server_ctime) . '</b></p>';
}
function getDlink($fs_id, $timestamp, $sign, $randsk, $share_id, $uk) { // 获取下载链接
	$url = 'https://pan.baidu.com/api/sharedownload?app_id=250528&channel=chunlei&clienttype=12&sign=' . $sign . '&timestamp=' . $timestamp . '&web=1';
	$data = "encrypt=0" . "&extra=" . urlencode('{"sekey":"' . urldecode($randsk) . '"}') . "&fid_list=[$fs_id]" . "&primaryid=$share_id" . "&uk=$uk" . "&product=share&type=nolimit";
	$header = array(
		"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.514.1919.810 Safari/537.36",
		"Cookie: BDUSS=" . BDUSS . ";STOKEN=" . STOKEN . ";BDCLND=" . $randsk . ";",
		"Referer: https://pan.baidu.com/disk/home"
	);
	return json_decode(post($url, $data, $header), true);
	//没有 referer 就 112 ，然后没有 sekey 参数就 118    -20？？？
	// 		参数				类型		描述
	// list					json array	文件信息列表
	// names				json		如果查询共享目录，该字段为共享目录文件上传者的uk和账户名称
	// list[0]["category"]	int			文件类型
	// list[0]["dlink"]		string		文件下载地址
	// list[0]["file_name"]	string		文件名
	// list[0]["isdir"]		int			是否是目录
	// list[0]["server_ctime"]	int		文件的服务器创建时间
	// list[0]["server_mtime"]	int		文件的服务修改时间
	// list[0]["size"]		int			文件大小
	// list[0]["thumbs"]				缩略图地址
	// list[0]["height"]	int			图片高度
	// list[0]["width"]		int			图片宽度
	// list[0]["date_taken"]	int		图片拍摄时间
}