<?php


namespace MaxZhang\LocoyPublish\Controller;


use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Server\RequestHandlerInterface;

abstract class LocoyBaseController implements RequestHandlerInterface
{
    public function success($data, $msg = '')
    {
        return new JsonResponse(['err' => 0, 'msg' => $msg, 'data' => $data]);
    }

    public function error($msg)
    {
        return new JsonResponse(['err' => 1, 'msg' => $msg, 'data' => '']);
    }
}
