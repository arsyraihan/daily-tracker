<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service with repository injection';

    public function handle()
    {
        $input = $this->argument('name');
        $name = basename(str_replace(['/', '\\'], '/', $input));
        $relativeDir = str_replace('\\', '/', $input);
        $directory = app_path("Services/" . $relativeDir);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->createService($name, $directory, $relativeDir);

        $this->info("Service for {$input} created successfully!");
    }

    protected function createService($name, $directory, $relativeDir)
    {
        $path = "{$directory}/{$name}Service.php";
        $interface = "{$name}RepositoryInterface";
        $repoNamespace = str_replace('/', '\\', $relativeDir);
        $interfaceNamespace = "App\Repositories\\{$repoNamespace}\\{$interface}";
        $serviceNamespace = "App\Services\\" . str_replace('/', '\\', $relativeDir);
        
        $content = "<?php\n\nnamespace {$serviceNamespace};\n\nuse {$interfaceNamespace};\n\nclass {$name}Service\n{\n    protected \$repository;\n\n    public function __construct({$interface} \$repository)\n    {\n        \$this->repository = \$repository;\n    }\n\n    public function getAll()\n    {\n        return \$this->repository->all();\n    }\n}\n";

        if (!File::exists($path)) {
            File::put($path, $content);
        }
    }
}
