package com.portal.servlet;

import com.portal.dao.StudentDAO;
import com.portal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

/**
 * StudentListServlet (URL: /students)
 * Controller: fetches the students from MySQL through the DAO, puts the list
 * in the request, and forwards to students.jsp which draws the HTML (MVC pattern).
 */
public class StudentListServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        String search = req.getParameter("q");
        try {
            List<Student> students = studentDAO.findAll(search);
            req.setAttribute("students", students);
            req.setAttribute("search", search == null ? "" : search.trim());
            req.getRequestDispatcher("/students.jsp").forward(req, resp);
        } catch (SQLException e) {
            log("Could not load students", e);
            resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Could not load students.");
        }
    }
}
