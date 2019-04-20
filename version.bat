@ECHO OFF
REM this script will update the "build" number of the ZenPhoto20 version and commit it
REM copyright by Stephen Billard, all rights reserved.

SET SOURCE=zp-core\version.php
FOR /F "delims=" %%a in ('FINDSTR "ZENPHOTO_VERSION" %SOURCE%') DO SET REL=%%a
SET REL=%REL:~28,-3%

FOR /F "tokens=1,2,3,4,5 delims=.'-" %%a in ("%REL%-") DO (
	SET major=%%a
	SET minor=%%b
	SET release=%%c
	SET build=%%d
	SET devbuild=%%e
	)

SET beta=[]
SET /a devversion=0

FOR /F "tokens=1,2 delims=.'-" %%a in ("%CD%") DO (
	SET base = %%a
	SET beta=%%b
)

if NOT [%beta%]==[] GOTO SETVERSION
SET param=%1
IF [%param%]==[] GOTO BUILD
SET option=%param:~0,3%
IF [%option%]==[maj] GOTO MAJOR
IF [%option%]==[min] GOTO MINOR
IF [%option%]==[rel] GOTO RELEASE
GOTO BUILD

:MAJOR
SET /a major=%major%+1
SET /a minor=1000000
SET /a release=1000000
SET /a build=1000000
GOTO SETVERSION

:MINOR
SET /a minor=%minor%+1
SET /a N=1%minor%-(11%minor%-1%minor%)/10
SET N=000000%N%
SET minor=%N:~-2%
SET /a release=1000000
SET /a build=1000000
GOTO SETVERSION

:RELEASE
SET /a release=%release%+1
SET /a N=1%release%-(11%release%-1%release%)/10
SET /a build=%N%+1
SET /a N=1%release%-(11%release%-1%release%)/10
SET release=1000000%N%
SET build=1000000
GOTO SETVERSION

:BUILD
SET /a N=1%build%-(11%build%-1%build%)/10
SET /a build=%N%+1
SET /a N=1%build%-(11%build%-1%build%)/10
SET build=1000000%N%

:SETVERSION
SET new=%major%.%minor:~-2%.%release:~-2%.%build:~-2%
SET doc=%new%

IF [%beta%]==[] GOTO TAG
if [%devbuild%]==[] goto DEVBUILD

FOR /F "tokens=1,2 delims=.'_" %%a in ("%devbuild%") DO (
	SET base=%%a
	SET devversion=%%b
)
if [%devversion%]==[] set devversion=%base%
:DEVBUILD
SET /a N=1%devversion%-(11%devversion%-1%devversion%)/10
SET /a devversion=%N%+1
SET /a N=1%devversion%-(11%devversion%-1%devversion%)/10
SET devversion=1000000%N%
SET new=%new%.%devversion:~-2%

REM for dev builds show doc as next build level
SET /a N=1%build%-(11%build%-1%build%)/10
SET /a build=%N%+1
SET /a N=1%build%-(11%build%-1%build%)/10
SET build=1000000%N%
SET doc=%major%.%minor:~0,2%.%release:~0,2%.%build:~0,2%

:TAG

>%SOURCE%	echo ^<?php
>>%SOURCE%	echo // This file contains version info only and is automatically updated. DO NOT EDIT.
>>%SOURCE%	echo define('ZENPHOTO_VERSION', '%new%');
>>%SOURCE%	echo ?^>

:DOCUPDATE
setlocal

rem update the version number in the release notes
set dest="docs\release notes.htm"

(for /f "delims=" %%i in (D:\test_sites\dev\docs\release_notes.htm) do (
    set "line=%%i"
    setlocal enabledelayedexpansion
    set "line=!line:$v$=%doc%!"
    echo(!line!
    endlocal
))>%dest%

rem update the user guide
D:\github\DevTools\officetopdf.exe "D:\github\DevTools\user guide.docx" "docs/user guide.pdf"

:COMMIT

rem commit the changes

@git add .
@git commit -m"release build %NEW%"

:END
