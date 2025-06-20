<?php
// Database connection include
include '../../config/connection.php';
include '../../config/utilities/intro.php';
include '../../config/utilities/encryption.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and clean input data
    $team_name = cleanme($_POST['team_name']);
    $team_leader = cleanme($_POST['team_leader']);
    $members = $_POST['members']; // Array of members

    // Validate team information
    if (empty($team_name) || empty($team_leader) || count($members) < 2 || count($members) > 5) {
        echo json_encode(array("status" => false, "message" => "Invalid team data provided."));
        exit;
    }

    // Prepare SQL to insert team data
    $sql = "INSERT INTO teams (team_name, team_leader, member_name, member_email) VALUES (?, ?, ?)";
    
    // Serialize member details to store as a single field
    $member_details = serialize($members);

    foreach ($members as $member) {
        $member_name = cleanme($member['name']);
        $member_email = cleanme($member['email']);

        $stmt->bind_param("ssss", $team_name, $team_leader, $member_name, $member_email);
        
        if (!$stmt->execute()) {
            echo json_encode(array("status" => false, "message" => "Failed to register member: " . $member_name));
            $stmt->close();
            exit;
        }
    }

    $stmt->close();

    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param("sss", $team_name, $team_leader, $member_details);
        
        if ($stmt->execute()) {
            echo json_encode(array("status" => true, "message" => "Team registered successfully."));
        } else {
            echo json_encode(array("status" => false, "message" => "Failed to register team."));
        }
        
        $stmt->close();
    } else {
        echo json_encode(array("status" => false, "message" => "Database error."));
    }
} else {
    echo json_encode(array("status" => false, "message" => "Invalid request method."));
}
?>
