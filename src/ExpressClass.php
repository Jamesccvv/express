<?php

/**
 * 模拟请求快递100抓取数据
 * Created by PhpStorm
 * author: james
 * Class Express
 */
class ExpressClass
{
    private $ip = '';

    public function __construct()
    {
        $this->ip = $this->randIp();
    }

    /**
     * 随机IP
     * @return string
     * author: james
     * date: 2020-01-22 上午11:26
     */
    public function randIp()
    {
        $ip2id = round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
        $ip3id = round(rand(600000, 2550000) / 10000);
        $ip4id = round(rand(600000, 2550000) / 10000);
        //下面是第二种方法，在以下数据中随机抽取
        $arr_1   = array("218", "218", "66", "66", "218", "218", "60", "60", "202", "204", "66", "66", "66", "59", "61", "60", "222", "221", "66", "59", "60", "60", "66", "218", "218", "62", "63", "64", "66", "66", "122", "211");
        $randArr = mt_rand(0, count($arr_1) - 1);
        $ip1id   = $arr_1[$randArr];
        return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
    }

    /**
     * 获取快递公司名称
     * @param $expressNum //快递单号
     * @return mixed
     * author: james
     * date: 2020-01-22 上午11:26
     */
    public function getExpressCompany($expressNum)
    {
        $headerArray = array("Accept-Language: zh-CN,zh;q=0.8", "Cache-Control: no-cache", "Host:www.kuaidi100.com", "Referer:https://www.kuaidi100.com/");
        $url         = "https://www.kuaidi100.com/autonumber/autoComNum?resultv2=1&text=" . $expressNum;
        $output      = $this->httpGet($url, $headerArray);
        //正常返回
        //{"comCode":"","num":"9473736974","auto":[{"comCode":"debangwuliu","lengthPre":10,"noCount":1550,"noPre":"947373"}]}
        //下面正则匹配名称(json也可以哦)
        preg_match_all('#"comCode":"(.*?)"#', $output, $match);
        $company = $match[1][1];
        return $company;
    }

    /**
     * 具体快递信息
     * @param $ExpressNum
     * @return bool|string
     * author: james
     * date: 2020-01-22 上午11:31
     */
    public function getExpressData($ExpressNum)
    {
        $expressCompany = $this->getExpressCompany($ExpressNum);
        $cook1          = $this->disguiseCookie('Hm_lvt_22ea01af58ba2be0fec7c11b25e88e6c');
        $cook2          = $this->disguiseCookie('Hm_lpvt_22ea01af58ba2be0fec7c11b25e88e6c', 1, 0);
        $temp           = '0.' . mt_rand(1111111111111111, 8888888888888888);
        $headerArray    = array(
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Cache-Control: no-cache",
            "Host:www.kuaidi100.com",
            "Referer:https://www.kuaidi100.com/", "Cookie: $cook1; $cook2",
            "User-Agent:" . $this->userAgent()
        );

        $url    = "https://www.kuaidi100.com/query?type=" . $expressCompany . "&postid=" . $ExpressNum . "&temp=" . $temp . "&phone=";
        $output = $this->httpGet($url, $headerArray);
        //正常返回
        //{"comCode":"","num":"9473736974","auto":[{"comCode":"debangwuliu","lengthPre":10,"noCount":1550,"noPre":"947373"}]}
        //下面正则匹配名称(json也可以哦)
        return json_decode($output);
    }

    /**
     * http 请求
     * @param $url
     * @param $headerArray
     * @return bool|string
     * author: james
     * date: 2020-01-22 下午1:57
     */
    public function httpGet($url, $headerArray)
    {
        $commonHeaderArray = array('X-FORWARDED-FOR:' . $this->ip, 'CLIENT-IP:' . $this->ip);
        $headerArray       = array_merge($headerArray, $commonHeaderArray);
        $curl              = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36");
        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 仿造 Cookie
     * @param $name
     * @param int $num
     * @param int $step
     * @return string
     * author: james
     * date: 2020-01-22 上午11:30
     */
    public function disguiseCookie($name, $num = 4, $step = 5000)
    {
        $name = $name . "=";
        $time = time();
        for ($i = $num; $i > 0; $i--) {
            $time -= $step;
            $name .= $time . ',';
        }
        return trim($name, ',');
    }

    /**
     * 随机客户端
     * @return mixed
     * author: james
     * date: 2020-01-22 上午11:31
     */
    public function userAgent()
    {
        $agents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.163 Safari/535.1",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
            "Opera/9.80 (Windows NT 6.1; U; zh-cn) Presto/2.9.168 Version/11.50",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; Tablet PC 2.0; .NET4.0E)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ) AppleWebKit/534.12 (KHTML, like Gecko) Maxthon/3.0 Safari/534.12",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.33 Safari/534.3 SE 2.X MetaSr 1.0",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.41 Safari/535.1 QQBrowser/6.9.11079.201",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; AcooBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)",
            "Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.35; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
            "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)",
            "Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2pre) Gecko/20070215 K-Ninja/2.1.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/20080705 Firefox/3.0 Kapiko/3.0",
            "Mozilla/5.0 (X11; Linux i686; U;) Gecko/20070322 Kazehakase/0.4.5",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.8) Gecko Fedora/1.9.0.8-1.fc10 Kazehakase/0.5.6",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
            "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52"
        ];

        return $agents[array_rand($agents)];
    }
}