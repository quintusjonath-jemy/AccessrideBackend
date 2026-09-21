<?php
/**
 * IdHelper — Secure Prefixed Identifier Utility for AccessRide
 *
 * Translates between internal database integer IDs and secure client-facing prefixed IDs.
 * Examples:
 *   User:         1 -> "ur_1"       | "ur_1" -> 1
 *   Driver:       1 -> "dr_1"       | "dr_1" -> 1
 *   Admin:        1 -> "adm_1"      | "adm_1" -> 1
 *   Ride:         1 -> "rd_1"       | "rd_1" -> 1
 *   Payment:      1 -> "pay_1"      | "pay_1" -> 1
 *   Subscription: 1 -> "sub_1"      | "sub_1" -> 1
 */

class IdHelper {
    public const PREFIX_USER         = 'ur';
    public const PREFIX_DRIVER       = 'dr';
    public const PREFIX_ADMIN        = 'adm';
    public const PREFIX_RIDE         = 'rd';
    public const PREFIX_PAYMENT      = 'pay';
    public const PREFIX_SUBSCRIPTION = 'sub';

    /**
     * Encode an integer ID with a specific prefix.
     */
    public static function encode(?int $id, string $prefix): ?string {
        if ($id === null || $id <= 0) {
            return null;
        }
        return "{$prefix}_{$id}";
    }

    /**
     * Decode a prefixed string ID (or raw integer string) into a clean integer ID.
     */
    public static function decode($value, ?string $expectedPrefix = null): ?int {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        $str = trim((string)$value);
        if ($str === '' || $str === 'null' || $str === 'undefined') {
            return null;
        }

        // If it's already a numeric string (e.g. "12")
        if (is_numeric($str)) {
            $num = (int)$str;
            return $num > 0 ? $num : null;
        }

        // If it contains prefix with underscore (e.g. "ur_12", "dr_5")
        if (strpos($str, '_') !== false) {
            $parts = explode('_', $str, 2);
            $prefix = $parts[0];
            $numStr = $parts[1] ?? '';

            if (is_numeric($numStr)) {
                return (int)$numStr;
            }
        }

        // Regex fallback: extract trailing numbers (e.g. "ur12", "dr5")
        if (preg_match('/(\d+)$/', $str, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    // --- User ID Helpers ---
    public static function encodeUser(?int $id): ?string {
        return self::encode($id, self::PREFIX_USER);
    }
    public static function decodeUser($value): ?int {
        return self::decode($value, self::PREFIX_USER);
    }

    // --- Driver ID Helpers ---
    public static function encodeDriver(?int $id): ?string {
        return self::encode($id, self::PREFIX_DRIVER);
    }
    public static function decodeDriver($value): ?int {
        return self::decode($value, self::PREFIX_DRIVER);
    }

    // --- Admin ID Helpers ---
    public static function encodeAdmin(?int $id): ?string {
        return self::encode($id, self::PREFIX_ADMIN);
    }
    public static function decodeAdmin($value): ?int {
        return self::decode($value, self::PREFIX_ADMIN);
    }

    // --- Ride ID Helpers ---
    public static function encodeRide(?int $id): ?string {
        return self::encode($id, self::PREFIX_RIDE);
    }
    public static function decodeRide($value): ?int {
        return self::decode($value, self::PREFIX_RIDE);
    }

    // --- Payment ID Helpers ---
    public static function encodePayment(?int $id): ?string {
        return self::encode($id, self::PREFIX_PAYMENT);
    }
    public static function decodePayment($value): ?int {
        return self::decode($value, self::PREFIX_PAYMENT);
    }

    // --- Subscription ID Helpers ---
    public static function encodeSubscription(?int $id): ?string {
        return self::encode($id, self::PREFIX_SUBSCRIPTION);
    }
    public static function decodeSubscription($value): ?int {
        return self::decode($value, self::PREFIX_SUBSCRIPTION);
    }
}
