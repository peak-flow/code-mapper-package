<?php

namespace Cascade\ClaudioClassMapper\Console\Commands;

use Illuminate\Console\Command;
use Cascade\ClaudioClassMapper\ClassMapper;

class GenerateClassMapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claudio:generate-map
                           {--class=* : Specific class(es) to include in the map}
                           {--force : Force regeneration of the map}'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a class map for AI context';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ClassMapper $classMapper)
    {        
        $this->info('Generating class map...');
        
        $classNames = $this->option('class');
        $forceRegeneration = $this->option('force');
        
        $storagePath = config('claudio-class-mapper.storage_path');
        $mapPath = $storagePath . '/class-map.json';
        
        if (file_exists($mapPath) && !$forceRegeneration && empty($classNames)) {
            if (!$this->confirm('A class map already exists. Do you want to regenerate it?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        $result = $classMapper->generateClassMap($classNames);
        
        $this->info('Class map generated successfully!');
        $this->info('Total classes mapped: ' . count($result));
        
        return 0;
    }
}
