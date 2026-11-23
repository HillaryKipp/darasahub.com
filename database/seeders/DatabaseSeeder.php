<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SiteSettingSeeder::class,
            CurriculumTaxonomySeeder::class,
            DriveLearningAreaSeeder::class,
            GradePastPaperSeeder::class,
            KcsePastPaperSeeder::class,
        ]);

        if (app()->environment('local', 'testing')) {
            $this->call(DemoPaperSeeder::class);
        }
    }
}
