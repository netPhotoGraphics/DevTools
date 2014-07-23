@echo off
# copyright Stephen Billard
# permission is granted for use in conjunction with ZenPhoto20 all other rights reserved
git rev-parse HEAD>zp-core\githead
SET /P LONG=<zp-core\githead
SET SHORT=%LONG:~0,10%
git add zp-core\githead
git commit -m"release id %SHORT%"
