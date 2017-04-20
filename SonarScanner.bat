@echo off
REM https://about.sonarqube.com/get-started/
echo Start SonarQube Scanner on %cd%
pause
sonar-scanner -X -Dsonar.projectKey=my_wordpress -Dsonar.sources=. -Dsonar.host.url=http://sonarqube-vandeel.westeurope.cloudapp.azure.com:9000 -Dsonar.organization=jmvandeel-github -Dsonar.login=10662584a057b40e9bb4a1989390433c1c95a992 -Dsonar.verbose=true
pause >nul
echo Done SonarQube Scanner
pause >nul