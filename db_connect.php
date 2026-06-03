<?php
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root"; // MySQL username
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ""; // MySQL password
$dbname = getenv('DB_NAME') ?: "ecoswap"; // Database Name
$port = getenv('DB_PORT') ?: "3306"; // MySQL port


// -------------------------------------------------------------------------
// MySQL to SQLite Compatibility / Emulation Layer
// This enables EcoSwap to run seamlessly on SQLite when MySQL is not present.
// -------------------------------------------------------------------------

if (!function_exists('sqlite_escape_string')) {
    function sqlite_escape_string($string) {
        if ($string === null) return '';
        return str_replace("'", "''", $string);
    }
}

if (!function_exists('get_item_image')) {
    function get_item_image($image_url, $category) {
        $clean_url = trim($image_url);
        if (empty($clean_url) || !file_exists(__DIR__ . '/' . $clean_url)) {
            $cat = strtolower(trim($category));
            if (strpos($cat, 'books') !== false) {
                return 'uploads/placeholder-book.svg';
            } elseif (strpos($cat, 'electronics') !== false) {
                return 'uploads/placeholder-headphones.svg';
            } elseif (strpos($cat, 'stationery') !== false) {
                return 'uploads/placeholder-cup.svg';
            } elseif (strpos($cat, 'clothing') !== false) {
                return 'uploads/placeholder-clothing.svg';
            } elseif (strpos($cat, 'dorm') !== false) {
                return 'uploads/placeholder-lamp.svg';
            } else {
                return 'uploads/placeholder-lamp.svg';
            }
        }
        return $clean_url;
    }
}

