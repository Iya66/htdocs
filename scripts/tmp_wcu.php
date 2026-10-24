<?php
require dirname(__DIR__) . '/app/config.php';

$new = 'Over 4+ successful digital initiatives with measurable ROI across e-commerce, healthcare, and SaaS.';

$stmt = $conn->prepare("UPDATE why_choose_us SET answer = ? WHERE question = 'Proven Track Record'");
$stmt->execute([$new]);

echo "Updated rows: " . $stmt->rowCount() . PHP_EOL;
foreach ($conn->query("SELECT id, question, answer FROM why_choose_us WHERE question = 'Proven Track Record' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  id " . str_pad($r['id'], 4) . $r['answer'] . PHP_EOL;
}
