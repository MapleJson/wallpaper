<?php

namespace app\index\controller;

use think\facade\App;

class Index
{
    /**
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bing()
    {
        list($limit, $offset) = getLimitOffset(12);

        $images = model('index/Bing')->order('id DESC')->limit($offset, $limit)->select();

        $pages = model('index/Bing')->order('id DESC')->paginate($limit)->render();

        return view('bing/index', [
            'images' => $images,
            'pages'  => $pages,
        ]);
    }

    public function spider360()
    {
        $tags = [
            ['id' => 36, 'name' => '4K专区',],
            ['id' => 6, 'name' => '美女模特',],
            ['id' => 30, 'name' => '爱情美图',],
            ['id' => 9, 'name' => '风景大片',],
            ['id' => 15, 'name' => '小清新',],
            ['id' => 26, 'name' => '动漫卡通',],
            ['id' => 11, 'name' => '明星风尚',],
            ['id' => 14, 'name' => '萌宠动物',],
            ['id' => 5, 'name' => '游戏壁纸',],
            ['id' => 12, 'name' => '汽车天下',],
            ['id' => 10, 'name' => '炫酷时尚',],
            ['id' => 29, 'name' => '月历壁纸',],
            ['id' => 7, 'name' => '影视剧照',],
            ['id' => 13, 'name' => '节日美图',],
            ['id' => 22, 'name' => '军事天地',],
            ['id' => 16, 'name' => '劲爆体育',],
            ['id' => 18, 'name' => 'BABY秀',],
            ['id' => 35, 'name' => '文字控',],
        ];

        list($limit, $offset) = getLimitOffset(12);

        $param = [
            'c'     => 'WallPaper',
            'a'     => 'getAppsByOrder',
            'order' => 'create_time',
            'from'  => '360chrome',
            'start' => $offset,
            'count' => $limit,
        ];
        if ($hasTag = input('?tag')) {
            $param['a']     = 'getAppsByCategory';
            $param['cid']   = input('tag');
            $param['count'] = 198;
        }
        $images = \Curl::instance()->get('http://wallpaper.apc.360.cn/index.php', [
            'data' => $param
        ])->body();
        $images = json_decode($images, true);

        $pages = '';
        if (!$hasTag) {
            $count = ceil($images['total'] / 12);
            $pages = '<ul class="pagination">';
            for ($i = 1; $i <= $count; $i++) {
                if ($i == input('page', 1)) {
                    $pages .= "<li class='active'><span>{$i}</span></li> ";
                } else {
                    $pages .= "<li><a href='/360.html?page={$i}'>{$i}</a></li>";
                }
            }
            $pages .= '</ul>';
        }

        return view('360/index', [
            'images' => $images['data'],
            'pages'  => $pages,
            'tags'   => $tags,
        ]);
    }

    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function wallpaper360()
    {
        $tags = [
            ['id' => 36, 'name' => '4K专区',],
            ['id' => 6, 'name' => '美女模特',],
            ['id' => 30, 'name' => '爱情美图',],
            ['id' => 9, 'name' => '风景大片',],
            ['id' => 15, 'name' => '小清新',],
            ['id' => 26, 'name' => '动漫卡通',],
            ['id' => 11, 'name' => '明星风尚',],
            ['id' => 14, 'name' => '萌宠动物',],
            ['id' => 5, 'name' => '游戏壁纸',],
            ['id' => 12, 'name' => '汽车天下',],
            ['id' => 10, 'name' => '炫酷时尚',],
            ['id' => 29, 'name' => '月历壁纸',],
            ['id' => 7, 'name' => '影视剧照',],
            ['id' => 13, 'name' => '节日美图',],
            ['id' => 22, 'name' => '军事天地',],
            ['id' => 16, 'name' => '劲爆体育',],
            ['id' => 18, 'name' => 'BABY秀',],
            ['id' => 35, 'name' => '文字控',],
        ];

        list($limit, $offset) = getLimitOffset(12);

        $model = model('index/ThreeSixty');

        if (input('?tag')) {
            $model = $model->where('class_id', input('tag'));
        }

        $images = $model->order('id DESC')
            ->limit($offset, $limit)
            ->select();

//        $tags = model('index/ThreeSixty')
//            ->field('class_id')
//            ->order('id DESC')
//            ->group('class_id')
//            ->select();
//        foreach ($tags as &$tag) {
//            $tag['name'] = $tagCn[$tag['class_id']];
//        }
        $pages = $model->order('id DESC')
            ->paginate($limit)
            ->render();

        return view('360/index', [
            'images' => $images,
            'pages'  => $pages,
            'tags'   => $tags,
        ]);
    }

    public function souGou()
    {
        $data = \Curl::instance()->get('https://www.sogou.com/home/data/skinlist')->body();
        $data = json_decode($data, true); //JSON进行解码

        $images = []; //新建一个数组，进行存储
        foreach ($data['skinlib'] as $lib) {
            //循环并将所有数据提
            $images = array_merge($images, $lib['skins']);
        }
        return view('souGou/index', [
            'images' => $images,
        ]);
    }

    /**
     * @param $filename
     * @return \think\response\Download
     */
    public function download($filename)
    {
        if (isUrl($filename)) {
            file_put_contents(App::getRootPath() . 'public/uploads/images/temp.jpg', file_get_contents($filename));
            $filename = '/uploads/images/temp.jpg';
        }
        return download(App::getRootPath() . 'public' . $filename, md5($filename));
    }
}
