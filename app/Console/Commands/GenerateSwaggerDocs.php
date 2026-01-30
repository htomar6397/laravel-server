<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger documentation for KMC M&E System API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Swagger documentation...');

        // Create annotations directory if it doesn't exist
        $annotationsDir = storage_path('app/swagger/annotations');
        if (!File::exists($annotationsDir)) {
            File::makeDirectory($annotationsDir, 0755, true);
            $this->info('Created annotations directory: ' . $annotationsDir);
        }

        // Create docs directory if it doesn't exist
        $docsDir = resource_path('docs');
        if (!File::exists($docsDir)) {
            File::makeDirectory($docsDir, 0755, true);
            $this->info('Created docs directory: ' . $docsDir);
        }

        // Generate the swagger.json file
        $this->call('l5-swagger:generate');

        $this->info('Swagger documentation generated successfully!');
        $this->info('You can access the documentation at: ' . config('app.url') . '/api/documentation');
        $this->info('Raw JSON available at: ' . config('app.url') . '/api/v1/docs');

        return 0;
    }
}
