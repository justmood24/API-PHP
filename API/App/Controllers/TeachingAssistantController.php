<?php

require_once __DIR__ . '/../Models/TeachingAssistant.php';

class TeachingAssistantController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db->getConnection();
    }

    public function index()
    {
        try {
            $tas = TeachingAssistant::getAll($this->db);
            return json_encode(['success' => true, 'data' => $tas]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $ta = TeachingAssistant::getById($this->db, $id);
            if ($ta) {
                return json_encode(['success' => true, 'data' => $ta]);
            } else {
                return json_encode(['success' => false, 'error' => 'Teaching Assistant not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    // Login method to authenticate user
    public function login($email, $password)
    {
        try {
            // Authenticate user using the provided name and password
            $ta = TeachingAssistant::login($this->db, $email, $password);
            return $ta;
        } catch (Exception $e) {
            
            return json_encode(['success' => false, 'error in login' => $e->getMessage()]);
        }
    }

    public function store($data)
    {
        try {
            //Ensure required data is provided
            if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
                throw new Exception("Name, email, and password are required");
            }
            // Create new Teaching Assistant 
            $ta = new TeachingAssistant($this->db, null, $data['name'], $data['email'], $data['password']);
            $ta->save();
            return json_encode(['success' => true, 'message' => 'Teaching Assistant created successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update($id, $data)
    {
        try {
            $ta = TeachingAssistant::getById($this->db, $id);
            if ($ta) {
                $ta->setName($data['name']);
                $ta->setEmail($data['email']);
                $ta->save();
                return json_encode(['success' => true, 'message' => 'Teaching Assistant updated successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Teaching Assistant not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $ta = TeachingAssistant::getById($this->db, $id);
            if ($ta) {
                $ta->delete();
                return json_encode(['success' => true, 'message' => 'Teaching Assistant deleted successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Teaching Assistant not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function assignToCourse($taId, $courseId)
    {
        try {
            $ta = TeachingAssistant::getById($this->db, $taId);
            if (!$ta) {
                throw new Exception("Teaching Assistant not found.");
            }

            $response = $ta->assignToCourse($courseId);
            return $response;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function getAssignedCourses($taId)
    {
        try {
            $ta = TeachingAssistant::getById($this->db, $taId);
            if (!$ta) {
                throw new Exception("Teaching Assistant not found.");
            }

            $response = $ta->getCourses();
            return $response;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
