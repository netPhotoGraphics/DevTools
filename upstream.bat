ECHO off
FOR /f "tokens=1-4 delims=/ " %%i IN ("%date%") DO (
	SET dow=%%i
	SET month=%%j
	SET day=%%k
	SET year=%%l
)
SET datestr=%month%_%day%_%year%
FOR /f "tokens=1-3 delims=/:." %%i IN ("%time%") DO (
	SET hr=%%i
	SET min=%%j
	SET sec=%%k
)
SET timestr=%hr%_%min%_%sec%
SET upstream=%datestr%_%timestr%
ECHO on

git remote add %upstream% https://github.com/zenphoto/zenphoto.git
git fetch %upstream%
git merge --no-commit %upstream%/master
git remote remove %upstream%