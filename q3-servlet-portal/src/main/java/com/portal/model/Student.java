package com.portal.model;

import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

/** One row of the students table, plus the list of its uploaded files. */
public class Student {
    private int id;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private Timestamp createdAt;
    private final List<StudentFile> files = new ArrayList<>();

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getRollNumber() { return rollNumber; }
    public void setRollNumber(String rollNumber) { this.rollNumber = rollNumber; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getCourse() { return course; }
    public void setCourse(String course) { this.course = course; }
    public Timestamp getCreatedAt() { return createdAt; }
    public void setCreatedAt(Timestamp createdAt) { this.createdAt = createdAt; }
    public List<StudentFile> getFiles() { return files; }

    /** Returns the profile photo, or null when the student has none. */
    public StudentFile getProfileImage() {
        for (StudentFile f : files) {
            if ("profile_image".equals(f.getCategory())) return f;
        }
        return null;
    }

    /** "Priya Sharma" -> "PS"  (shown when there is no photo). */
    public String getInitials() {
        String[] parts = name.trim().split("\\s+");
        String first = parts[0].substring(0, 1);
        String last = parts.length > 1 ? parts[parts.length - 1].substring(0, 1) : "";
        return (first + last).toUpperCase();
    }
}
