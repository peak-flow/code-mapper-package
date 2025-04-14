<?php

namespace Cascade\ClaudioClassMapper\Tests\Fixtures;

class TestUser
{
    /**
     * The user's ID
     * @var int
     */
    private $id;
    
    /**
     * The user's name
     * @var string
     */
    protected $name;
    
    /**
     * The user's email
     * @var string
     */
    public $email;
    
    /**
     * Create a new user instance
     *
     * @param int $id
     * @param string $name
     * @param string $email
     */
    public function __construct(int $id, string $name, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
    
    /**
     * Get the user's ID
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * Get the user's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Set the user's name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    /**
     * Get the user as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
    
    /**
     * Create a user from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['email']
        );
    }
}
