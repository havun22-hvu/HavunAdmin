<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hosting & Infrastructuur',
                'slug' => 'hosting',
                'description' => 'Server hosting, domeinen, CDN, storage',
                'color' => '#FF6B6B',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Software & Licenties',
                'slug' => 'software',
                'description' => 'Development tools, design software, SaaS subscriptions',
                'color' => '#4ECDC4',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Betaaldiensten',
                'slug' => 'betaaldiensten',
                'description' => 'Mollie, Bunq, payment processing fees',
                'color' => '#45B7D1',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Advertenties, SEO tools, social media',
                'color' => '#FFA07A',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Kantoorkosten',
                'slug' => 'kantoor',
                'description' => 'Thuiswerkplek, telefoon, internet, kantoormateriaal',
                'color' => '#98D8C8',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Overige',
                'slug' => 'overige',
                'description' => 'Diverse kosten die niet in andere categorieën vallen',
                'color' => '#95A5A6',
                'parent_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
