<?php

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/EventService.php';

class EventServiceTest extends TestCase
{
    private $eventRepository;
    private $user;
    private $eventService;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->user = new User();
        
        $this->eventService = new EventService(
            $this->eventRepository,
            $this->user
        );
    }

    public function testCreateEvent()
    {
        $eventData = [
            'name' => 'Test Event',
            'description' => 'Test Description',
            'startDate' => '2024-03-01 10:00:00',
            'endDate' => '2024-03-01 12:00:00',
            'location' => 'Test Location'
        ];

        $this->eventRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($event) use ($eventData) {
                return $event instanceof Event &&
                    $event->getName() === $eventData['name'] &&
                    $event->getDescription() === $eventData['description'] &&
                    $event->getLocation() === $eventData['location'];
            }));

        $result = $this->eventService->createEvent($eventData);

        $this->assertInstanceOf(Event::class, $result);
        $this->assertEquals($eventData['name'], $result->getName());
        $this->assertEquals($eventData['description'], $result->getDescription());
        $this->assertEquals($eventData['location'], $result->getLocation());
        $this->assertSame($this->user, $result->getCreatedBy());
    }

    public function testUpdateEvent()
    {
        $event = new Event();
        $event->setName('Original Name');
        $event->setDescription('Original Description');
        $event->setLocation('Original Location');

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'location' => 'Updated Location'
        ];

        $this->eventRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($event) use ($updateData) {
                return $event instanceof Event &&
                    $event->getName() === $updateData['name'] &&
                    $event->getDescription() === $updateData['description'] &&
                    $event->getLocation() === $updateData['location'];
            }));

        $updatedEvent = $this->eventService->updateEvent($event, $updateData);

        $this->assertEquals($updateData['name'], $updatedEvent->getName());
        $this->assertEquals($updateData['description'], $updatedEvent->getDescription());
        $this->assertEquals($updateData['location'], $updatedEvent->getLocation());
    }

    public function testDeleteEvent()
    {
        $event = new Event();

        $this->eventRepository->expects($this->once())
            ->method('remove')
            ->with($event);

        $this->eventService->deleteEvent($event);
    }

    public function testGetEvent()
    {
        $event = new Event();
        $eventId = 1;

        $this->eventRepository->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($event);

        $result = $this->eventService->getEvent($eventId);

        $this->assertSame($event, $result);
    }

    public function testGetAllEvents()
    {
        $events = [new Event(), new Event()];

        $this->eventRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($events);

        $result = $this->eventService->getAllEvents();

        $this->assertSame($events, $result);
    }

    public function testGetUpcomingEvents()
    {
        $events = [new Event(), new Event()];

        $this->eventRepository->expects($this->once())
            ->method('findUpcomingEvents')
            ->willReturn($events);

        $result = $this->eventService->getUpcomingEvents();

        $this->assertSame($events, $result);
    }

    public function testGetEventsByUser()
    {
        $userId = 1;
        $events = [new Event(), new Event()];

        $this->eventRepository->expects($this->once())
            ->method('findBy')
            ->with(['createdBy' => $userId])
            ->willReturn($events);

        $result = $this->eventService->getEventsByUser($userId);

        $this->assertSame($events, $result);
    }
} 