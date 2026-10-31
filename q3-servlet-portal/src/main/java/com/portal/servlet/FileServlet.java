package com.portal.servlet;

import com.portal.dao.StudentDAO;
import com.portal.model.StudentFile;
import com.portal.util.DBUtil;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.OutputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;

/**
 * FileServlet (URL: /file?id=12)
 * Streams an uploaded image/document from the upload folder to the browser.
 * That is why <img src="file?id=12"> works in the JSP: the browser asks this
 * servlet, the servlet reads the bytes from disk and writes them to the response.
 * Only reachable after login (AuthFilter), and the real disk path is never exposed.
 */
public class FileServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        int fileId;
        try {
            fileId = Integer.parseInt(req.getParameter("id"));
        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST);
            return;
        }

        try {
            // 1) metadata from MySQL
            StudentFile meta = studentDAO.findFile(fileId);
            if (meta == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "No such file.");
                return;
            }

            // 2) build the path safely: getFileName() drops any "../" parts, and we
            //    double-check the result still lives inside the upload folder
            Path folder = Paths.get(DBUtil.getUploadDir()).toAbsolutePath().normalize();
            Path file = folder.resolve(Paths.get(meta.getStoredName()).getFileName()).normalize();
            if (!file.startsWith(folder) || !Files.isRegularFile(file)) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File is missing on the server.");
                return;
            }

            // 3) response headers: type, size, and inline (show) vs attachment (download)
            resp.setContentType(meta.getFileType());
            resp.setContentLengthLong(Files.size(file));
            resp.setHeader("Cache-Control", "private, max-age=3600");   // uploads never change
            resp.setHeader("X-Content-Type-Options", "nosniff");
            boolean download = "1".equals(req.getParameter("download"));
            String safeName = meta.getOriginalName().replaceAll("[^A-Za-z0-9._-]", "_");
            resp.setHeader("Content-Disposition",
                    (download ? "attachment" : "inline") + "; filename=\"" + safeName + "\"");

            // 4) copy the file bytes to the browser
            try (OutputStream out = resp.getOutputStream()) {
                Files.copy(file, out);
            }

        } catch (SQLException e) {
            log("File lookup failed", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
}
