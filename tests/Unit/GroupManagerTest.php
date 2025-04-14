<?php

namespace Cascade\ClaudioClassMapper\Tests\Unit;

use Cascade\ClaudioClassMapper\GroupManager;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestService;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestUser;
use Cascade\ClaudioClassMapper\Tests\TestCase;

class GroupManagerTest extends TestCase
{
    protected $groupManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->groupManager = app(GroupManager::class);
        
        // Run migrations to ensure tables exist
        $this->artisan('migrate:fresh');
    }
    
    public function testCreateGroup()
    {
        $classNames = [TestUser::class, TestService::class];
        
        $id = $this->groupManager->createGroup('test-group', $classNames, 'Test group description');
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Verify group was created in database
        $this->assertDatabaseHas('class_map_groups', [
            'id' => $id,
            'name' => 'test-group',
            'description' => 'Test group description',
        ]);
    }
    
    public function testUpdateGroup()
    {
        // First create a group
        $classNames = [TestUser::class];
        $this->groupManager->createGroup('test-group', $classNames, 'Test group description');
        
        // Update the group
        $updatedClassNames = [TestUser::class, TestService::class];
        $result = $this->groupManager->updateGroup('test-group', $updatedClassNames, 'Updated description');
        
        $this->assertTrue($result);
        
        // Verify group was updated
        $this->assertDatabaseHas('class_map_groups', [
            'name' => 'test-group',
            'description' => 'Updated description',
        ]);
        
        // Get the group and check class names
        $group = $this->groupManager->getGroup('test-group');
        $this->assertEquals($updatedClassNames, $group->class_names);
    }
    
    public function testDeleteGroup()
    {
        // First create a group
        $classNames = [TestUser::class];
        $this->groupManager->createGroup('test-group', $classNames);
        
        // Delete the group
        $result = $this->groupManager->deleteGroup('test-group');
        
        $this->assertTrue($result);
        
        // Verify group was deleted
        $this->assertDatabaseMissing('class_map_groups', [
            'name' => 'test-group',
        ]);
    }
    
    public function testGetGroup()
    {
        // First create a group
        $classNames = [TestUser::class, TestService::class];
        $this->groupManager->createGroup('test-group', $classNames, 'Test group description');
        
        // Get the group
        $group = $this->groupManager->getGroup('test-group');
        
        $this->assertIsObject($group);
        $this->assertEquals('test-group', $group->name);
        $this->assertEquals('Test group description', $group->description);
        $this->assertEquals($classNames, $group->class_names);
    }
    
    public function testGetNonExistentGroup()
    {
        $group = $this->groupManager->getGroup('non-existent-group');
        
        $this->assertNull($group);
    }
    
    public function testListGroups()
    {
        // Create some groups
        $this->groupManager->createGroup('group1', [TestUser::class], 'Group 1');
        $this->groupManager->createGroup('group2', [TestService::class], 'Group 2');
        
        // List all groups
        $groups = $this->groupManager->listGroups();
        
        $this->assertCount(2, $groups);
        
        // Check first group
        $group1 = collect($groups)->firstWhere('name', 'group1');
        $this->assertEquals('Group 1', $group1->description);
        $this->assertEquals([TestUser::class], $group1->class_names);
        
        // Check second group
        $group2 = collect($groups)->firstWhere('name', 'group2');
        $this->assertEquals('Group 2', $group2->description);
        $this->assertEquals([TestService::class], $group2->class_names);
    }
    
    public function testGetGroupClassNames()
    {
        // Create a group
        $classNames = [TestUser::class, TestService::class];
        $this->groupManager->createGroup('test-group', $classNames);
        
        // Get class names
        $result = $this->groupManager->getGroupClassNames('test-group');
        
        $this->assertEquals($classNames, $result);
    }
    
    public function testGetNonExistentGroupClassNames()
    {
        $result = $this->groupManager->getGroupClassNames('non-existent-group');
        
        $this->assertEmpty($result);
    }
}