<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ page import="com.portal.util.Html" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Student Profile Portal</title>
  <link rel="stylesheet" href="<%= request.getContextPath() %>/css/style.css">
</head>
<body>
  <div class="container login-box">
    <div class="card">
      <h1>🎓 Student Portal</h1>
      <p class="muted">Please sign in to view student profiles.</p>

      <%-- error set by LoginServlet (forward) or passed in the URL by AuthFilter --%>
      <%
        String error = (String) request.getAttribute("error");
        if (error == null) error = request.getParameter("error");
        String msg = request.getParameter("msg");
        if (error != null) {
      %>
        <div class="alert error"><%= Html.escape(error) %></div>
      <% } else if (msg != null) { %>
        <div class="alert success"><%= Html.escape(msg) %></div>
      <% } %>

      <form method="POST" action="<%= request.getContextPath() %>/login">
        <div class="field" style="margin-bottom:1rem">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" autofocus required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="actions">
          <button type="submit" class="btn" style="width:100%">Login</button>
        </div>
      </form>
      <p class="hint" style="margin-top:1rem">Demo account: <code>admin</code> / <code>admin123</code></p>
    </div>
  </div>
</body>
</html>
