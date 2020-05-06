<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Exceptions\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    /**
     * @Route("/api/orders", name="post_order", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createOrder(Request $request, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();

        $product_id = (int)$request->get('product_id');
        $quantity = (int) $request->get('quantity');
        $shipping_address = $request->get('shipping_address');

        // Basic request validation
        if ($product_id < 1 || $quantity < 1 || !$shipping_address) {
            throw new ValidationException('Prooduct id, quantity and shipping address cannot be empty');
        }

        // Product control
        $product = $em->getRepository(Product::class)->find($product_id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $user = $this->getUser();

        $order = new Order();
        $order->setUser($user);
        $order->setProduct($product);
        $order->setOrderCode(rand(10000, 99999));
        $order->setQuantity($quantity);
        $order->setPrice($quantity * $product->getPrice());
        $order->setOrderDate(new \DateTime('now'));
        $order->setShippingAddress($shipping_address);
        $order->setShippingDate(new \DateTime('tomorrow'));

        // Db validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errors_str = (string)$errors;

            throw new ValidationException($errors_str);
        }

        $em->persist($order);
        $em->flush();

        return $this->json(['success' => 'Order successfully created'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/orders/{id}", name="update_order", methods={"PUT"}, requirements={"id"="\d+"})
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function updateOrder(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $shipping_date = $order->getOrderDate();
        $now = new \DateTime('now');
        if ($shipping_date > $now) {
            throw new HttpException(400, 'Product shipped. The order cannot be updated.');
        }

        $product_id = (int)$request->get('product_id');
        $quantity = (int) $request->get('quantity');
        $shipping_address = $request->get('shipping_address');

        // [optional] Product update
        if ($product_id > 0) {
            // Product control
            $product = $em->getRepository(Product::class)->find($product_id);
            if (!$product) {
                throw $this->createNotFoundException('Product not found');
            }

            // [optional]Quantity update
            if ($quantity) {
                $order->setQuantity($quantity);
                $order->setPrice($quantity * $product->getPrice());
            }
            $order->setProduct($product);
        }

        // Shipping address update
        if ($shipping_address) {
            $order->setShippingAddress($shipping_address);
        }

        $em->flush();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/orders", name="get_orders", methods={"GET"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository(Order::class)->findAll();

        return $this->json($orders);
    }

    /**
     * @Route("/api/orders/my", name="get_my_orders", methods={"GET"})
     */
    public function myOrders()
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository(Order::class)->findByUserId($user->getId());

        return $this->json($orders);
    }

    /**
     * @Route("/api/orders/{id}", name="get_order", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function orderDetail($id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->json($order);
    }
}
