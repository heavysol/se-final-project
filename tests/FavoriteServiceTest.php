<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/FavoriteService.php';

class FavoriteServiceTest extends TestCase {
    private $mysqli;
    private $service;

    protected function setUp(): void {
        // Mock mysqli connection
        $this->mysqli = $this->createMock(mysqli::class);
        $this->service = new FavoriteService($this->mysqli, 1);
    }

    public function testAddFavoriteEventAlreadyFavorited() {
        $stub = $this->getMockBuilder(FavoriteService::class)
                     ->setConstructorArgs([$this->mysqli, 1])
                     ->onlyMethods(['eventExists', 'isFavorited'])
                     ->getMock();

        $stub->method('eventExists')->willReturn(true);
        $stub->method('isFavorited')->willReturn(true);

        $result = $stub->addFavorite(10);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Event is already in favorites', $result['message']);
    }

    public function testAddFavoriteEventNotFound() {
        $stub = $this->getMockBuilder(FavoriteService::class)
                     ->setConstructorArgs([$this->mysqli, 1])
                     ->onlyMethods(['eventExists'])
                     ->getMock();

        $stub->method('eventExists')->willReturn(false);

        $result = $stub->addFavorite(999);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Event not found', $result['message']);
    }

    public function testRemoveFavoriteNotFavorited() {
        $stub = $this->getMockBuilder(FavoriteService::class)
                     ->setConstructorArgs([$this->mysqli, 1])
                     ->onlyMethods(['isFavorited'])
                     ->getMock();

        $stub->method('isFavorited')->willReturn(false);

        $result = $stub->removeFavorite(5);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Event is not in favorites', $result['message']);
    }

    // Additional tests can mock execute() for DB write success/failure
}
?>