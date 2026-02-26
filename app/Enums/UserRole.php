<?php

    namespace App\Enums;

    enum UserRole: string
    {
        case SUPER_ADMIN = 'super_admin';
        case ADMIN = 'admin';
        case USER = 'user';

        /**
         * Get a human-readable label if needed
         */
        public function label(): string
        {
            return match($this) {
                self::SUPER_ADMIN => 'Super Administrator',
                self::ADMIN => 'Administrator',
                self::USER => 'Regular User',
            };
        }
    }