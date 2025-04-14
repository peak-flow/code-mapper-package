<?php

namespace PeakFlow\CodeMapper\Tests\Fixtures;

class TestService
{
    /**
     * The user instance
     * @var TestUser
     */
    private $user;
    
    /**
     * Create a new service instance
     *
     * @param TestUser $user
     */
    public function __construct(TestUser $user)
    {
        $this->user = $user;
    }
    
    /**
     * Get the user
     *
     * @return TestUser
     */
    public function getUser(): TestUser
    {
        return $this->user;
    }
    
    /**
     * Process the user
     *
     * @param array $data
     * @return array
     */
    public function processUser(array $data): array
    {
        $this->user->setName($data['name'] ?? $this->user->getName());
        
        return $this->user->toArray();
    }
}
