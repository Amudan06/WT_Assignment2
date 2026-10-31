package com.portal.util;

/** Tiny helper used by the JSP pages to escape text (prevents XSS). */
public final class Html {

    private Html() { }

    public static String escape(Object value) {
        if (value == null) return "";
        String s = value.toString();
        StringBuilder out = new StringBuilder(s.length() + 16);
        for (int i = 0; i < s.length(); i++) {
            char c = s.charAt(i);
            switch (c) {
                case 60:  out.append("&lt;");   break;   // <
                case 62:  out.append("&gt;");   break;   // >
                case 38:  out.append("&amp;");  break;   // &
                case 34:  out.append("&quot;"); break;   // "
                case 39:  out.append("&#39;");  break;   // 
                default:  out.append(c);
            }
        }
        return out.toString();
    }

    /** Timestamp -> "31 Oct 2026, 03:50 PM" (same style as the PHP pages). */
    public static String date(java.util.Date d) {
        return d == null ? "" : new java.text.SimpleDateFormat("dd MMM yyyy, hh:mm a").format(d);
    }

    /** 1536000 -> "1.5 MB" */
    public static String size(long bytes) {
        if (bytes >= 1048576) return String.format("%.1f MB", bytes / 1048576.0);
        if (bytes >= 1024)    return String.format("%.1f KB", bytes / 1024.0);
        return bytes + " B";
    }
}
