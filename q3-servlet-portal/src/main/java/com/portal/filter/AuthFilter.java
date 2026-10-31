package com.portal.filter;

import jakarta.servlet.Filter;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;

/**
 * AuthFilter - the security guard of the portal.
 * web.xml maps it to /students, /profile and /file, so every request to
 * those URLs passes through doFilter() first. No login -> redirect to /login.
 */
public class AuthFilter implements Filter {

    @Override
    public void doFilter(ServletRequest req, ServletResponse res, FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest request = (HttpServletRequest) req;
        HttpServletResponse response = (HttpServletResponse) res;

        // false = do NOT create a new session just for checking
        HttpSession session = request.getSession(false);
        boolean loggedIn = session != null && session.getAttribute("user") != null;

        if (loggedIn) {
            // stop browsers from caching private pages (back button after logout)
            response.setHeader("Cache-Control", "no-store, must-revalidate");
            chain.doFilter(req, res);          // allowed: carry on to the servlet
        } else {
            response.sendRedirect(request.getContextPath() + "/login?error=Please+log+in+first");
        }
    }
}
