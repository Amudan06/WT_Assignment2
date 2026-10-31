package com.portal.dao;

import com.portal.util.DBUtil;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/** Data access for the users table (login check). */
public class UserDAO {

    /**
     * Checks username + password.
     * @return the user full name when the login is correct, otherwise null
     */
    public String authenticate(String username, String password) throws SQLException {
        // ? placeholders = PreparedStatement, so SQL injection is impossible
        String sql = "SELECT full_name, password_hash FROM users WHERE username = ?";
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setString(1, username);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    byte[] stored = rs.getString("password_hash").getBytes(StandardCharsets.UTF_8);
                    byte[] given  = sha256(password).getBytes(StandardCharsets.UTF_8);
                    // isEqual compares in constant time (avoids timing attacks)
                    if (MessageDigest.isEqual(stored, given)) {
                        return rs.getString("full_name");
                    }
                }
            }
        }
        return null;
    }

    /** SHA-256 of the text as a 64 character hex string. */
    static String sha256(String text) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            byte[] hash = md.digest(text.getBytes(StandardCharsets.UTF_8));
            StringBuilder hex = new StringBuilder();
            for (byte b : hash) hex.append(String.format("%02x", b));
            return hex.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalStateException(e);   // SHA-256 exists on every JVM
        }
    }
}
