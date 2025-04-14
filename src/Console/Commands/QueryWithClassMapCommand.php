<?php

namespace Cascade\ClaudioClassMapper\Console\Commands;

use Illuminate\Console\Command;
use Cascade\ClaudioClassMapper\ClassMapper;
use Cascade\ClaudioClassMapper\GroupManager;

class QueryWithClassMapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claudio:query
                           {query : The question to ask about the code}
                           {--class=* : Specific class(es) to include in the context}
                           {--group= : Use a predefined group of classes}'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query the AI with class context';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ClassMapper $classMapper)
    {        
        $query = $this->argument('query');
        $classNames = $this->option('class');
        $groupName = $this->option('group');
        
        if (empty($classNames) && empty($groupName)) {
            $this->error('You must specify either classes or a group to include in the context.');
            return 1;
        }
        
        // Load class map
        $classMapper->loadClassMap();
        
        // If group is specified, load classes from the group
        if (!empty($groupName)) {
            $groupManager = app(GroupManager::class);
            $group = $groupManager->getGroup($groupName);
            
            if (!$group) {
                $this->error("Group '{$groupName}' not found.");
                return 1;
            }
            
            $classNames = $group->class_names;
            
            if (empty($classNames)) {
                $this->error("Group '{$groupName}' has no classes defined.");
                return 1;
            }
        }
        
        $this->info('Querying AI with context...');
        
        try {
            $response = $classMapper->queryWithContext($query, $classNames);
            
            $this->line('\n' . $response);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error querying AI: ' . $e->getMessage());
            return 1;
        }
    }
}
