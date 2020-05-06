<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->addUsers($manager);
        $this->addProducts($manager);
    }

    private function addUsers(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Arif');
        $user->setEmail('cwepicentre@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, '123456'));
        $user->setCreatedAt(new \DateTime('now'));
        $user->setUpdatedAt(new \DateTime('now'));
        $manager->persist($user);

        // Dummy data
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setName($this->faker->name);
            $user->setEmail($this->faker->unique()->safeEmail);
            $user->setPassword($this->passwordEncoder->encodePassword($user, '123456'));
            $user->setCreatedAt(new \DateTime('now'));
            $user->setUpdatedAt(new \DateTime('now'));

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function addProducts(ObjectManager $manager)
    {
        $user = $manager->getRepository(User::class)->findOneBy([]);
        // Dummy data
        for ($i = 1; $i <= 100; $i++) {
            $product = new Product();
            $product->setUser($user);
            $product->setProductCode($this->faker->word);
            $product->setName($this->faker->sentence());
            $product->setDescription($this->faker->text());
            $product->setPrice($this->faker->randomFloat());
            $product->setCreatedAt(new \DateTime('now'));
            $product->setUpdatedAt(new \DateTime('now'));

            $manager->persist($product);

            if ($i === 1) {
                $this->addOrder($manager, $user, $product);
            }
        }

        $manager->flush();
    }

    private function addOrder(ObjectManager $manager, $user, Product $product)
    {
        $quantity = rand(1, 5);

        $order = new Order();
        $order->setUser($user);
        $order->setProduct($product);
        $order->setOrderCode($this->faker->word);
        $order->setQuantity($quantity);
        $order->setPrice($quantity * $product->getPrice());
        $order->setOrderDate(new \DateTime('now'));
        $order->setShippingAddress('Maltepe/İstanbul');
        $order->setShippingDate(new \DateTime('tomorrow'));

        $manager->persist($order);
        $manager->flush();
    }
}
