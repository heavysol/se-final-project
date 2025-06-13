<?php

use App\Entity\Event;

class EventValidator
{
    public function validate(Event $event): array
    {
        $errors = [];

        // Validate name
        if (empty($event->getName())) {
            $errors[] = 'Event name is required';
        } elseif (strlen($event->getName()) > 255) {
            $errors[] = 'Event name cannot exceed 255 characters';
        }

        // Validate description
        if (empty($event->getDescription())) {
            $errors[] = 'Event description is required';
        }

        // Validate dates
        if ($event->getStartDate() === null) {
            $errors[] = 'Start date is required';
        }
        if ($event->getEndDate() === null) {
            $errors[] = 'End date is required';
        }
        if ($event->getStartDate() !== null && $event->getEndDate() !== null) {
            if ($event->getStartDate() > $event->getEndDate()) {
                $errors[] = 'End date must be after start date';
            }
            if ($event->getStartDate() < new \DateTime()) {
                $errors[] = 'Start date cannot be in the past';
            }
        }

        // Validate location
        if (empty($event->getLocation())) {
            $errors[] = 'Event location is required';
        } elseif (strlen($event->getLocation()) > 255) {
            $errors[] = 'Location cannot exceed 255 characters';
        }

        // Validate category
        if (empty($event->getCategory())) {
            $errors[] = 'Event category is required';
        }

        // Validate capacity
        if ($event->getMaxCapacity() === null) {
            $errors[] = 'Maximum capacity is required';
        } elseif ($event->getMaxCapacity() < 1) {
            $errors[] = 'Maximum capacity must be at least 1';
        }

        return $errors;
    }

    public function validateEventData(array $data): array
    {
        $errors = [];

        // Validate required fields
        $requiredFields = ['name', 'description', 'startDate', 'endDate', 'location', 'category', 'maxCapacity'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = ucfirst($field) . ' is required';
            }
        }

        // Validate dates format
        if (isset($data['startDate'])) {
            if (!strtotime($data['startDate'])) {
                $errors[] = 'Invalid start date format';
            }
        }
        if (isset($data['endDate'])) {
            if (!strtotime($data['endDate'])) {
                $errors[] = 'Invalid end date format';
            }
        }

        // Validate capacity
        if (isset($data['maxCapacity'])) {
            if (!is_numeric($data['maxCapacity']) || $data['maxCapacity'] < 1) {
                $errors[] = 'Maximum capacity must be a positive number';
            }
        }

        return $errors;
    }

    public function validateEventUpdate(Event $event, array $data): array
    {
        $errors = [];

        // Validate dates if provided
        if (isset($data['startDate'])) {
            $startDate = new \DateTime($data['startDate']);
            if ($startDate < new \DateTime()) {
                $errors[] = 'Start date cannot be in the past';
            }
        }
        if (isset($data['endDate'])) {
            $endDate = new \DateTime($data['endDate']);
            if (isset($data['startDate'])) {
                $startDate = new \DateTime($data['startDate']);
                if ($endDate < $startDate) {
                    $errors[] = 'End date must be after start date';
                }
            } else {
                if ($endDate < $event->getStartDate()) {
                    $errors[] = 'End date must be after start date';
                }
            }
        }

        // Validate capacity if provided
        if (isset($data['maxCapacity'])) {
            if (!is_numeric($data['maxCapacity']) || $data['maxCapacity'] < 1) {
                $errors[] = 'Maximum capacity must be a positive number';
            } else {
                $currentRegistrations = $event->getRegistrationCount();
                if ($data['maxCapacity'] < $currentRegistrations) {
                    $errors[] = 'Maximum capacity cannot be less than current registrations';
                }
            }
        }

        return $errors;
    }
} 