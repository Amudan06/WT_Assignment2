package com.portal.servlet;

import com.portal.dao.UserDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.SQLException;

/**
 * LoginServlet (URL: /login)
 *   GET  -> shows the login page
 *   POST -> checks the credentials and starts a session
 */
public class LoginServlet extends HttpServlet {

    private final UserDAO userDAO = new UserDAO();

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        // already logged in? skip the login page
        HttpSession session = req.getSession(false);
        if (session != null && session.getAttribute("user") != null) {
            resp.sendRedirect(req.getContextPath() + "/students");
            return;
        }
        req.getRequestDispatcher("/login.jsp").forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        String username = req.getParameter("username");
        String password = req.getParameter("password");

        // basic empty-field check before touching the database
        if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
            fail(req, resp, "Please enter both username and password.");
            return;
        }

        try {
            String fullName = userDAO.authenticate(username.trim(), password);
            if (fullName == null) {
                fail(req, resp, "Invalid username or password.");
                return;
            }

            // Login OK. Destroy any old session first (prevents session fixation),
            // then create a fresh one and remember who logged in.
            HttpSession old = req.getSession(false);
            if (old != null) old.invalidate();

            HttpSession session = req.getSession(true);
            session.setAttribute("user", username.trim());
            session.setAttribute("fullName", fullName);
            session.setMaxInactiveInterval(30 * 60);     // log out after 30 idle minutes

            resp.sendRedirect(req.getContextPath() + "/students");

        } catch (SQLException e) {
            log("Login query failed", e);
            fail(req, resp, "Server error. Please try again later.");
        }
    }

    /** Re-shows the login page with an error message. */
    private void fail(HttpServletRequest req, HttpServletResponse resp, String message)
            throws ServletException, IOException {
        req.setAttribute("error", message);
        req.getRequestDispatcher("/login.jsp").forward(req, resp);
    }
}
