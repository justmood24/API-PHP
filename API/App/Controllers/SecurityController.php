<?php

require_once __DIR__ . '/../Models/Security.php';

class SecurityController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db->getConnection();
    }


    //All Security Personnel
    public function index()
    {
        try {
            $securityPersonnel = Security::getAll($this->db);
            return json_encode(['success' => true, 'data' => $securityPersonnel]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    //Get Security by ID 
    public function show($id)
    {
        try {
            $securityPersonnel = Security::getById($this->db, $id);
            if ($securityPersonnel) {
                return json_encode(['success' => true, 'data' => $securityPersonnel]);
            } else {
                return json_encode(['success' => false, 'error' => 'Security personnel not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function store($data)
    {
        try {
            // Ensure required data is provided
            if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
                throw new Exception("Name, email, and password are required");
            }

            // Create new Security instance with hashed password
            $security = new Security($this->db, null, $data['name'], $data['email'], $data['password']);
            $result = $security->save();
            return $result;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    // Login method to authenticate user
    public function login($email, $password)
    {
        try {
            // Authenticate user using the provided name and password
            $security = Security::login($this->db, $email, $password);
            return $security;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error in login' => $e->getMessage()]);
        }
    }
    public function update($id, $data)
    {
        try {
            $security = Security::getById($this->db, $id);
            if ($security) {
                $security->setName($data['name']);
                $security->setEmail($data['email']);
                $security->update();
                return json_encode(['success' => true, 'message' => 'Security entry updated successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Security entry not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $security = Security::getById($this->db, $id);
            if ($security) {
                $security->destroy();
                return json_encode(['success' => true, 'message' => 'Security entry deleted successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Security entry not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function accessControl($qrCode)
{
    try {
        $sql = "SELECT * FROM student WHERE qr_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $qrCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $studentData = $result->fetch_assoc();
            $response = [
                "status" => "success",
                "message" => "Entering granted to student: " . $studentData['name']
            ];
            return json_encode($response);
        } else {
            $response = [
                "status" => "error",
                "message" => "Invalid QR code"
            ];
            return json_encode($response);
        }
    } catch (Exception $e) {
        // Handle any exceptions that occur during database operations
        return json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
}

}
