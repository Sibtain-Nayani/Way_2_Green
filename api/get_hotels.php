<?php
// api/get_hotels.php - Fetch hotels dynamically from MySQL with accessibility filters
header('Content-Type: application/json');
require_once '../db.php';

$dest_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : 0;
$dest_name = isset($_GET['destination_name']) ? trim($_GET['destination_name']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

try {
    $params = [];
    $sql = "SELECT h.*, d.name AS destination_name, d.latitude, d.longitude 
            FROM hotels h 
            JOIN destinations d ON h.destination_id = d.id 
            WHERE 1=1";

    if ($dest_id > 0) {
        $sql .= " AND h.destination_id = ?";
        $params[] = $dest_id;
    } elseif (!empty($dest_name)) {
        $sql .= " AND d.name LIKE ?";
        $params[] = "%$dest_name%";
    }

    if ($filter !== 'all' && !empty($filter)) {
        if ($filter === 'wheelchair') {
            $sql .= " AND (h.accessibility_tags LIKE '%wheelchair%' OR h.accessibility_tags LIKE '%step-free%' OR h.accessibility_tags LIKE '%accessible%' OR h.accessibility_tags LIKE '%ramp%' OR h.accessibility_tags LIKE '%doorway%' OR h.accessibility_tags LIKE '%roll-in%')";
        } elseif ($filter === 'sensory') {
            $sql .= " AND (h.accessibility_tags LIKE '%sensory%' OR h.accessibility_tags LIKE '%quiet%' OR h.accessibility_tags LIKE '%low stimulation%' OR h.accessibility_tags LIKE '%calm%')";
        } elseif ($filter === 'braille') {
            $sql .= " AND (h.accessibility_tags LIKE '%braille%' OR h.accessibility_tags LIKE '%audio%' OR h.accessibility_tags LIKE '%tactile%' OR h.accessibility_tags LIKE '%strobe%')";
        } elseif ($filter === 'dog') {
            $sql .= " AND (h.accessibility_tags LIKE '%dog%' OR h.accessibility_tags LIKE '%service animal%' OR h.accessibility_tags LIKE '%guide dog%')";
        }
    }

    $sql .= " ORDER BY h.eco_rating DESC, h.water_saved_liters DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'count' => count($hotels),
        'hotels' => $hotels
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve hotels: ' . $e->getMessage()
    ]);
}
?>
