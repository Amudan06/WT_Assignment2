package com.portal.model;

import java.sql.Timestamp;

/** One row of the student_files table (metadata of an uploaded file). */
public class StudentFile {
    private int id;
    private int studentId;
    private String category;       // profile_image | id_proof | certificate
    private String originalName;
    private String storedName;     // real file name inside the upload folder
    private String fileType;       // MIME type
    private long fileSize;
    private Timestamp uploadedAt;

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    public int getStudentId() { return studentId; }
    public void setStudentId(int studentId) { this.studentId = studentId; }
    public String getCategory() { return category; }
    public void setCategory(String category) { this.category = category; }
    public String getOriginalName() { return originalName; }
    public void setOriginalName(String originalName) { this.originalName = originalName; }
    public String getStoredName() { return storedName; }
    public void setStoredName(String storedName) { this.storedName = storedName; }
    public String getFileType() { return fileType; }
    public void setFileType(String fileType) { this.fileType = fileType; }
    public long getFileSize() { return fileSize; }
    public void setFileSize(long fileSize) { this.fileSize = fileSize; }
    public Timestamp getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(Timestamp uploadedAt) { this.uploadedAt = uploadedAt; }

    public boolean isImage() { return fileType != null && fileType.startsWith("image/"); }

    /** Friendly label shown on the page. */
    public String getCategoryLabel() {
        switch (category) {
            case "profile_image": return "Profile photo";
            case "id_proof":      return "ID proof";
            default:              return "Certificate";
        }
    }
}
