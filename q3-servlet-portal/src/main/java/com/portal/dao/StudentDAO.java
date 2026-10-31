package com.portal.dao;

import com.portal.model.Student;
import com.portal.model.StudentFile;
import com.portal.util.DBUtil;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/** Data access for students and their uploaded files. */
public class StudentDAO {

    /** Lists students (optionally filtered by a search word), each with its files. */
    public List<Student> findAll(String search) throws SQLException {
        // keep students in insertion order and find them quickly by id
        Map<Integer, Student> byId = new LinkedHashMap<>();

        String sql = "SELECT id, name, roll_number, email, course, created_at FROM students";
        boolean filter = search != null && !search.trim().isEmpty();
        if (filter) {
            sql += " WHERE name LIKE ? OR roll_number LIKE ? OR course LIKE ?";
        }
        sql += " ORDER BY name";

        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            if (filter) {
                String like = "%" + search.trim() + "%";
                ps.setString(1, like);
                ps.setString(2, like);
                ps.setString(3, like);
            }
            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    Student s = mapStudent(rs);
                    byId.put(s.getId(), s);
                }
            }

            // One extra query loads every file, then we attach them to their student
            try (PreparedStatement fp = con.prepareStatement(
                     "SELECT * FROM student_files ORDER BY category, uploaded_at DESC");
                 ResultSet rs = fp.executeQuery()) {
                while (rs.next()) {
                    StudentFile f = mapFile(rs);
                    Student owner = byId.get(f.getStudentId());
                    if (owner != null) owner.getFiles().add(f);
                }
            }
        }
        return new ArrayList<>(byId.values());
    }

    /** One student with files, or null if the id does not exist. */
    public Student findById(int id) throws SQLException {
        Student student = null;
        try (Connection con = DBUtil.getConnection()) {
            try (PreparedStatement ps = con.prepareStatement(
                    "SELECT id, name, roll_number, email, course, created_at FROM students WHERE id = ?")) {
                ps.setInt(1, id);
                try (ResultSet rs = ps.executeQuery()) {
                    if (rs.next()) student = mapStudent(rs);
                }
            }
            if (student != null) {
                try (PreparedStatement ps = con.prepareStatement(
                        "SELECT * FROM student_files WHERE student_id = ? ORDER BY category, uploaded_at DESC")) {
                    ps.setInt(1, id);
                    try (ResultSet rs = ps.executeQuery()) {
                        while (rs.next()) student.getFiles().add(mapFile(rs));
                    }
                }
            }
        }
        return student;
    }

    /** Looks up a single file record (used by FileServlet). */
    public StudentFile findFile(int fileId) throws SQLException {
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement("SELECT * FROM student_files WHERE id = ?")) {
            ps.setInt(1, fileId);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? mapFile(rs) : null;
            }
        }
    }

    // ---- row -> object helpers ----

    private Student mapStudent(ResultSet rs) throws SQLException {
        Student s = new Student();
        s.setId(rs.getInt("id"));
        s.setName(rs.getString("name"));
        s.setRollNumber(rs.getString("roll_number"));
        s.setEmail(rs.getString("email"));
        s.setCourse(rs.getString("course"));
        s.setCreatedAt(rs.getTimestamp("created_at"));
        return s;
    }

    private StudentFile mapFile(ResultSet rs) throws SQLException {
        StudentFile f = new StudentFile();
        f.setId(rs.getInt("id"));
        f.setStudentId(rs.getInt("student_id"));
        f.setCategory(rs.getString("category"));
        f.setOriginalName(rs.getString("original_name"));
        f.setStoredName(rs.getString("stored_name"));
        f.setFileType(rs.getString("file_type"));
        f.setFileSize(rs.getLong("file_size"));
        f.setUploadedAt(rs.getTimestamp("uploaded_at"));
        return f;
    }
}
