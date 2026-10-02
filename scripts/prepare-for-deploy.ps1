# Bundle shared CMS helpers into this school folder (standalone AWS deploy-ready).
$ErrorActionPreference = 'Stop'
$site = Split-Path $PSScriptRoot -Parent
$prepare = Join-Path (Split-Path $site -Parent) 'scripts\prepare-school-for-deploy.ps1'
& $prepare -SchoolPath $site
