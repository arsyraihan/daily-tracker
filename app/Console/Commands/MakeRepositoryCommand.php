<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a new repository and its interface';

    public function handle()
    {
        $input = $this->argument('name');
        $name = basename(str_replace(['/', '\\'], '/', $input));
        $relativeDir = str_replace('\\', '/', $input);
        $directory = app_path("Repositories/" . $relativeDir);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $namespace = "App\\Repositories\\" . str_replace('/', '\\', $relativeDir);

        $this->createInterface($name, $directory, $namespace);
        $this->createRepository($name, $directory, $namespace);

        $this->info("Repository and Interface for {$input} created successfully!");
    }

    protected function createInterface($name, $directory, $namespace)
    {
        $path = "{$directory}/{$name}RepositoryInterface.php";
        $content = "<?php\n\nnamespace {$namespace};\n\ninterface {$name}RepositoryInterface\n{\n    public function all();\n    public function findById(int \$id);\n    public function create(array \$data);\n    public function update(int \$id, array \$data);\n    public function delete(int \$id);\n}\n";

        if (!File::exists($path)) {
            File::put($path, $content);
        }
    }

    protected function createRepository($name, $directory, $namespace)
    {
        $path = "{$directory}/{$name}Repository.php";
        $content = "<?php\n\nnamespace {$namespace};\n\nclass {$name}Repository implements {$name}RepositoryInterface\n{\n    public function all()\n    {\n        // Implement logic\n    }\n\n    public function findById(int \$id)\n    {\n        // Implement logic\n    }\n\n    public function create(array \$data)\n    {\n        // Implement logic\n    }\n\n    public function update(int \$id, array \$data)\n    {\n        // Implement logic\n    }\n\n    public function delete(int \$id)\n    {\n        // Implement logic\n    }\n}\n";

        if (!File::exists($path)) {
            File::put($path, $content);
        }
    }
}
