<?php

namespace Cascade\ClaudioClassMapper;

use Illuminate\Support\Facades\DB;

class GroupManager
{
    /**
     * Create a new class map group
     *
     * @param string $name
     * @param array $classNames
     * @param string|null $description
     * @return int
     */
    public function createGroup(string $name, array $classNames, ?string $description = null): int
    {
        return DB::table('class_map_groups')->insertGetId([
            'name' => $name,
            'description' => $description,
            'class_names' => json_encode($classNames),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Update an existing class map group
     *
     * @param string $name
     * @param array $classNames
     * @param string|null $description
     * @return bool
     */
    public function updateGroup(string $name, array $classNames, ?string $description = null): bool
    {
        return DB::table('class_map_groups')
            ->where('name', $name)
            ->update([
                'class_names' => json_encode($classNames),
                'description' => $description,
                'updated_at' => now(),
            ]) > 0;
    }
    
    /**
     * Delete a class map group
     *
     * @param string $name
     * @return bool
     */
    public function deleteGroup(string $name): bool
    {
        return DB::table('class_map_groups')
            ->where('name', $name)
            ->delete() > 0;
    }
    
    /**
     * Get a class map group
     *
     * @param string $name
     * @return object|null
     */
    public function getGroup(string $name): ?object
    {
        $group = DB::table('class_map_groups')
            ->where('name', $name)
            ->first();
            
        if ($group) {
            $group->class_names = json_decode($group->class_names, true);
        }
        
        return $group;
    }
    
    /**
     * List all class map groups
     *
     * @return array
     */
    public function listGroups(): array
    {
        $groups = DB::table('class_map_groups')
            ->select(['id', 'name', 'description', 'class_names'])
            ->get()
            ->toArray();
            
        foreach ($groups as &$group) {
            $group->class_names = json_decode($group->class_names, true);
        }
        
        return $groups;
    }
    
    /**
     * Get the class names for a group
     *
     * @param string $name
     * @return array
     */
    public function getGroupClassNames(string $name): array
    {
        $group = $this->getGroup($name);
        
        if (!$group) {
            return [];
        }
        
        return $group->class_names;
    }
}
