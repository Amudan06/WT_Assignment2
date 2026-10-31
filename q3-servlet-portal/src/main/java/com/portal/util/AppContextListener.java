package com.portal.util;

import jakarta.servlet.ServletContext;
import jakarta.servlet.ServletContextEvent;
import jakarta.servlet.ServletContextListener;

/**
 * Runs once when Tomcat starts the web application.
 * It reads the settings written in WEB-INF/web.xml (<context-param>) and
 * hands them to DBUtil, so no password is hard-coded inside Java code.
 */
public class AppContextListener implements ServletContextListener {

    @Override
    public void contextInitialized(ServletContextEvent sce) {
        ServletContext ctx = sce.getServletContext();
        DBUtil.init(
            ctx.getInitParameter("db.driver"),
            ctx.getInitParameter("db.url"),
            ctx.getInitParameter("db.user"),
            ctx.getInitParameter("db.password"),
            ctx.getInitParameter("upload.dir")
        );
        ctx.log("Student portal started. Upload folder = " + DBUtil.getUploadDir());
    }

    @Override
    public void contextDestroyed(ServletContextEvent sce) { /* nothing to clean up */ }
}
