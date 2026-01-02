<?php
/**
 * Epistora DB Engine - Optimized for Multi-language (Bangla/Arabic/Hindi)
 */

require_once 'config.php';

// Force UTF-8 internal encoding for multibyte character safety
mb_internal_encoding("UTF-8");

if (!defined('DATA_PATH')) {
    die("Core Engine Error: Configuration constants not loaded.");
}

class DBEngine {

    private static function ensureStorage() {
        $folders = [DATA_PATH, USER_DATA_PATH, POST_CONTENT_PATH];
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
        }
    }

    public static function readJSON($filename) {
        $path = DATA_PATH . $filename;
        if (!file_exists($path)) return null;

        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        // If JSON is malformed (often due to encoding issues), return null
        if (json_last_error() !== JSON_ERROR_NONE) return null;

        return $decoded;
    }

    public static function writeJSON($filename, $data) {
        self::ensureStorage();
        $path = DATA_PATH . $filename;

        /**
         * FIX: JSON_UNESCAPED_UNICODE keeps Bangla characters readable.
         * FIX: JSON_INVALID_UTF8_SUBSTITUTE prevents the whole save from failing if one char is broken.
         */
        $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json_string === false) return false;

        return file_put_contents($path, $json_string, LOCK_EX);
    }

    public static function initVault($user_id) {
        $filename = "user_data/" . $user_id . ".json";
        if (file_exists(DATA_PATH . $filename)) return false;

        $vault_template = [
            "user_id"    => $user_id,
            "role"       => ROLE_USER,
            "created_at" => date('Y-m-d H:i:s'),
            "profile"    => ["name" => "", "email" => ""],
            "settings"   => ["bg_color" => "#ffffff", "font_style" => "sans-serif", "font_size" => "16px"],
            "history"    => [],
            "following"  => [],
            "notifications" => []
        ];
        return self::writeJSON($filename, $vault_template);
    }

    public static function updateKey($filename, $key, $value) {
        $data = self::readJSON($filename);
        if ($data !== null) {
            $data[$key] = $value;
            return self::writeJSON($filename, $data);
        }
        return false;
    }

    public static function pushNotification($target_user_id, $type, $from_name, $post_id) {
        $filename = "user_data/" . $target_user_id . ".json";
        $vault = self::readJSON($filename);
        if ($vault) {
            $notification = [
                "id"         => uniqid('ntf_'),
                "type"       => $type,
                "from_name"  => $from_name,
                "post_id"    => $post_id,
                "is_read"    => false,
                "timestamp"  => time(),
                "date_human" => date('M d, H:i')
            ];
            array_unshift($vault['notifications'], $notification);
            $vault['notifications'] = array_slice($vault['notifications'], 0, 50);
            return self::writeJSON($filename, $vault);
        }
        return false;
    }

    public static function logAction($admin_id, $admin_name, $action, $details) {
        $log_file = "system_logs.json";
        $logs = self::readJSON($log_file) ?? [];
        $new_log = [
            "id" => uniqid('log_'), "timestamp" => time(), "date" => date('Y-m-d H:i:s'),
            "admin_id" => $admin_id, "admin_name" => $admin_name, "action" => $action,
            "details" => $details, "ip" => $_SERVER['REMOTE_ADDR']
        ];
        array_unshift($logs, $new_log);
        if (count($logs) > 1000) $logs = array_slice($logs, 0, 1000);
        return self::writeJSON($log_file, $logs);
    }
}