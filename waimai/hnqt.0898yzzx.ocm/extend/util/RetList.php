<?php
namespace util;

use util\Ret;
/**
 * 返回app前端分页数据类
 */
class RetList
{
    public $list; //数据列表
    public $count; //数据总数
    public $page; //页码
    public $limit; //每页条数
    public function __construct($list = '', $count = 0, $page = 1, $limit = 10)
    {
        $this->list = $list;
        $this->count = $count;
        $this->page = $page;
        $this->limit = $limit;
    }
}