if (!function_exists('contains_bad_words')) {
    function contains_bad_words($text) {
        if ($text === null || !is_string($text)) return false;
        $bad_words = [
            'badword', 'abuse', 'spam', 'scam', 'offensive', 'trash', 
            'curseword', 'inappropriate', 'fraud', 'sh*t', 'f*ck', 
            'b*tch', 'asshole', 'bastard', 'crap', 'dick', 'piss'
        ];
        $text_lower = strtolower($text);
        foreach ($bad_words as $word) {
            if (strpos($text_lower, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}


class SQLitei_result {
    private $rows;
    private $current = 0;
    public $num_rows = 0;

    public function __construct($rows) {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    public function fetch_assoc() {
        if ($this->current >= count($this->rows)) {
            return null;
        }
        $row = $this->rows[$this->current++];
        $assoc = [];
        foreach ($row as $key => $val) {
            if (!is_int($key)) {
                $assoc[$key] = $val;
            }
        }
        return $assoc;
    }
}

class SQLitei_stmt {
    private $stmt;
    private $conn;
    private $params = [];
    private $bind_results = [];
    private $results = [];
    private $current_row = 0;
    public $num_rows = 0;
    public $error = '';

    public function __construct($stmt, $conn) {
        $this->stmt = $stmt;
        $this->conn = $conn;
    }

    public function bind_param($types, &...$params) {
        $this->params = [];
        foreach ($params as &$param) {
            $this->params[] = &$param;
        }
        return true;
    }

    public function execute() {
        try {
            foreach ($this->params as $i => &$val) {
                // PDO parameters are 1-indexed
                $this->stmt->bindParam($i + 1, $val);
            }
            $res = $this->stmt->execute();
            if (!$res) {
                $err = $this->stmt->errorInfo();
                $this->error = $err[2];
                return false;
            }
            
            // Check if this was a SELECT query
            if ($this->stmt->columnCount() > 0) {
                $this->results = $this->stmt->fetchAll(PDO::FETCH_BOTH);
                $this->num_rows = count($this->results);
            } else {
                $this->results = [];
                $this->num_rows = 0;
                $this->conn->insert_id = $this->conn->getPdo()->lastInsertId();
            }
            
            $this->current_row = 0;
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function store_result() {
        return true;
    }

    public function get_result() {
        return new SQLitei_result($this->results);
    }

    public function bind_result(&...$vars) {
        $this->bind_results = [];
        foreach ($vars as &$var) {
            $this->bind_results[] = &$var;
        }
        return true;
    }

    public function fetch() {
        if ($this->current_row >= count($this->results)) {
            return false;
        }
        $row = $this->results[$this->current_row++];
        foreach ($this->bind_results as $i => &$var) {
            if (array_key_exists($i, $row)) {
                $var = $row[$i];
            } else {
                $var = null;
            }
        }
        return true;
    }

    public function close() {
        return true;
    }
}

class SQLitei {
    private $pdo;
    public $connect_error = null;
    public $error = '';
    public $insert_id = 0;

    public function __construct($servername, $username, $password, $dbname) {
        try {
            $db_file = __DIR__ . '/ecoswap.db';
            if (getenv('VERCEL') || !is_writable(dirname($db_file)) || (file_exists($db_file) && !is_writable($db_file))) {
                $temp_db = '/tmp/ecoswap.db';
                if (!file_exists($temp_db) && file_exists($db_file)) {
                    @copy($db_file, $temp_db);
                }
                $db_file = $temp_db;
            }
            $is_new = !file_exists($db_file);
            $this->pdo = new PDO("sqlite:" . $db_file);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable Foreign Keys in SQLite
            $this->pdo->exec("PRAGMA foreign_keys = ON;");

            if ($is_new) {
                $this->initialize_db();
            } else {
                $this->ensure_new_tables();
            }
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    private function ensure_new_tables() {
        $queries = [
            "CREATE TABLE IF NOT EXISTS ratings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                swap_id INTEGER NOT NULL,
                reviewer_id INTEGER NOT NULL,
                reviewee_id INTEGER NOT NULL,
                rating INTEGER CHECK(rating >= 1 AND rating <= 5) NOT NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (swap_id) REFERENCES swaps (id) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE CASCADE,
                FOREIGN KEY (reviewee_id) REFERENCES users (id) ON DELETE CASCADE,
                UNIQUE(swap_id, reviewer_id)
            )"
        ];
        foreach ($queries as $q) {
            $this->pdo->exec($q);
        }
    }

    private function initialize_db() {
        $queries = [
            "CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                category VARCHAR(255),
                condition VARCHAR(50),
                image_url VARCHAR(255),
                user_id INTEGER,
                status TEXT CHECK(status IN ('available','pending','swapped')) DEFAULT 'available',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )",
            "CREATE TABLE swaps (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item1_id INTEGER NOT NULL,
                item2_id INTEGER NOT NULL,
                item1_owner_id INTEGER NOT NULL,
                item2_owner_id INTEGER NOT NULL,
                status TEXT CHECK(status IN ('pending','accepted','completed','declined')) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (item1_id) REFERENCES items (id) ON DELETE CASCADE,
                FOREIGN KEY (item2_id) REFERENCES items (id) ON DELETE CASCADE
            )",
            "CREATE TABLE messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                swap_id INTEGER,
                sender_id INTEGER NOT NULL,
                receiver_id INTEGER NOT NULL,
                message_text TEXT NOT NULL,
                sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (swap_id) REFERENCES swaps (id) ON DELETE CASCADE,
                FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE
            )",
            "CREATE TABLE ratings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                swap_id INTEGER NOT NULL,
                reviewer_id INTEGER NOT NULL,
                reviewee_id INTEGER NOT NULL,
                rating INTEGER CHECK(rating >= 1 AND rating <= 5) NOT NULL,
                comment TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (swap_id) REFERENCES swaps (id) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE CASCADE,
                FOREIGN KEY (reviewee_id) REFERENCES users (id) ON DELETE CASCADE,
                UNIQUE(swap_id, reviewer_id)
            )"
        ];

        foreach ($queries as $q) {
            $this->pdo->exec($q);
        }

        // Insert mock data matching ecoswap.sql
        $users_seed = [
            [1, 'raven', 'dharvityagi@gmail.com', '$2y$10$oKOxVudEY.ml.rdGgsQ4gu0ZAsAYNCamEyNxJANPpdBY0UIJEg7Mq', '2025-10-03 16:27:28'],
            [5, 'honey', '123@gmail.com', '$2y$10$H6lyIXGYifSJek9iKPV.GeNU3tkmgtynrfobCw/VXW72/d1DGAhC6', '2025-10-03 16:29:35'],
            [8, 'dracula', 'honey123@gmail.com', '$2y$10$6umEFLkLvdwUOZNEiQVMlO1Oit0GLfbiks4/bFlMsm0hrQjPXVyk6', '2025-10-26 06:40:17'],
            [9, 'Shalvi', 'shalvi123@gmail.com', '$2y$10$VEVpJyjpHpGj7rIlDxCkCeRWKB8tVPZ58nd6uHehhzMlBkLBv8Xmm', '2025-12-09 12:50:00']
        ];
        foreach ($users_seed as $u) {
            $stmt = $this->pdo->prepare("INSERT INTO users (id, username, email, password_hash, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($u);
        }

        $items_seed = [
            [1, 'Pink Ceramic Cup', 'A beautiful minimalist aesthetic cup, perfect for morning coffee or studio vibes.', 'Stationery', 'New', 'uploads/placeholder-cup.svg', 1, 'swapped', '2025-10-03 17:12:41'],
            [2, 'Harry Potter Deluxe Edition', 'Complete hardcover collection of all books, stored carefully.', 'Books', 'New', 'uploads/placeholder-book.svg', 8, 'swapped', '2025-10-26 09:37:50'],
            [3, 'Minimalist Desk Lamp', 'Sleek, warm LED lamp with adjustable brightness, ideal for late study hours.', 'Dorm', 'Like New', 'uploads/placeholder-lamp.svg', 8, 'available', '2025-10-26 09:37:54']
        ];
        foreach ($items_seed as $it) {
            $stmt = $this->pdo->prepare("INSERT INTO items (id, name, description, category, condition, image_url, user_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($it);
        }

        $swaps_seed = [
            [1, 1, 2, 1, 8, 'accepted', '2025-10-26 09:45:08'],
            [2, 2, 1, 8, 1, 'accepted', '2025-10-26 10:14:42'],
            [3, 1, 2, 1, 8, 'accepted', '2025-10-26 10:24:13'],
            [4, 2, 1, 8, 1, 'pending', '2025-10-26 10:25:36'],
            [5, 3, 1, 8, 1, 'pending', '2025-10-26 10:44:25'],
            [6, 3, 1, 8, 1, 'pending', '2025-12-09 13:05:13'],
            [7, 3, 1, 8, 1, 'pending', '2025-12-09 13:06:01']
        ];
        foreach ($swaps_seed as $sw) {
            $stmt = $this->pdo->prepare("INSERT INTO swaps (id, item1_id, item2_id, item1_owner_id, item2_owner_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($sw);
        }

        $messages_seed = [
            [1, 7, 1, 8, 'hi', '2025-12-09 13:06:21']
        ];
        foreach ($messages_seed as $m) {
            $stmt = $this->pdo->prepare("INSERT INTO messages (id, swap_id, sender_id, receiver_id, message_text, sent_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($m);
        }
    }

    public function prepare($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return new SQLitei_stmt($stmt, $this);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            if (!$stmt) {
                $err = $this->pdo->errorInfo();
                $this->error = $err[2];
                return false;
            }
            return new SQLitei_result($stmt->fetchAll(PDO::FETCH_BOTH));
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function real_escape_string($str) {
        return sqlite_escape_string($str);
    }

    public function escape_string($str) {
        return $this->real_escape_string($str);
    }

    public function begin_transaction() {
        return $this->pdo->beginTransaction();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }

    public function close() {
        $this->pdo = null;
        return true;
    }
}

// -------------------------------------------------------------------------
// Connection Instantiation
// -------------------------------------------------------------------------

$conn = null;
if (class_exists('mysqli', false)) {
    try {
        $conn = @new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $conn = null;
        }
    } catch (Exception $e) {
        $conn = null;
    }
}

if ($conn === null) {
    $conn = new SQLitei($servername, $username, $password, $dbname);
}
?>