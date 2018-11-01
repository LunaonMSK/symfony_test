<?php
/**
 * Created by PhpStorm.
 * User: skymei
 * Date: 2018/10/8
 * Time: 14:38
 */

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class LuckyController extends AbstractController
{
    /**
     * @Route("/lucky/number/{number<\d+>?123}",name="lucky_number",methods={"GET"})
     * @return Response
     * @throws \Exception
     */
    public function number($number, LoggerInterface $logger)
    {
//        throw new NotFoundHttpException('木有啦');
        return new Response($number);
    }

    /**
     * @Route("lucky/test", name="lucky_test",methods={"GET"})
     */
    public function test()
    {
        return $this->redirectToRoute('lucky_number' , ['number'=>666]);
    }

    /**
     * @Route("lucky/getProducts" , name="get_products" , methods={"GET"})
     */
    public function getProducts(ProductRepository $productRepository)
    {
        $res = $productRepository->findAllProducts();
        return new JsonResponse($res);
    }

    /**
     * @Route("lucky/demo" , methods={"GET"})
     */
    public function demo()
    {
        swoole_server();
        phpinfo();
    }
}