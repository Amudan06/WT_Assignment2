package com.portal.servlet;

import com.portal.dao.StudentDAO;
import com.portal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.SQLException;

/** StudentProfileServlet (URL: /profile?id=5) - shows one student with all files. */
public class StudentProfileServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int id;
        try {
            id = Integer.parseInt(req.getParameter("id"));
        } catch (NumberFormatException e) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid student id.");
            return;
        }

        try {
            Student student = studentDAO.findById(id);
            if (student == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Student not found.");
                return;
            }
            req.setAttribute("student", student);
            req.getRequestDispatcher("/profile.jsp").forward(req, resp);
        } catch (SQLException e) {
            log("Could not load student " + id, e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Could not load the profile.");
        }
    }
}
