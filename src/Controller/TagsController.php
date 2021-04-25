<?php


namespace MaxZhang\LocoyPublish\Controller;


use Flarum\Tags\Tag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TagsController extends LocoyBaseController
{
    private $tags;

    public function __construct(Tag $tags)
    {
        $this->tags = $tags;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tags = $this->tags->get();
        return $this->success($tags);
    }
}
