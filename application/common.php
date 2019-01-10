<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if (!function_exists('getLimitOffset')) {
    /**
     * @param int $size
     * @return array
     */
    function getLimitOffset(int $size = 12)
    {
        $page = input('page', 1);

        return [
            $size,
            ($page - 1) * $size
        ];
    }
}

if (!function_exists('isUrl')) {
    /**
     * 判断是否是URL
     * @param $src
     * @return bool
     */
    function isUrl($src)
    {
        return substr($src, 0, 7) === 'http://' ||
            substr($src, 0, 8) === 'https://';
    }
}


