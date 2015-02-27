@ECHO OFF
REM this script will "tag" the ZenPhoto20 release
REM copyright by Stephen Billard, all rights reserved.

SET SOURCE=zp-core\version.php
FOR /F "delims=" %%a in ('FINDSTR "ZENPHOTO_VERSION" %SOURCE%') DO SET REL=%%a
SET REL=%REL:~28,-3%
FOR /F "tokens=1,2,3,4,5 delims=.'-" %%a in ("%REL%") DO (
	SET major=%%a
	SET minor=%%b
	SET release=%%c
	SET build=%%d
	SET beta=%%e
)

SET VERSION=%major%.%minor%.%release%.%build%

IF [%beta%]==[] GOTO TAG
	SET VERSION=%VERSION%-%beta%
:TAG
echo "Tagging %VERSION%..."

git tag -a -f -m"ZenPhoto20 version %VERSION%" ZenPhoto20-%VERSION%
git push --tags
