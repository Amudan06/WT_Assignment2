package com.portal.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * DBUtil - the only class that knows how to open a JDBC connection.
 * DAO classes call DBUtil.getConnection() inside try-with-resources,
 * so every connection is closed automatically.
 */
public final class DBUtil {

    private static String url;
    private static String user;
    private static String password;
    private static String uploadDir;

    private DBUtil() { }

    /** Called once by AppContextListener at start-up. */
    public static void init(String driver, String dbUrl, String dbUser, String dbPassword, String uploads) {
        try {
            Class.forName(driver);          // loads the MySQL / MariaDB JDBC driver
        } catch (ClassNotFoundException e) {
            throw new IllegalStateException("JDBC driver not found: " + driver
                + " - copy the connector jar into WEB-INF/lib", e);
        }
        url = dbUrl;
        user = dbUser;
        password = dbPassword == null ? "" : dbPassword;
        uploadDir = uploads;
    }

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(url, user, password);
    }

    /** Folder that holds the files uploaded by the PHP admin panel. */
    public static String getUploadDir() {
        return uploadDir;
    }
}
