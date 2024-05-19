<?php

require_once __DIR__ . '/../Models/Section.php';

class SectionController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db->getConnection();
    }

    public function index()
    {
        try {
            $sections = Section::getAll($this->db);
            return json_encode(['success' => true, 'data' => $sections]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $section = Section::getById($this->db, $id);
            if ($section) {
                return json_encode(['success' => true, 'data' => $section]);
            } else {
                return json_encode(['success' => false, 'error' => 'Section not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function store($data)
    {
        try {
            // Ensure course and teaching assistant exist
            $course = Course::getById($this->db, $data['course_id']);
            $ta = TeachingAssistant::getById($this->db, $data['ta_id']);

            if (!$course || !$ta) {
                return json_encode(['success' => false, 'error' => 'Invalid course or teaching assistant']);
            }

            // Create new section
            $section = new Section($this->db, null, $data['course_id'], $data['ta_id'], $data['time'], $data['location']);
            $section->save();
            return json_encode(['success' => true, 'message' => 'Section created successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    public function update($id, $data)
    {
        try {
            $section = Section::getById($this->db, $id);
            if ($section) {
                $section->setCourseId($data['course_id']);
                $section->setTAId($data['ta_id']);
                $section->setTime($data['time']);
                $section->setLocation($data['location']);
                $section->save();
                return json_encode(['success' => true, 'message' => 'Section updated successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Section not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $section = Section::getById($this->db, $id);
            if ($section) {
                $section->delete();
                return json_encode(['success' => true, 'message' => 'Section deleted successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Section not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
