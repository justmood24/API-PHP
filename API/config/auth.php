<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../App/Controllers/StudentController.php';
require_once __DIR__ . '/../App/Controllers/TeachingAssistantController.php';
require_once __DIR__ . '/../App/Controllers/SecurityController.php';


header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['email']) && isset($data['password']) && isset($data['user_type'])) {
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        $user_type = $data['user_type'];

        // Authenticate user based on user type
        $db = new Database();
        $conn = $db->getConnection();

        switch ($user_type) {

            case 'student':
                $studentController = new StudentController($conn);
                $student = $studentController->login($email, $password);

                if ($student) {
                    $_SESSION['student_id'] = $student->getId();
                    $_SESSION['email'] = $email;
                    echo json_encode(['success' => true, 'message' => 'Login successful', 'student_id' => $student->getId()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                }
                break;

            case 'teaching_assistant':
                $taController = new TeachingAssistantController($conn);
                $teachingAssistant = $taController->login($email, $password);

                if ($teachingAssistant) {
                    $_SESSION['ta_id'] = $teachingAssistant->getId();
                    $_SESSION['email'] = $email;
                    echo json_encode(['success' => true, 'message' => 'Login successful', 'ta_id' => $teachingAssistant->getId()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                }
                break;

            case 'security':
                $securityController = new SecurityController($conn);
                $security = $securityController->login($email, $password);

                if ($security) {
                    $_SESSION['security_id'] = $security->getId();
                    $_SESSION['email'] = $email;
                    echo json_encode(['success' => true, 'message' => 'Login successful', 'security_id' => $security->getId()]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid user type']);
                break;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'email, password, and user type are required']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
