<?php


namespace MaxZhang\LocoyPublish\Controller;


use Flarum\Discussion\Command\StartDiscussion;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
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
        $auth_id = 1;

        $title   = $data['title'];
        $content = $data['content'];

        $actor = $this->user->findOrFail($auth_id);


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
                                'id'   => $tags
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->bus->dispatch(
            new StartDiscussion($actor, Arr::get($dis, 'data', []), $ipAddress)
        );
        return $this->success($pwd);
    }
}
