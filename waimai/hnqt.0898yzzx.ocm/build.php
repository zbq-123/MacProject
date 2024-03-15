<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 生成应用公共文件
    //'__file__' => ['common.php', 'config.php', 'database.php'],

    // 定义模块的自动生成 （按照实际定义的文件名生成）
    // 'common'     => [
    //     '__dir__'    => ['controller', 'model'],
    //     'controller' => ['Base','ClientBase','AdminBase'],
    //     'model'      => ['User'],
    // ],
     'client'     => [
        // '__file__'   => ['common.php'],
         //'__dir__'    => ['controller', 'model'],
         'controller' => ['Index', 'Comments', 'Interaction', 'Gov', 'NetGroups', 'Video', 'User'],
         'model'      => [],
     ],
    // 'admin'     => [
    //     '__file__'   => ['common.php'],
    //     '__dir__'    => ['controller', 'model'],
    //     'controller' => ['Index'],
    //     'model'      => [],
    // ],
    // 'extra'     => [],

    //生成模型
    /*'admin' =>  [
        'model' =>  ['Admin','AuthRule','AuthGroup']
    ],
    'common' =>  [
        'model' =>  ['News','Comment','Carousel','NewsType','NetGroup','GovType','Gov','Favorites','VideoNews','Ad','Push','Follows','Rights','TipOff','RightType','RightReply',
            'Complaint','ComplaintReply','Consults','ConsultsType','ConsultsReply',
            'Votes','VoteMember','VoteRecord','Contribution','Area','Feedback','User',
        ]
    ],*/

];
