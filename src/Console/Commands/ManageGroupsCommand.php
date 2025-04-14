<?php

namespace PeakFlow\CodeMapper\Console\Commands;

use Illuminate\Console\Command;
use PeakFlow\CodeMapper\GroupManager;

class ManageGroupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:groups
                           {action? : Action to perform (create, update, delete, list, show)}
                           {name? : Name of the group}
                           {--description= : Description of the group}
                           {--class=* : Classes to include in the group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage class map groups';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GroupManager $groupManager)
    {
        $action = $this->argument('action') ?? 'list';
        $name = $this->argument('name');
        $description = $this->option('description');
        $classes = $this->option('class');
        
        switch ($action) {
            case 'create':
                return $this->createGroup($groupManager, $name, $classes, $description);
            
            case 'update':
                return $this->updateGroup($groupManager, $name, $classes, $description);
            
            case 'delete':
                return $this->deleteGroup($groupManager, $name);
            
            case 'show':
                return $this->showGroup($groupManager, $name);
            
            case 'list':
                return $this->listGroups($groupManager);
            
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }
    
    /**
     * Create a new group
     */
    protected function createGroup(GroupManager $groupManager, ?string $name, array $classes, ?string $description): int
    {
        if (!$name) {
            $name = $this->ask('Enter group name');
        }
        
        if (empty($classes)) {
            $this->error('You must specify at least one class to include in the group');
            return 1;
        }
        
        try {
            $groupManager->createGroup($name, $classes, $description);
            $this->info("Group '{$name}' created successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error creating group: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Update an existing group
     */
    protected function updateGroup(GroupManager $groupManager, ?string $name, array $classes, ?string $description): int
    {
        if (!$name) {
            $name = $this->ask('Enter group name to update');
        }
        
        $group = $groupManager->getGroup($name);
        
        if (!$group) {
            $this->error("Group '{$name}' not found");
            return 1;
        }
        
        if (empty($classes)) {
            $classes = $group->class_names;
        }
        
        if ($description === null) {
            $description = $group->description;
        }
        
        try {
            $groupManager->updateGroup($name, $classes, $description);
            $this->info("Group '{$name}' updated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error updating group: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Delete a group
     */
    protected function deleteGroup(GroupManager $groupManager, ?string $name): int
    {
        if (!$name) {
            $name = $this->ask('Enter group name to delete');
        }
        
        if (!$groupManager->getGroup($name)) {
            $this->error("Group '{$name}' not found");
            return 1;
        }
        
        if (!$this->confirm("Are you sure you want to delete group '{$name}'?")) {
            $this->info('Operation cancelled');
            return 0;
        }
        
        try {
            $groupManager->deleteGroup($name);
            $this->info("Group '{$name}' deleted successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error deleting group: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Show a group's details
     */
    protected function showGroup(GroupManager $groupManager, ?string $name): int
    {
        if (!$name) {
            $name = $this->ask('Enter group name to show');
        }
        
        $group = $groupManager->getGroup($name);
        
        if (!$group) {
            $this->error("Group '{$name}' not found");
            return 1;
        }
        
        $this->info("Group: {$group->name}");
        $this->info("Description: {$group->description}");
        $this->info("Classes:");
        
        foreach ($group->class_names as $className) {
            $this->line(" - {$className}");
        }
        
        return 0;
    }
    
    /**
     * List all groups
     */
    protected function listGroups(GroupManager $groupManager): int
    {
        $groups = $groupManager->listGroups();
        
        if (empty($groups)) {
            $this->info('No groups found');
            return 0;
        }
        
        $this->table(
            ['ID', 'Name', 'Description', 'Class Count'],
            array_map(function ($group) {
                return [
                    $group->id,
                    $group->name,
                    $group->description ?? 'No description',
                    count($group->class_names),
                ];
            }, $groups)
        );
        
        return 0;
    }
}