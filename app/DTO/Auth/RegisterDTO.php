<?php

namespace App\DTO\Auth;

readonly class RegisterDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $middleName,
        public string $email,
        public ?string $phone,
        public string $password,
        public string $birthDate,
        public string $municipality,
        public int $startYear,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            middleName: $data['middle_name'] ?? null,
            email: $data['email'],
            phone: $data['phone'] ?? null,
            password: $data['password'],
            birthDate: $data['birth_date'],
            municipality: $data['municipality'],
            startYear: (int) $data['start_year'],
        );
    }
}