<?php

namespace App\Repository;

use App\Entity\Event;

interface EventRepository
{
    public function save(Event $event): void;
    public function delete(Event $event): void;
    public function find(int $id): ?Event;
    public function findAll(): array;
    public function findUpcoming(): array;
    public function findByUser(int $userId): array;
    public function findByCategory(string $category): array;
    public function findByStatus(string $status): array;
    public function findPublic(): array;
    public function getRegistrationCount(int $eventId): int;
} 