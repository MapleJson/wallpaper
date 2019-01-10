<?php

class curlException extends \Exception
{
}

/**
 * demo1 get 请求
 * curl()->get("http://www.baidu.com")->body();
 */
/**
 * demo2 get 请求 data 传参
 * curl()->get("http://www.baidu.com",[
 *      'data' => array(
 *                  'title' => 'test title',
 *                  'content' => 'test content',
 *              ),
 *      'build' => 1
 * ])->body();
 */
/**
 * demo3 post请求
 * curl()->post("http://www.xxx.com/say.php",array(
 *      'data' => array(
 *                  'title' => 'test title',
 *                  'content' => 'test content',
 *              ),
 * ))->body();
 */
/**
 * demo4 post请求 ajax请求 并设置cookie
 * curl()->post("http://www.xxx.com/save.php",array(
 *      'data' => array(
 *              'username' => 'test',
 *              'password' => 'test'
 *             ),
 *      'cookie' => '/tmp/cookie.txt',
 *      'ajax' => 1,
 * ))->body();
 */

/**
 * Class Curl
 * @method Curl post($url, $data = [])
 * @method Curl get($url, $data = [])
 * @package App\Extensions
 */
class Curl
{
    //单例对象
    private static $ins = null;
    //curl参数设置
    private $options = [];
    //请求结果
    private $body = null;
    //cookie文件
    private $cookieFile = null;
    //支持的请求方法
    private $method = array('get', 'post');
    //连接句柄
    private $handle = null;

    //禁用初始化方法
    final private function __construct()
    {
    }

    /**
     * 禁止克隆对象
     */
    private function __clone()
    {
    }

    /**
     * 单例化对象
     */
    public static function instance()
    {
        if (self::$ins instanceof self) {
            return self::$ins;
        }
        return self::$ins = new self();
    }

    /**
     * 调用不存在的方法被调用
     * @param string $method
     * @param array $args
     * @return Curl
     * @throws curlException
     */
    public function __call($method, $args)
    {
        if (!in_array($method, $this->method)) {
            throw new curlException("错误:不支持{$method}方法,支持的方法有"
                . join(',', $this->method));
        }
        return $this->request($method, $args);
    }

    /**
     * 返回执行结果
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * 执行请求
     * @param string $method
     * @param array $args
     * @return Curl
     * @throws curlException
     */
    private function request($method, $args)
    {
        $this->body = $this->execCurl($method, $args);
        return $this;
    }

    /**
     * 浏览器环境字符串
     */
    private function setHttpUserAgent()
    {
        $this->options[CURLOPT_USERAGENT] = isset($_SERVER['HTTP_USER_AGENT']) ?
            $_SERVER['HTTP_USER_AGENT'] :
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.101 Safari/537.36';
    }

    /**
     * 设置curl选项
     *
     * @param string $url
     * @param int $timeout
     */
    private function setCurlOptions(string $url, int $timeout)
    {
        //设置curl选项
        $this->options = [
            CURLOPT_URL            => $url,      //目标url
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => $timeout,  //超时
            CURLOPT_RETURNTRANSFER => 1,         //输出数据流
            CURLOPT_FOLLOWLOCATION => 1,         //自动跳转追踪
            CURLOPT_AUTOREFERER    => 1,         //自动设置来路信息
            CURLOPT_SSL_VERIFYPEER => 0,         //认证证书检查
            CURLOPT_SSL_VERIFYHOST => 0,         //检查SSL加密算法
            CURLOPT_HEADER         => 0,         //禁止头文件输出
            CURLOPT_NOSIGNAL       => 1,         //忽略所有传递的信号
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4, //ipv4寻址方式
            CURLOPT_ENCODING       => 'gzip,deflate',    //解析使用gzip压缩的网页
            CURLOPT_MAXREDIRS      => 3,
        ];
    }

    /**
     * 设置curl选项cookie
     *
     * @param string $cookie
     */
    private function setCookie(string $cookie)
    {
        //获取cookie文件地址路径
        if (!$this->cookieFile) {
            $this->cookieFile = $cookie;
        }
        //设置cookie
        if ($this->cookieFile) {
            $this->options[CURLOPT_COOKIEFILE] = $this->cookieFile;
            $this->options[CURLOPT_COOKIEJAR]  = $this->cookieFile;
        }
    }

