<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ page import="com.portal.model.Student, com.portal.model.StudentFile, com.portal.util.Html" %>
<%
  Student s = (Student) request.getAttribute("student");   // set by StudentProfileServlet
  StudentFile photo = s.getProfileImage();
  String ctx = request.getContextPath();
%>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><%= Html.escape(s.getName()) %> | Student Profile Portal</title>
  <link rel="stylesheet" href="<%= ctx %>/css/style.css">
</head>
<body>
<%@ include file="/WEB-INF/jspf/header.jspf" %>

<main class="container">
  <div class="card">
    <div class="profile-head">
      <% if (photo != null) { %>
        <img class="avatar big" src="<%= ctx %>/file?id=<%= photo.getId() %>" alt="Profile photo">
      <% } else { %>
        <span class="avatar big"><%= Html.escape(s.getInitials()) %></span>
      <% } %>
      <div>
        <h1 style="margin:0"><%= Html.escape(s.getName()) %></h1>
        <span class="badge"><%= Html.escape(s.getRollNumber()) %></span>
        <p class="muted"><%= Html.escape(s.getCourse()) %></p>
      </div>
    </div>

    <table class="details">
      <tr><th>Email</th><td><%= Html.escape(s.getEmail()) %></td></tr>
      <tr><th>Roll number</th><td><%= Html.escape(s.getRollNumber()) %></td></tr>
      <tr><th>Course</th><td><%= Html.escape(s.getCourse()) %></td></tr>
      <tr><th>Registered on</th><td><%= Html.date(s.getCreatedAt()) %></td></tr>
    </table>

    <h2 style="margin-top:1.8rem">Uploaded Files <span class="badge"><%= s.getFiles().size() %></span></h2>

    <% if (s.getFiles().isEmpty()) { %>
      <p class="muted">No files uploaded yet.</p>
    <% } else { %>
    <div class="file-list">
      <% for (StudentFile f : s.getFiles()) { %>
      <div class="file-box">
        <%-- the src points to the SERVLET, which streams the bytes from disk --%>
        <% if (f.isImage()) { %>
          <a href="<%= ctx %>/file?id=<%= f.getId() %>" target="_blank">
            <img src="<%= ctx %>/file?id=<%= f.getId() %>" alt="<%= Html.escape(f.getOriginalName()) %>">
          </a>
        <% } else { %>
          <a href="<%= ctx %>/file?id=<%= f.getId() %>" target="_blank"><div class="doc-icon">📄</div></a>
        <% } %>
        <span class="badge"><%= Html.escape(f.getCategoryLabel()) %></span><br>
        <strong><%= Html.escape(f.getOriginalName()) %></strong><br>
        <span class="muted">
          <%= Html.escape(f.getFileType()) %> &middot; <%= Html.size(f.getFileSize()) %><br>
          Uploaded <%= Html.date(f.getUploadedAt()) %>
        </span><br>
        <a class="btn small secondary" style="margin-top:.5rem" href="<%= ctx %>/file?id=<%= f.getId() %>&download=1">Download</a>
      </div>
      <% } %>
    </div>
    <% } %>

    <div class="actions"><a class="btn secondary" href="<%= ctx %>/students">&larr; Back to students</a></div>
  </div>
</main>
<footer>Web Technology (U21CS501) &middot; Assignment II &middot; Q3</footer>
</body>
</html>
