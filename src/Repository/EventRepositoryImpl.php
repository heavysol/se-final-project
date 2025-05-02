<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use PDO;

class EventRepositoryImpl implements EventRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Event $event): void
    {
        if ($event->getId() === null) {
            $this->insert($event);
        } else {
            $this->update($event);
        }
    }

    private function insert(Event $event): void
    {
        $sql = "INSERT INTO events (
            name, description, start_datetime, end_datetime, location,
            organizer_id, category, max_capacity, is_public, status,
            created_at, updated_at
        ) VALUES (
            :name, :description, :start_datetime, :end_datetime, :location,
            :organizer_id, :category, :max_capacity, :is_public, :status,
            :created_at, :updated_at
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'start_datetime' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_datetime' => $event->getEndDate()->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'organizer_id' => $event->getCreatedBy()->getId(),
            'category' => $event->getCategory(),
            'max_capacity' => $event->getMaxCapacity(),
            'is_public' => $event->isPublic() ? 1 : 0,
            'status' => $event->getStatus(),
            'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $event->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $event->setId($this->pdo->lastInsertId());
    }

    private function update(Event $event): void
    {
        $sql = "UPDATE events SET
            name = :name,
            description = :description,
            start_datetime = :start_datetime,
            end_datetime = :end_datetime,
            location = :location,
            category = :category,
            max_capacity = :max_capacity,
            is_public = :is_public,
            status = :status,
            updated_at = :updated_at
        WHERE event_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $event->getId(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'start_datetime' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_datetime' => $event->getEndDate()->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'category' => $event->getCategory(),
            'max_capacity' => $event->getMaxCapacity(),
            'is_public' => $event->isPublic() ? 1 : 0,
            'status' => $event->getStatus(),
            'updated_at' => $event->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(Event $event): void
    {
        $sql = "DELETE FROM events WHERE event_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $event->getId()]);
    }

    public function find(int $id): ?Event
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.event_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->createEventFromRow($row);
        }
        
        return null;
    }

    public function findAll(): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                ORDER BY e.start_datetime DESC";
        
        $stmt = $this->pdo->query($sql);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findUpcoming(): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.start_datetime > NOW() AND e.status = 'approved'
                ORDER BY e.start_datetime ASC";
        
        $stmt = $this->pdo->query($sql);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByUser(int $userId): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.organizer_id = :user_id
                ORDER BY e.start_datetime DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByCategory(string $category): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.category = :category AND e.status = 'approved'
                ORDER BY e.start_datetime DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['category' => $category]);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByStatus(string $status): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.status = :status
                ORDER BY e.start_datetime DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['status' => $status]);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findPublic(): array
    {
        $sql = "SELECT e.*, u.user_id as organizer_id, u.first_name, u.last_name
                FROM events e
                JOIN users u ON e.organizer_id = u.user_id
                WHERE e.is_public = 1 AND e.status = 'approved'
                ORDER BY e.start_datetime DESC";
        
        $stmt = $this->pdo->query($sql);
        return $this->createEventsFromRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getRegistrationCount(int $eventId): int
    {
        $sql = "SELECT COUNT(*) FROM registrations WHERE event_id = :event_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return (int) $stmt->fetchColumn();
    }

    private function createEventFromRow(array $row): Event
    {
        $event = new Event();
        $event->setId($row['event_id']);
        $event->setName($row['name']);
        $event->setDescription($row['description']);
        $event->setStartDate(new \DateTime($row['start_datetime']));
        $event->setEndDate(new \DateTime($row['end_datetime']));
        $event->setLocation($row['location']);
        $event->setCategory($row['category']);
        $event->setMaxCapacity($row['max_capacity']);
        $event->setIsPublic((bool) $row['is_public']);
        $event->setStatus($row['status']);
        $event->setCreatedAt(new \DateTime($row['created_at']));
        $event->setUpdatedAt(new \DateTime($row['updated_at']));

        $organizer = new User();
        $organizer->setId($row['organizer_id']);
        $organizer->setFirstName($row['first_name']);
        $organizer->setLastName($row['last_name']);
        $event->setCreatedBy($organizer);

        return $event;
    }

    private function createEventsFromRows(array $rows): array
    {
        return array_map([$this, 'createEventFromRow'], $rows);
    }
} 