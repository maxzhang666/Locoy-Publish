<?php


namespace MaxZhang\LocoyPublish\Controller;


use Flarum\Discussion\Command\StartDiscussion;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;

//use Illuminate\Support\Facades\Log;
use Laminas\Diactoros\Response\JsonResponse;
use Overtrue\Pinyin\Pinyin;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

class LocoyController implements RequestHandlerInterface
{
    private $user;
    private $bus;

    public function __construct(UserRepository $user, Dispatcher $bus)
    {
        $this->user = $user;
        $this->bus  = $bus;
    }

    private function success($data)
    {
        return new JsonResponse(['err' => 0, 'msg' => $data]);
    }

    private function error($msg)
    {
        return new JsonResponse(['err' => 1, 'msg' => $msg]);
    }


    private function getTags($tagsStr)
    {
        $strTags = explode(',', $tagsStr);
        if (class_exists('Flarum\Tags\Tag')) {
            $tags         = \Flarum\Tags\Tag::all();
            $exitsTags    = [];
            $notExitsTags = [];
            foreach ($strTags as $tag) {
                $t = $tags->where('name', $tag)->first();
                if ($t) {
                    $exitsTags[] = $t->id;
                } else {
                    $notExitsTags[] = $tag;
                }
            }

            #region 创建不存在的tag
            //创建不存在的tag
            foreach ($notExitsTags as $tag) {

                if (empty($tag)) {
                    continue;
                }

                $tag = trim($tag);

                $slug = (new Pinyin())->abbr($tag);
                if ($tags->where('slug', $slug)->first()) {
                    $slug .= '-' . time();
                }
                $data = [
                    'data' => [
                        'type'       => 'tags',
                        'attributes' => [
                            'name'        => $tag,
                            'description' => $tag,
                            'color'       => '#000000',
                            'icon'        => 'fas fa-hashtag',
                            'slug'        => $slug,
                            'isHidden'    => false,
                            'primary'     => false
                        ]
                    ]
                ];
                $this->bus->dispatch(
                    new \Flarum\Tags\Command\CreateTag($this->actor, Arr::get($data, 'data', []))
                );
                $t = \Flarum\Tags\Tag::all()->where('name', $tag)->first();
                if ($t) {
                    $exitsTags[] = $t->id;
                }
            }
            #endregion

            return $exitsTags;
        }

        return [];
    }

    private $authId = 1;
    private $actor;

    public function handle(Request $request): ResponseInterface
    {

        $data = $request->getParsedBody();

        $ipAddress = $request->getAttribute('ipAddress');

        //请求密码
        $pwd = $data['pwd'];
        if ($pwd !== '123') {
            return $this->error('爬:' . $pwd);
        }

        $tags = array_key_exists('tag', $data) ? $data['tag'] : 3;
        if (!isset($tags)) {
            $tags = 3;
        }

        $tagsArr = [];
        $title   = $data['title'];
        $content = $data['content'];

        $this->actor = $this->user->findOrFail($this->authId);

        $tagsStr = $data['tags'];

        if (isset($tagsStr) && $tagsStr != '') {
            //try {
            $tagsArr = $this->getTags($tagsStr);
            //} catch (\Exception $e) {
            //Log::error('自动化创建此标签失败', [$e->getMessage()]);
            //}
        }

        $tagsArr[] = $tags;

        $dis = [
            'data' => [
                'type'          => 'discussions',
                'attributes'    => [
                    'title'   => $title,
                    'content' => $content
                ],
                'relationships' => [
                    'tags' => [
                        'data' => [
                            [
                                'type' => 'tags',
                                'id'   => $tagsArr
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->bus->dispatch(
            new StartDiscussion($this->actor, Arr::get($dis, 'data', []), $ipAddress)
        );
        return $this->success($pwd . ' ok');
    }
}
