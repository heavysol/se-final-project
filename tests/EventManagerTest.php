<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/EventManager.php';

class EventManagerTest extends TestCase {
    private $mysqli;
    private $eventManager;

    protected function setUp(): void {
        // Create a mock mysqli object
        $this->mysqli = $this->createMock(mysqli::class);
        $this->eventManager = new EventManager($this->mysqli);
    }

    public function testCalculateProgress() {
        $this->assertEquals(50, $this->eventManager->calculateProgress(25, 50));
        $this->assertEquals(0, $this->eventManager->calculateProgress(0, 100));
        $this->assertEquals(0, $this->eventManager->calculateProgress(10, 0)); // Avoid division by zero
    }

    public function testGetProgressClass() {
        $this->assertEquals('bg-success', $this->eventManager->getProgressClass(85));
        $this->assertEquals('bg-warning', $this->eventManager->getProgressClass(60));
        $this->assertEquals('bg-info', $this->eventManager->getProgressClass(30));
    }

    // Integration or database tests for getEventsByOrganizer would require a real or mocked DB
}

?>