<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ page import="java.util.List, com.portal.model.Student, com.portal.model.StudentFile, com.portal.util.Html" %>
<%
  // Data prepared by StudentListServlet
  @SuppressWarnings("unchecked")
  List<Student> students = (List<Student>) request.getAttribute("students");
  String search = (String) request.getAttribute("search");
  String ctx = request.getContextPath();
%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students | Student Profile Portal</title>
  <link rel="stylesheet" href="<%= ctx %>/css/style.css">
</head>
<body>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<main class="container">
  <div class="page-head">
    <h1>Student Profiles <span class="badge"><%= students.size() %></span></h1>
    <form method="GET" action="<%= ctx %>/students" style="display:flex;gap:.5rem">
      <input type="search" name="q" value="<%= Html.escape(search) %>" placeholder="Search name, roll no, course">
      <button class="btn" type="submit">Search</button>
    </form>
  </div>

  <%-- CONDITIONAL: empty result vs. list --%>
  <% if (students.isEmpty()) { %>
    <div class="card"><p class="muted" style="text-align:center">No students found.</p></div>
  <% } else { %>
  <div class="student-grid">
    <%-- LOOP 1: one card per student --%>
    <% for (Student s : students) {
         StudentFile photo = s.getProfileImage();
    %>
    <div class="student-card">
      <%-- CONDITIONAL: real photo (streamed by FileServlet) or initials --%>
      <% if (photo != null) { %>
        <img class="avatar big" src="<%= ctx %>/file?id=<%= photo.getId() %>" alt="Photo of <%= Html.escape(s.getName()) %>">
      <% } else { %>
        <span class="avatar big"><%= Html.escape(s.getInitials()) %></span>
      <% } %>

      <h3><%= Html.escape(s.getName()) %></h3>
      <span class="badge"><%= Html.escape(s.getRollNumber()) %></span>
      <p class="muted"><%= Html.escape(s.getCourse()) %></p>

      <ul class="file-links">
        <%-- LOOP 2: every file of this student --%>
        <% for (StudentFile f : s.getFiles()) { %>
          <li>
            <%= f.isImage() ? "🖼️" : "📄" %>
            <a href="<%= ctx %>/file?id=<%= f.getId() %>" target="_blank"><%= Html.escape(f.getCategoryLabel()) %></a>
            <span class="muted">(<%= Html.size(f.getFileSize()) %>)</span>
          </li>
        <% } %>
        <% if (s.getFiles().isEmpty()) { %>
          <li class="muted">No files uploaded</li>
        <% } %>
      </ul>

      <div class="actions" style="justify-content:center">
        <a class="btn small" href="<%= ctx %>/profile?id=<%= s.getId() %>">View profile</a>
      </div>
    </div>
    <% } %>
  </div>
  <% } %>
</main>
<footer>Web Technology (U21CS501) &middot; Assignment II &middot; Q3 &middot; Servlet + JDBC + JSP</footer>
</body>
</html>
