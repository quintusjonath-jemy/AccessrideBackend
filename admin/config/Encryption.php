<?php
class Encryption
{
    private static $key = "AccessRideSystemSecureKey2026!"; // Secret key for AES-256-CBC
    private static $cipher = "AES-256-CBC";

    public static function encrypt($plaintext)
    {
        if (empty($plaintext)) return "";
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, self::$key, true);
        return base64_encode($iv . $hmac . $ciphertext_raw);
    }

    public static function decrypt($ciphertext)
    {
        if (empty($ciphertext)) return "";
        $c = base64_decode($ciphertext);
        if ($c === false) return "";
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, 32);
        $ciphertext_raw = substr($c, $ivlen + 32);
        $original_plaintext = openssl_decrypt($ciphertext_raw, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, self::$key, true);
        if (hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        }
        return "";
    }
}
?>
