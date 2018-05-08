@echo off
REM this script will update the "build" number of the ZenPhoto20 version and commit it
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
SET param=%1
IF [%param%]==[] GOTO BUILD
SET option=%param:~0,3%
IF [%option%]==[maj] GOTO MAJOR
IF [%option%]==[min] GOTO MINOR
IF [%option%]==[rel] GOTO RELEASE
SET /a build=%build%+1
GOTO SETBETA
:MAJOR
SET /a major=%major%+1
SET /a minor=0
SET /a release=0
SET /a build=0
GOTO SETBETA
:MINOR
SET /a minor=%minor%+1
SET /a release=0
SET /a build=0
GOTO SETBETA
:RELEASE
SET /a release=%release%+1
SET /a build=0
GOTO SETBETA
:BUILD
SET /a build=%build%+1
:SETBETA
SET new=%major%.%minor%.%release%.%build%
IF [%beta%]==[] GOTO TAG
	SET new=%new%-%beta%
:TAG

>%SOURCE%	echo ^<?php
>>%SOURCE%	echo // This file contains version info only and is automatically updated. DO NOT EDIT.
>>%SOURCE%	echo define('ZENPHOTO_VERSION', '%new%');
>>%SOURCE%	echo ?^>

rem set the version number into the release notes document

setlocal

set dest="docs\release notes.htm"

rem del %dest%

(for /f "delims=" %%i in (D:\test_sites\dev\docs\release_notes.htm) do (
    set "line=%%i"
    setlocal enabledelayedexpansion
    set "line=!line:$v$=%new%!"
    echo(!line!
    endlocal
))>%dest%

:COMMIT
rem commit the changes

@git add .
@git commit -m"release build %NEW%"

:END