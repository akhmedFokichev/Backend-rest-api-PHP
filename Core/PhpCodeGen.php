<?php
class PhpCodeGen {
    // версия 1.0 (041124)
    private $conn;
    private $tableNames;
    private $outputDir;

    public function __construct($prefix = "class_", $outputDir = "../src/Class") {
        $config = new Config();
        $host = $config->host;
        $dbName = $config->db_name;
        $username = $config->username;
        $password = $config->password;

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->tableNames = $this->getTablesWithPrefix($prefix);
            $this->outputDir = rtrim($outputDir, '/') . '/';

            // Создаем директорию, если она не существует
            if (!is_dir($this->outputDir)) {
                mkdir($this->outputDir, 0777, true);
            }
        } catch (PDOException $e) {
            echo "Ошибка подключения: " . $e->getMessage();
        }
    }

    // Получаем таблицы с заданным префиксом
    private function getTablesWithPrefix($prefix) {
        $tables = [];
        try {
            $stmt = $this->conn->prepare("SHOW TABLES LIKE :prefix");
            $stmt->execute([':prefix' => $prefix . '%']);
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        } catch (PDOException $e) {
            echo "Ошибка при получении таблиц: " . $e->getMessage();
        }
        return $tables;
    }

    // Генерация классов для всех таблиц
    public function generateClasses() {
        foreach ($this->tableNames as $tableName) {
            $className = $this->toCamelCase(str_replace('class_', '', $tableName));
            $classCode = $this->generateClass($tableName, $className);
            $fileName = $this->outputDir . $className . ".php";
            file_put_contents($fileName, $classCode);
            echo "Класс для таблицы $tableName успешно сгенерирован в $fileName\n";
        }
    }

    // Преобразование имени из snake_case в CamelCase
    private function toCamelCase($string) {
        $string = str_replace('_', ' ', strtolower($string));
        return str_replace(' ', '', ucwords($string));
    }

    // Получаем структуру таблицы
    private function getTableStructure($tableName) {
        $stmt = $this->conn->prepare("DESCRIBE " . $tableName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Генерация класса для одной таблицы
    private function generateClass($tableName, $className) {
        $structure = $this->getTableStructure($tableName);
        $classCode = "<?php\n\nclass $className {\n";

        // Добавляем свойства для каждого столбца
        foreach ($structure as $column) {
            $classCode .= "    public $" . $this->toCamelCase($column['Field']) . ";\n";
        }

        // Добавляем свойство для подключения к базе данных
        $classCode .= "\n    private \$conn;\n";

        // Конструктор класса
        $classCode .= "    public function __construct(\$db) {\n";
        $classCode .= "        \$this->conn = \$db;\n    }\n\n";

        // Генерация метода Save
        $classCode .= $this->generateSaveMethod($structure, $tableName);

        // Генерация метода Delete
        $classCode .= $this->generateDeleteMethod($structure, $tableName);

        // Добавляем статические методы (например, getAll с фильтром)
        $classCode .= $this->generateStaticMethods($structure, $tableName);

        $classCode .= "}\n\n?>";

        return $classCode;
    }

    // Генерация метода Save
    private function generateSaveMethod($structure, $tableName) {
        $primaryKey = $structure[0]['Field'];
        $fields = array_column($structure, 'Field');
        
        $saveMethod = "    public function save() {\n";
        $saveMethod .= "        if (isset(\$this->" . $this->toCamelCase($primaryKey) . ") && !empty(\$this->" . $this->toCamelCase($primaryKey) . ")) {\n";
        $saveMethod .= "            // Если " . $this->toCamelCase($primaryKey) . " установлен, выполняем обновление\n";
        
        // Генерация запроса для обновления
        $saveMethod .= "            \$query = \"UPDATE $tableName SET ";
        $updateFields = array_map(fn($col) => "$col = :$col", array_diff($fields, [$primaryKey]));
        $saveMethod .= implode(", ", $updateFields) . " WHERE $primaryKey = :$primaryKey\";\n";
        
        $saveMethod .= "            \$stmt = \$this->conn->prepare(\$query);\n";
        
        foreach ($fields as $field) {
            $saveMethod .= "            \$stmt->bindParam(':" . $field . "', \$this->" . $this->toCamelCase($field) . ");\n";
        }

        $saveMethod .= "        } else {\n";
        $saveMethod .= "            // Если " . $this->toCamelCase($primaryKey) . " не установлен, выполняем вставку новой записи\n";
        
        // Генерация запроса для вставки
        $insertFields = implode(", ", array_diff($fields, [$primaryKey]));
        $insertParams = ":" . implode(", :", array_diff($fields, [$primaryKey]));
        
        $saveMethod .= "            \$query = \"INSERT INTO $tableName ($insertFields) VALUES ($insertParams)\";\n";
        $saveMethod .= "            \$stmt = \$this->conn->prepare(\$query);\n";

        foreach (array_diff($fields, [$primaryKey]) as $field) {
            $saveMethod .= "            \$stmt->bindParam(':" . $field . "', \$this->" . $this->toCamelCase($field) . ");\n";
        }

        // Получаем ID, если это была вставка
        $saveMethod .= "            if (\$stmt->execute()) {\n";
        $saveMethod .= "                \$this->" . $this->toCamelCase($primaryKey) . " = \$this->conn->lastInsertId();\n";
        $saveMethod .= "            }\n";
        $saveMethod .= "        }\n";

        $saveMethod .= "        return \$stmt->rowCount() > 0;\n    }\n\n";

        return $saveMethod;
    }

    // Генерация метода Delete
    private function generateDeleteMethod($structure, $tableName) {
        $primaryKey = $structure[0]['Field'];
        $primaryKeyCamel = $this->toCamelCase($primaryKey);

        $deleteMethod = "    public function delete() {\n";
        $deleteMethod .= "        if (isset(\$this->$primaryKeyCamel) && !empty(\$this->$primaryKeyCamel)) {\n";
        $deleteMethod .= "            \$query = \"DELETE FROM $tableName WHERE $primaryKey = :$primaryKey\";\n";
        $deleteMethod .= "            \$stmt = \$this->conn->prepare(\$query);\n";
        $deleteMethod .= "            \$stmt->bindParam(':" . $primaryKey . "', \$this->$primaryKeyCamel);\n";
        $deleteMethod .= "            return \$stmt->execute();\n";
        $deleteMethod .= "        }\n";
        $deleteMethod .= "        return false;\n";
        $deleteMethod .= "    }\n\n";

        return $deleteMethod;
    }

    // Генерация статических методов
    private function generateStaticMethods($structure, $tableName) {
        $className = $this->toCamelCase($tableName);
        $staticMethods = "";

        // Метод getAll с фильтром по полю
        $staticMethods .= "    public static function getAll(\$db, \$filterField = null, \$filterValue = null) {\n";
        $staticMethods .= "        \$query = \"SELECT * FROM $tableName\";\n";
        $staticMethods .= "        if (\$filterField && \$filterValue) {\n";
        $staticMethods .= "            \$query .= \" WHERE \$filterField = :filterValue\";\n";
        $staticMethods .= "        }\n";
        $staticMethods .= "        \$stmt = \$db->prepare(\$query);\n";
        $staticMethods .= "        if (\$filterField && \$filterValue) {\n";
        $staticMethods .= "            \$stmt->bindParam(':filterValue', \$filterValue);\n";
        $staticMethods .= "        }\n";
        $staticMethods .= "        \$stmt->execute();\n\n";
        $staticMethods .= "        \$items = [];\n";
        $staticMethods .= "        while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {\n";
        $staticMethods .= "            \$item = new self(\$db);\n";
        foreach ($structure as $column) {
            $staticMethods .= "            \$item->" . $this->toCamelCase($column['Field']) . " = \$row['" . $column['Field'] . "'];\n";
        }
        $staticMethods .= "            \$items[] = \$item;\n";
        $staticMethods .= "        }\n\n";
        $staticMethods .= "        return \$items;\n    }\n\n";

        // Метод getAllWithDate для фильтрации по дате
        $staticMethods .= "    public static function getAllWithDate(\$db, \$dateField, \$startDate, \$endDate) {\n";
        $staticMethods .= "        \$query = \"SELECT * FROM $tableName WHERE \$dateField BETWEEN :startDate AND :endDate\";\n";
        $staticMethods .= "        \$stmt = \$db->prepare(\$query);\n";
        $staticMethods .= "        \$stmt->bindParam(':startDate', \$startDate);\n";
        $staticMethods .= "        \$stmt->bindParam(':endDate', \$endDate);\n";
        $staticMethods .= "        \$stmt->execute();\n\n";
        $staticMethods .= "        \$items = [];\n";
        $staticMethods .= "        while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {\n";
        $staticMethods .= "            \$item = new self(\$db);\n";
        foreach ($structure as $column) {
            $staticMethods .= "            \$item->" . $this->toCamelCase($column['Field']) . " = \$row['" . $column['Field'] . "'];\n";
        }
        $staticMethods .= "            \$items[] = \$item;\n";
        $staticMethods .= "        }\n\n";
        $staticMethods .= "        return \$items;\n    }\n\n";

        return $staticMethods;
    }
}

// Пример использования
// $generator = new PhpCodeGen("class_", "../src/Class");
// $generator->generateClasses();

?>