    /**
     * 设置curl选项proxy
     *
     * @param array $proxy
     */
    private function setProxy(array $proxy)
    {
        $this->options[CURLOPT_PROXY]        = $proxy['host'];
        $this->options[CURLOPT_PROXYPORT]    = $proxy['port'];
        $this->options[CURLOPT_PROXYUSERPWD] = "{$proxy['user']}:{$proxy['pass']}";
    }

    /**
     * 设置curl所传参数
     *
     * @param $data
     */
    private function setPost($data)
    {
        //发送一个常规的POST请求
        $this->options[CURLOPT_POST] = 1;
        //使用HTTP协议中的"POST"操作来发送数据,支持键值对数组定义
        $this->options[CURLOPT_POSTFIELDS] = $data;
    }

    /**
     * 设置curl选项referer
     *
     * @param $referer
     */
    private function setReferer($referer)
    {
        //设置来路
        if ($referer) {
            $this->options[CURLOPT_REFERER] = $referer;
        }
    }

    /**
     * 设置curl选项header
     *
     * @param $headers
     * @param $ajax
     */
    private function setHeaders($headers, $ajax)
    {
        //初始化header数组
        $headerOptions = array();
        //检测是否是ajax提交
        if ($ajax) {
            $headerOptions['X-Requested-With'] = 'XMLHttpRequest';
        }
        //合并header
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $key => $header) {
                $headerOptions[$key] = $header;
            }
        }
        //转换header选项为浏览器header格式
        if (!empty($headerOptions) && is_array($headerOptions)) {
            foreach ($headerOptions as $k => $v) {
                $this->options[CURLOPT_HTTPHEADER][] = "{$k}: {$v}";
            }
        }
    }

    /**
     * 格式化参数
     *
     * @param $data
     * @return string
     */
    private function parseHttpData($data)
    {
        if (empty($data)) {
            return '';
        }
        if (is_array($data)) {
            return http_build_query($data, '', '&');
        }
        return $data;
    }

    /**
     * 是否get请求
     *
     * @param $method
     * @return bool
     */
    private function isGet($method)
    {
        return strtolower($method) === 'get';
    }

    /**
     * 是否post请求
     *
     * @param $method
     * @return bool
     */
    private function isPost($method)
    {
        return strtolower($method) === 'post';
    }

    /**
     * curl 单句柄请求
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws curlException
     */
    private function execCurl($method, $args)
    {
        //解析参数
        $url     = isset($args[0]) ? $args[0] : '';
        $data    = isset($args[1]['data']) ? $args[1]['data'] : '';
        $ajax    = isset($args[1]['ajax']) ? $args[1]['ajax'] : '';
        $timeout = isset($args[1]['timeout']) ? $args[1]['timeout'] : 30;
        $referer = isset($args[1]['referer']) ? $args[1]['referer'] : '';
        $proxy   = isset($args[1]['proxy']) ? $args[1]['proxy'] : '';
        $headers = isset($args[1]['headers']) ? $args[1]['headers'] : '';
        $cookie  = isset($args[1]['cookie']) ? $args[1]['cookie'] : '';
        //检测url必须参数 不能为空
        if (!$url) {
            throw new curlException('错误：curl请求地址不能为空');
        }
        //检测是否未启用自定义urlencode编码
        //如果get方式以data作为参数传递，此参数必须为1
        if (!empty($args[1]['build']) || $this->isGet($method)) {
            $data = $this->parseHttpData($data);
        }
        if ($this->isGet($method) && !empty($data)) {
            $url .= "?{$data}";
        }
        $this->setCurlOptions($url, $timeout); // 此方法必须最先使用，设置初始参数
        $this->setHttpUserAgent();
        $this->setCookie($cookie);
        $this->setReferer($referer);
        //设置代理 必须是数组并且非空
        if (is_array($proxy) && !empty($proxy)) {
            $this->setProxy($proxy);
        }
        //检测判断是否是post请求
        if ($this->isPost($method)) {
            $this->setPost($data);
        }
        $this->setHeaders($headers, $ajax);
        return $this->doCurl();
    }

    /**
     * 执行curl请求
     *
     * @return mixed
     */
    private function doCurl()
    {
        //创建curl句柄
        $this->handle = curl_init();
        //设置curl选项
        curl_setopt_array($this->handle, $this->options);
        //获取返回内容
        $content = curl_exec($this->handle);
        $this->_destroy();
        //返回内容
        return $content;
    }

    private function _destroy()
    {
        //关闭curl句柄
        curl_close($this->handle);
        $this->options = [];
        $this->handle  = null;
        $this->body    = null;
    }

    /**
     * 对一个对象进行字符串echo输出
     * 自动调用__toString方法
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }
}