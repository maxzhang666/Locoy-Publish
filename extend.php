<?php

/*
 * This file is part of maxzhang/locoy-publish.
 *
 * Copyright (c) 2021 maxzhang.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace MaxZhang\LocoyPublish;

use Flarum\Extend;
use Flarum\Http\Middleware\CheckCsrfToken;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),
    new Extend\Locales(__DIR__ . '/locale'),

    //添加api接口
    (new Extend\Routes('api'))
        ->get('/locoy', 'locoy.home', Controller\LocoyController::class)
        ->post('/locoy', 'locoy.home', Controller\LocoyController::class)
        ->post('/locoy/tags', 'locoy.tags', Controller\TagsController::class)
    ,
    //在api接口忽略csrf校验
    (new Extend\Middleware('api'))->remove(CheckCsrfToken::class),


    (new Extend\Settings())
        ->serializeToForum('locoy-publish.switch-pwd', 'locoy-publish.switch-pwd', 'boolval')
        ->serializeToForum('locoy-publish.pwd', 'locoy-publish.pwd', 'strval'),
];
