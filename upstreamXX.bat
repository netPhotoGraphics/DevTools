SET source=%1
SET repro=%2
git remote remove upstream
git remote add upstream https://github.com/%source%/%repro%.git
git fetch upstream
git merge --no-commit upstream/master