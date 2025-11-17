#!/usr/bin/env bash
set -e
DB_NAME=${1-wp_tests}
DB_USER=${2-root}
DB_PASS=${3-''}
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

install_wp() {
  if [ -d $WP_CORE_DIR ]; then
    return
  fi
  mkdir -p $WP_CORE_DIR
  wget -nv -O /tmp/wordpress.tar.gz https://wordpress.org/wordpress-${WP_VERSION}.tar.gz
  tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
}

install_test_suite() {
  if [ -d $WP_TESTS_DIR ]; then
    return
  fi
  mkdir -p $WP_TESTS_DIR
  wget -nv -O /tmp/wordpress-tests-lib.tar.gz https://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/includes/export.tar.gz
  tar --strip-components=1 -zxmf /tmp/wordpress-tests-lib.tar.gz -C $WP_TESTS_DIR
}

install_db() {
  mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" --force
}

install_wp
install_test_suite
install_db
