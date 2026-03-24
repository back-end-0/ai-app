<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $products = [
        ['name' => 'Wireless Headphones', 'description' => 'Bluetooth noise-cancelling over-ear headphones with 30-hour battery life.', 'price' => 79.99, 'quantity' => 25],
        ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical keyboard with Cherry MX Blue switches and USB-C.', 'price' => 129.99, 'quantity' => 15],
        ['name' => 'USB-C Hub', 'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, SD card reader, and PD charging.', 'price' => 34.99, 'quantity' => 50],
        ['name' => 'Laptop Stand', 'description' => 'Adjustable aluminum laptop stand for ergonomic desk setup.', 'price' => 45.00, 'quantity' => 30],
        ['name' => 'Wireless Mouse', 'description' => 'Ergonomic wireless mouse with 4000 DPI sensor and silent clicks.', 'price' => 29.99, 'quantity' => 40],
        ['name' => '4K Monitor', 'description' => '27-inch 4K IPS monitor with HDR10 and 99% sRGB color accuracy.', 'price' => 349.99, 'quantity' => 10],
        ['name' => 'Webcam HD', 'description' => '1080p webcam with built-in microphone and auto-focus.', 'price' => 49.99, 'quantity' => 35],
        ['name' => 'Desk Lamp', 'description' => 'LED desk lamp with adjustable brightness and color temperature.', 'price' => 24.99, 'quantity' => 60],
        ['name' => 'Portable SSD', 'description' => '1TB portable SSD with USB 3.2 Gen 2 and 1050MB/s read speed.', 'price' => 89.99, 'quantity' => 20],
        ['name' => 'Phone Charger', 'description' => '65W GaN USB-C fast charger compatible with laptops and phones.', 'price' => 39.99, 'quantity' => 0],
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $productsWithoutDescription = [
        ['name' => 'Gaming Mouse Pad', 'price' => 19.99, 'quantity' => 45],
        ['name' => 'Bluetooth Speaker', 'price' => 59.99, 'quantity' => 20],
        ['name' => 'Tablet Stand', 'price' => 22.50, 'quantity' => 35],
        ['name' => 'Noise Cancelling Earbuds', 'price' => 149.99, 'quantity' => 12],
        ['name' => 'Smart Watch', 'price' => 199.99, 'quantity' => 8],
        ['name' => 'Ring Light', 'price' => 34.99, 'quantity' => 28],
        ['name' => 'Wireless Charger', 'price' => 25.99, 'quantity' => 55],
        ['name' => 'Mechanical Numpad', 'price' => 44.99, 'quantity' => 18],
        ['name' => 'USB Microphone', 'price' => 69.99, 'quantity' => 22],
        ['name' => 'Cable Management Kit', 'price' => 15.99, 'quantity' => 70],
    ];

    public function run(): void
    {
        foreach ($this->products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }

        foreach ($this->productsWithoutDescription as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
