<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un admin
        $admin = new User();
        $admin->setEmail('admin@anime-shop.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('Anime Shop');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Créer un utilisateur test
        $user = new User();
        $user->setEmail('user@test.fr');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPhone('0612345678');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        // Créer une adresse pour l'utilisateur
        $address = new Address();
        $address->setFullName('Jean Dupont');
        $address->setStreet('10 Rue des Anime');
        $address->setCity('Paris');
        $address->setZipCode('75001');
        $address->setCountry('France');
        $address->setType('livraison');
        $address->setUser($user);
        $address->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($address);
        


        // Créer des catégories
        $categories = [
            ['name' => 'Figurines', 'slug' => 'figurines', 'desc' => 'Figurines de collection'],
            ['name' => 'Vêtements', 'slug' => 'vetements', 'desc' => 'T-shirts, sweats, casquettes'],
            ['name' => 'Posters', 'slug' => 'posters', 'desc' => 'Affiches et posters'],
            ['name' => 'Accessoires', 'slug' => 'accessoires', 'desc' => 'Porte-clés, badges, stickers'],
            ['name' => 'Manga', 'slug' => 'manga', 'desc' => 'Mangas et artbooks'],
        ];

        $categoryObjects = [];
        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat['name']);
            $category->setSlug($cat['slug']);
            $category->setDescription($cat['desc']);
            $manager->persist($category);
            $categoryObjects[] = $category;
        }

        // Créer des produits
        $products = [
            ['name' => 'Figurine Naruto Uzumaki', 'price' => 29.99, 'stock' => 15, 'anime' => 'Naruto', 'cat' => 0, 'featured' => true],
            ['name' => 'Figurine Luffy Gear 5', 'price' => 39.99, 'stock' => 10, 'anime' => 'One Piece', 'cat' => 0, 'featured' => true],
            ['name' => 'T-shirt Attack on Titan', 'price' => 24.99, 'stock' => 50, 'anime' => 'Attack on Titan', 'cat' => 1, 'featured' => false],
            ['name' => 'Poster Demon Slayer', 'price' => 12.99, 'stock' => 30, 'anime' => 'Demon Slayer', 'cat' => 2, 'featured' => true],
            ['name' => 'Porte-clés Goku', 'price' => 7.99, 'stock' => 100, 'anime' => 'Dragon Ball Z', 'cat' => 3, 'featured' => false],
            ['name' => 'Manga Jujutsu Kaisen Vol.1', 'price' => 6.99, 'stock' => 25, 'anime' => 'Jujutsu Kaisen', 'cat' => 4, 'featured' => false],
            ['name' => 'Figurine Sasuke Uchiha', 'price' => 34.99, 'stock' => 12, 'anime' => 'Naruto', 'cat' => 0, 'featured' => false],
            ['name' => 'Sweat My Hero Academia', 'price' => 44.99, 'stock' => 20, 'anime' => 'My Hero Academia', 'cat' => 1, 'featured' => true],
            ['name' => 'Poster One Piece Wanted', 'price' => 9.99, 'stock' => 40, 'anime' => 'One Piece', 'cat' => 2, 'featured' => false],
            ['name' => 'Badge Pack Anime Mix', 'price' => 14.99, 'stock' => 60, 'anime' => 'Mix', 'cat' => 3, 'featured' => false],
        ];

        foreach ($products as $prod) {
            $product = new Product();
            $product->setName($prod['name']);
            $product->setSlug(strtolower(str_replace(' ', '-', $prod['name'])));
            $product->setDescription('Description du produit ' . $prod['name']);
            $product->setPrice($prod['price']);
            $product->setStock($prod['stock']);
            $product->setAnimeName($prod['anime']);
            $product->setFeatured($prod['featured']);
            $product->setCategory($categoryObjects[$prod['cat']]);
            $product->setImage('default-product.jpg'); 
            $manager->persist($product);
        }

        $manager->flush();
    }
}