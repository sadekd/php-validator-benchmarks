#!/bin/sh
# Install Composer after verifying the installer against the SHA-384 checksum Composer publishes
# (https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md).
# The hash is computed with PHP (busybox in the php alpine images has no sha384sum).
# Downloads retry up to 3 times with a 5s pause; a checksum mismatch counts as a failed attempt.
# After the last attempt the script exits 1 and the installer is never run unchecked.
set -u
setup=/tmp/composer-setup.php
for i in 1 2 3; do
  rm -f "$setup"
  if expected=$(curl -fsS https://composer.github.io/installer.sig) \
     && [ -n "$expected" ] \
     && curl -fsS -o "$setup" https://getcomposer.org/installer; then
    actual=$(php -r 'echo hash_file("sha384", $argv[1]);' "$setup")
    if [ "$actual" = "$expected" ]; then
      php "$setup" --quiet --install-dir=/usr/local/bin --filename=composer && rm -f "$setup" && exit 0
      echo "attempt $i: composer installer failed"
    else
      echo "attempt $i: Composer installer checksum MISMATCH (expected $expected, got $actual)"
    fi
  else
    echo "attempt $i: download failed"
  fi
  rm -f "$setup"
  [ "$i" -lt 3 ] && sleep 5
done
echo "ERROR: could not install a verified Composer installer after 3 attempts" >&2
exit 1
