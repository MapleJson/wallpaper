<?php

namespace app\worker;

use Curl;
use think\facade\App;
use think\worker\Server;
use Workerman\Lib\Timer;

class Spider extends Server
{
    private static $preTime = '';

    private static $page360 = 0;

    private static $pages = 1;

    /**
     * 壁纸采集
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function spider()
    {
        self::doSpiderBing();
        Timer::add(600, function () {
            self::doSpiderBing();
        });
//        self::doSpider360();
//        Timer::add(60, function () {
//            self::doSpider360();
//        });
    }

    /**
     * bing采集
     * @return bool|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function doSpiderBing()
    {
        $data = Curl::instance()->get('http://www.bing.com/HPImageArchive.aspx', [
            'data' => [
                'format' => 'js',
                'idx'    => 0,
                'n'      => 1,
            ]
        ])->body();
        $wap  = Curl::instance()
            ->get('https://www.bing.com/cnhp/coverstory/?mkt=zh-CN')
            ->body();

        $wap    = json_decode($wap, true);
        $imgUrl = 'https://cn.bing.com' . json_decode($data, true)['images'][0]['url'];

        $filename = date('Ymd') . '.jpg';
        $exist    = model('index/Bing')->where('img', "/uploads/images/bing/{$filename}")->find();
        if (!empty($exist)) {
            return false;
        }
        $file = App::getRootPath() . 'public/uploads/images/bing/' . $filename;
        try {
            file_put_contents($file, file_get_contents($imgUrl));
            return model('index/Bing')->insert([
                /* 更改图片尺寸，减小体积 */
                'img'     => "/uploads/images/bing/{$filename}",
                /* 结束日期 */
                'day'     => date('Ymd'),
                /* 故事标题 */
                'title'   => $wap['title'],
                /* 内容 */
                'summary' => $wap['para1'],
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 360采集
     * @return bool|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function doSpider360()
    {
        self::$preTime = date('Ymd');
        if (self::$page360 > self::$pages) {
            self::$page360 = 0;
            return false;
        }
        $images = Curl::instance()->get('http://wallpaper.apc.360.cn/index.php', [
            'data' => [
                'c'     => 'WallPaper',
                'a'     => 'getAppsByOrder',
                'order' => 'create_time',
                'from'  => '360chrome',
                'start' => self::$page360++ * 10,
                'count' => 10,
            ]
        ])->body();
        $images = json_decode($images, true);
        if ((int)$images['errno'] !== 0) {
            return false;
        }
        self::$pages = ceil($images['total'] / 10);

        $data = [];
        $dir  = App::getRootPath() . 'public';
        $path = '/uploads/images/360/' . date('Ymd') . '/';
        if (!is_dir($dir . $path)) {
            mkdir($dir . $path);
        }
        $pxs = [
            'img_1600_900',
            'img_1440_900',
            'img_1366_768',
            'img_1280_800',
            'img_1280_1024',
            'img_1024_768',
            'url_mobile',
            'url_thumb',
        ];
        foreach ($images['data'] as $key => $image) {
            $exist = model('index/ThreeSixty')->where('thumb_id', $image['id'])->find();
            if (empty($exist)) {
                $data[$key]['thumb_id'] = $image['id'];
                $data[$key]['class_id'] = $image['class_id'];
                foreach ($pxs as $px) {
                    if (!empty($image[$px])) {
                        $$px = md5($image[$px]) . '.jpg';
                        try {
                            file_put_contents($dir . $path . $$px, file_get_contents($image[$px]));
                            $data[$key][$px] = $path . $$px;
                        } catch (\Exception $e) {
                            $data[$key][$px] = null;
                        }
                    } else {
                        $data[$key][$px] = null;
                    }
                }
            }
        }
        return model('index/ThreeSixty')->insertAll($data);
    }

}