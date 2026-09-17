<?php
require_once "config.php";

echo "<h2>Database Schema Fixer</h2>";

// Check current structure of questions table
$result = $conn->query("DESCRIBE questions");

echo "<h3>Current 'questions' table structure:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if section_id exists
$check = $conn->query("SHOW COLUMNS FROM questions LIKE 'section_id'");
if ($check->num_rows === 0) {
    echo "<h3>❌ section_id column NOT found!</h3>";
    echo "<p>Adding section_id column...</p>";
    
    // Add section_id column with foreign key constraint
    $alter = "ALTER TABLE questions 
              ADD COLUMN section_id INT NOT NULL DEFAULT 1,
              ADD CONSTRAINT fk_section_id FOREIGN KEY (section_id) 
              REFERENCES sections(section_id) ON DELETE CASCADE";
    
    if ($conn->query($alter)) {
        echo "<h3>✅ SUCCESS! section_id column added!</h3>";
        echo "<p>The questions table now has a section_id column with proper foreign key constraint.</p>";
        
        // Show updated structure
        echo "<h3>Updated 'questions' table structure:</h3>";
        $result2 = $conn->query("DESCRIBE questions");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result2->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3>❌ ERROR adding column: " . $conn->error . "</h3>";
    }
} else {
    echo "<h3>✅ section_id column already exists!</h3>";
}

$conn->close();
?>
