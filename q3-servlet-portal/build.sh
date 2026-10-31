#!/bin/bash
# build.sh - compiles the servlets and packages portal.war (no Maven needed)
# Usage:  ./build.sh  [path-to-servlet-api.jar]  [path-to-jdbc-driver.jar]
#   e.g.  ./build.sh /opt/tomcat/lib/servlet-api.jar ~/Downloads/mysql-connector-j-8.4.0.jar
set -e
SERVLET_API=${1:-$CATALINA_HOME/lib/servlet-api.jar}
JDBC_JAR=${2:-}
OUT=build/portal

rm -rf build && mkdir -p $OUT
cp -r src/main/webapp/* $OUT/                         # JSPs, CSS, WEB-INF/web.xml
mkdir -p $OUT/WEB-INF/classes
javac -d $OUT/WEB-INF/classes -cp "$SERVLET_API" $(find src/main/java -name "*.java")
[ -n "$JDBC_JAR" ] && cp "$JDBC_JAR" $OUT/WEB-INF/lib/   # driver must live in WEB-INF/lib
(cd $OUT && jar cf ../portal.war .)
echo "Built build/portal.war  -> copy it to Tomcat's webapps folder"
