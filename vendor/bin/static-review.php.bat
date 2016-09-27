@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sjparkinson/static-review/bin/static-review.php
php "%BIN_TARGET%" %*
