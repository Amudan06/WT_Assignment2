<%@ page contentType="text/html;charset=UTF-8" language="java" isErrorPage="true" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Something went wrong</title>
  <link rel="stylesheet" href="<%= request.getContextPath() %>/css/style.css">
</head>
<body>
  <div class="container login-box">
    <div class="card" style="text-align:center">
      <h1>😕 Oops!</h1>
      <p class="muted">The page you asked for could not be shown.</p>
      <a class="btn" href="<%= request.getContextPath() %>/students">Back to students</a>
    </div>
  </div>
</body>
</html>
