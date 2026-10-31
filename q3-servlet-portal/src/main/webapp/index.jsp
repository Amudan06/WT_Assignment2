<%-- Welcome page: just send visitors to the student list (the filter asks for login first) --%>
<% response.sendRedirect(request.getContextPath() + "/students"); %>
