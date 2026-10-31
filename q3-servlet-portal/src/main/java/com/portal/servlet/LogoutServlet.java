package com.portal.servlet;

import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;

/** LogoutServlet (URL: /logout) - ends the session and returns to the login page. */
public class LogoutServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);
        if (session != null) session.invalidate();     // removes all session data
        resp.sendRedirect(req.getContextPath() + "/login?msg=You+have+been+logged+out");
    }
}
