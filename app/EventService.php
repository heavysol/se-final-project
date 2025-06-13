<?php

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Validator\EventValidator;

class EventService
{
    private $eventRepository;
    private $user;
    private $validator;

    public function __construct(
        EventRepository $eventRepository,
        User $user,
        EventValidator $validator
    ) {
        $this->eventRepository = $eventRepository;
        $this->user = $user;
        $this->validator = $validator;
    }

    public function createEvent(array $data): array
    {
        $errors = $this->validator->validateEventData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $event = new Event();
        $event->setName($data['name']);
        $event->setDescription($data['description']);
        $event->setStartDate(new \DateTime($data['startDate']));
        $event->setEndDate(new \DateTime($data['endDate']));
        $event->setLocation($data['location']);
        $event->setCreatedBy($this->user);
        $event->setCategory($data['category']);
        $event->setMaxCapacity($data['maxCapacity']);
        $event->setIsPublic($data['isPublic'] ?? false);
        $event->setStatus('pending');
        $event->setCreatedAt(new \DateTime());
        $event->setUpdatedAt(new \DateTime());

        $errors = $this->validator->validate($event);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->eventRepository->save($event);

        return ['success' => true, 'event' => $event];
    }

    public function updateEvent(Event $event, array $data): array
    {
        $errors = $this->validator->validateEventUpdate($event, $data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if (isset($data['name'])) {
            $event->setName($data['name']);
        }
        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }
        if (isset($data['startDate'])) {
            $event->setStartDate(new \DateTime($data['startDate']));
        }
        if (isset($data['endDate'])) {
            $event->setEndDate(new \DateTime($data['endDate']));
        }
        if (isset($data['location'])) {
            $event->setLocation($data['location']);
        }
        if (isset($data['category'])) {
            $event->setCategory($data['category']);
        }
        if (isset($data['maxCapacity'])) {
            $event->setMaxCapacity($data['maxCapacity']);
        }
        if (isset($data['isPublic'])) {
            $event->setIsPublic($data['isPublic']);
        }
        if (isset($data['status'])) {
            $event->setStatus($data['status']);
        }
        
        $event->setUpdatedAt(new \DateTime());

        $errors = $this->validator->validate($event);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->eventRepository->save($event);

        return ['success' => true, 'event' => $event];
    }

    public function deleteEvent(Event $event): void
    {
        $this->eventRepository->delete($event);
    }

    public function getEvent(int $id): ?Event
    {
        return $this->eventRepository->find($id);
    }

    public function getAllEvents(): array
    {
        return $this->eventRepository->findAll();
    }

    public function getUpcomingEvents(): array
    {
        return $this->eventRepository->findUpcoming();
    }

    public function getEventsByUser(int $userId): array
    {
        return $this->eventRepository->findByUser($userId);
    }

    public function approveEvent(Event $event): array
    {
        if ($event->getStatus() !== 'pending') {
            return ['success' => false, 'errors' => ['Event is not in pending status']];
        }

        $event->setStatus('approved');
        $event->setUpdatedAt(new \DateTime());
        $this->eventRepository->save($event);
        return ['success' => true, 'event' => $event];
    }

    public function rejectEvent(Event $event): array
    {
        if ($event->getStatus() !== 'pending') {
            return ['success' => false, 'errors' => ['Event is not in pending status']];
        }

        $event->setStatus('rejected');
        $event->setUpdatedAt(new \DateTime());
        $this->eventRepository->save($event);
        return ['success' => true, 'event' => $event];
    }

    public function cancelEvent(Event $event): array
    {
        if ($event->getStatus() !== 'approved') {
            return ['success' => false, 'errors' => ['Only approved events can be cancelled']];
        }

        $event->setStatus('cancelled');
        $event->setUpdatedAt(new \DateTime());
        $this->eventRepository->save($event);
        return ['success' => true, 'event' => $event];
    }

    public function getEventsByCategory(string $category): array
    {
        return $this->eventRepository->findByCategory($category);
    }

    public function getPublicEvents(): array
    {
        return $this->eventRepository->findPublic();
    }

    public function getPendingEvents(): array
    {
        return $this->eventRepository->findByStatus('pending');
    }

    public function checkEventCapacity(Event $event): bool
    {
        $currentRegistrations = $this->eventRepository->getRegistrationCount($event->getId());
        return $currentRegistrations < $event->getMaxCapacity();
    }
} 