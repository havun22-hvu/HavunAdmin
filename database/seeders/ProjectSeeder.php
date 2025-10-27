<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Herdenkingsportaal.nl',
                'slug' => 'herdenkingsportaal',
                'description' => 'Online platform voor digitale herdenkingen en condoleances',
                'color' => '#4A90E2',
                'status' => 'active',
                'start_date' => '2024-01-01',
                'is_active' => true,
            ],
            [
                'name' => 'IDSee',
                'slug' => 'idsee',
                'description' => 'Hondenregistratie systeem met chipnummers en blockchain registratie voor controle op buitenlandse hondenhandel',
                'color' => '#50E3C2',
                'status' => 'development',
                'start_date' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Judotoernooi',
                'slug' => 'judotoernooi',
                'description' => 'Toernooi organisatie en afhandeling systeem voor live judotoernooien',
                'color' => '#F5A623',
                'status' => 'development',
                'start_date' => '2024-06-01',
                'is_active' => true,
            ],
            [
                'name' => 'Algemeen',
                'slug' => 'algemeen',
                'description' => 'Algemene kosten die niet project-specifiek zijn',
                'color' => '#7ED321',
                'status' => 'active',
                'start_date' => null,
                'is_active' => true,
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
