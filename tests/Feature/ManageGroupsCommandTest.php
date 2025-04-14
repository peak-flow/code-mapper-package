<?php

namespace PeakFlow\CodeMapper\Tests\Feature;

use PeakFlow\CodeMapper\Tests\Fixtures\TestService;
use PeakFlow\CodeMapper\Tests\Fixtures\TestUser;
use PeakFlow\CodeMapper\Tests\TestCase;

class ManageGroupsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');
    }
    
    public function testCreateGroup()
    {
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
            '--description' => 'Test group description',
            '--class' => [TestUser::class, TestService::class],
        ])
            ->expectsOutput("Group 'test-group' created successfully!")
            ->assertExitCode(0);
        
        $this->assertDatabaseHas('class_map_groups', [
            'name' => 'test-group',
            'description' => 'Test group description',
        ]);
    }
    
    public function testCreateGroupWithoutName()
    {
        $this->artisan('code:groups', [
            'action' => 'create',
            '--description' => 'Test group description',
            '--class' => [TestUser::class],
        ])
            ->expectsQuestion('Enter group name', 'prompted-group')
            ->expectsOutput("Group 'prompted-group' created successfully!")
            ->assertExitCode(0);
        
        $this->assertDatabaseHas('class_map_groups', [
            'name' => 'prompted-group',
        ]);
    }
    
    public function testCreateGroupWithoutClasses()
    {
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
        ])
            ->expectsOutput('You must specify at least one class to include in the group')
            ->assertExitCode(1);
    }
    
    public function testUpdateGroup()
    {
        // First create a group
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
            '--description' => 'Original description',
            '--class' => [TestUser::class],
        ]);
        
        // Then update it
        $this->artisan('code:groups', [
            'action' => 'update',
            'name' => 'test-group',
            '--description' => 'Updated description',
            '--class' => [TestUser::class, TestService::class],
        ])
            ->expectsOutput("Group 'test-group' updated successfully!")
            ->assertExitCode(0);
        
        $this->assertDatabaseHas('class_map_groups', [
            'name' => 'test-group',
            'description' => 'Updated description',
        ]);
    }
    
    public function testUpdateNonExistentGroup()
    {
        $this->artisan('code:groups', [
            'action' => 'update',
            'name' => 'non-existent-group',
        ])
            ->expectsOutput("Group 'non-existent-group' not found")
            ->assertExitCode(1);
    }
    
    public function testDeleteGroup()
    {
        // First create a group
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
            '--class' => [TestUser::class],
        ]);
        
        // Then delete it
        $this->artisan('code:groups', [
            'action' => 'delete',
            'name' => 'test-group',
        ])
            ->expectsQuestion("Are you sure you want to delete group 'test-group'?", true)
            ->expectsOutput("Group 'test-group' deleted successfully!")
            ->assertExitCode(0);
        
        $this->assertDatabaseMissing('class_map_groups', [
            'name' => 'test-group',
        ]);
    }
    
    public function testDeleteGroupCancelled()
    {
        // First create a group
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
            '--class' => [TestUser::class],
        ]);
        
        // Then try to delete it but cancel
        $this->artisan('code:groups', [
            'action' => 'delete',
            'name' => 'test-group',
        ])
            ->expectsQuestion("Are you sure you want to delete group 'test-group'?", false)
            ->expectsOutput("Operation cancelled")
            ->assertExitCode(0);
        
        $this->assertDatabaseHas('class_map_groups', [
            'name' => 'test-group',
        ]);
    }
    
    public function testDeleteNonExistentGroup()
    {
        $this->artisan('code:groups', [
            'action' => 'delete',
            'name' => 'non-existent-group',
        ])
            ->expectsOutput("Group 'non-existent-group' not found")
            ->assertExitCode(1);
    }
    
    public function testShowGroup()
    {
        // First create a group
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'test-group',
            '--description' => 'Test group description',
            '--class' => [TestUser::class],
        ]);
        
        // Then show it
        $this->artisan('code:groups', [
            'action' => 'show',
            'name' => 'test-group',
        ])
            ->expectsOutput("Group: test-group")
            ->expectsOutput("Description: Test group description")
            ->expectsOutput("Classes:")
            ->expectsOutput(" - " . TestUser::class)
            ->assertExitCode(0);
    }
    
    public function testShowNonExistentGroup()
    {
        $this->artisan('code:groups', [
            'action' => 'show',
            'name' => 'non-existent-group',
        ])
            ->expectsOutput("Group 'non-existent-group' not found")
            ->assertExitCode(1);
    }
    
    public function testListGroups()
    {
        // Create some groups
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'group1',
            '--description' => 'Group 1',
            '--class' => [TestUser::class],
        ]);
        
        $this->artisan('code:groups', [
            'action' => 'create',
            'name' => 'group2',
            '--description' => 'Group 2',
            '--class' => [TestService::class],
        ]);
        
        // List all groups
        $this->artisan('code:groups')
            ->assertExitCode(0);
        
        // Default action is list
        $this->artisan('code:groups', [
            'action' => 'list',
        ])
            ->assertExitCode(0);
    }
    
    public function testListEmptyGroups()
    {
        $this->artisan('code:groups')
            ->expectsOutput('No groups found')
            ->assertExitCode(0);
    }
    
    public function testInvalidAction()
    {
        $this->artisan('code:groups', [
            'action' => 'invalid-action',
        ])
            ->expectsOutput('Unknown action: invalid-action')
            ->assertExitCode(1);
    }
}