# copyright Stephen Billard
# permission is granted for use in conjunction with ZenPhoto20 all other rights reservedSET source=%1
SET repro=%2
git remote remove upstream
git remote add upstream https://github.com/%source%/%repro%.git
git fetch upstream
git merge upstream/master