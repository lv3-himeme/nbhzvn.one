#!/bin/bash
cp Packages Packages-backup
rm Packages.bz2
bzip2 Packages
mv Packages-backup Packages