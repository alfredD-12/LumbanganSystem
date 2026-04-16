<?php

class MysqlRegressionTestDatabase
{
    public static function recreate()
    {
        $pdo = MysqlAuthTestDatabase::recreate();

        self::runSqlFile($pdo, __DIR__ . '/../../db/test_regression_schema.sql');
        self::seedLookupRows($pdo);

        return $pdo;
    }

    public static function seedDocumentType(PDO $pdo, array $overrides = [])
    {
        $data = array_merge([
            'category_id' => 1,
            'document_name' => 'Barangay Clearance',
            'description' => null,
            'requirements' => null,
            'fee' => 0.00,
        ], $overrides);

        $stmt = $pdo->prepare(
            "INSERT INTO document_types (category_id, document_name, description, requirements, fee)
             VALUES (:category_id, :document_name, :description, :requirements, :fee)"
        );

        $stmt->execute([
            ':category_id' => (int) $data['category_id'],
            ':document_name' => $data['document_name'],
            ':description' => $data['description'],
            ':requirements' => $data['requirements'],
            ':fee' => $data['fee'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function seedGalleryItems(PDO $pdo, $n = 2)
    {
        $n = (int) $n;
        if ($n < 1) {
            return [];
        }

        $stmt = $pdo->prepare(
            "INSERT INTO gallery (title, description, image_path, display_order, is_active)
             VALUES (:title, :description, :image_path, :display_order, :is_active)"
        );

        $ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $stmt->execute([
                ':title' => 'Seed ' . $i,
                ':description' => 'Seed item ' . $i,
                ':image_path' => 'seed_' . $i . '.jpg',
                ':display_order' => $i,
                ':is_active' => 1,
            ]);
            $ids[] = (int) $pdo->lastInsertId();
        }

        return $ids;
    }

    private static function seedLookupRows(PDO $pdo)
    {
        $pdo->exec("INSERT INTO statuses (id, label) VALUES (1, 'Pending')
                    ON DUPLICATE KEY UPDATE label = VALUES(label)");
        $pdo->exec("INSERT INTO statuses (id, label) VALUES (2, 'Investigating')
                    ON DUPLICATE KEY UPDATE label = VALUES(label)");
        $pdo->exec("INSERT INTO statuses (id, label) VALUES (3, 'Resolved')
                    ON DUPLICATE KEY UPDATE label = VALUES(label)");

        $pdo->exec("INSERT INTO case_types (id, label) VALUES (1, 'General')
                    ON DUPLICATE KEY UPDATE label = VALUES(label)");

        $pdo->exec("INSERT INTO document_categories (category_id, category_name) VALUES (1, 'General')
                    ON DUPLICATE KEY UPDATE category_name = VALUES(category_name)");
    }

    private static function runSqlFile(PDO $pdo, $path)
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Unable to read SQL file: ' . $path);
        }

        $statement = '';
        foreach (preg_split("/\R/", $sql) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $statement .= $line . "\n";
            if (substr(rtrim($trimmed), -1) === ';') {
                $pdo->exec($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
}

