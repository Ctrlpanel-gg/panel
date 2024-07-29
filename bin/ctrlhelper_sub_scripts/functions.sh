#!/bin/bash
#
# The auxiliary file for CtrlHelper script.
# Contains important functions for the main script to work
#
# Made with love by MrWeez
# Contact me
#   GitHub: https://github.com/MrWeez
#   Discord: @mrweez_
#   Email: contact@mrweez.dev

#######################################
# Terminal save function, for recovery after exiting
#######################################
save_terminal() {
  trap restore_terminal SIGINT
  tput smcup
}

#######################################
# Terminal recovery function, after execution
#######################################
restore_terminal() {
  tput rmcup
  exit
}

#######################################
# Getting current and latest version of CtrlPanel
# Globals:
#   PANEL_VER
#   PANEL_LATEST_VER
#   DEFAULT_DIR
#   cpgg_dir
#######################################
get_version() {
  PANEL_VER=$(grep -oP "'version' => '\K[^']+" "${cpgg_dir:-$DEFAULT_DIR}/config/app.php")
  readonly PANEL_VER
  PANEL_LATEST_VER=$(
    curl -s https://api.github.com/repos/ctrlpanel-gg/panel/tags \
      | sed -n 's/.*"name": "\([^"]*\)".*/\1/p' \
      | head -n 1
  )
  readonly PANEL_LATEST_VER
}

#######################################
# Comparing current and latest version of CtrlPanel
# Arguments:
#   Current version
#   Latest version
# Outputs:
#   0 if versions match
#   1 if latest version is newer than the current one
#   2 if current version is newer than the latest one
#######################################
version_compare() {
  local current_version="$1"
  local latest_version="$2"

  # Break down versions into components
  IFS='.' read -r -a current_parts <<<"$current_version"
  IFS='.' read -r -a latest_parts <<<"$latest_version"

  # Add zeros to the shorter version (e.g. 0.10 => 0.10.0)
  while ((${#current_parts[@]} < ${#latest_parts[@]})); do
    current_parts+=("0")
  done

  # Compare components one by one
  for ((i = 0; i < ${#current_parts[@]}; i++)); do
    if ((current_parts[i] < latest_parts[i])); then
      echo "1"
      return 0
    elif ((current_parts[i] > latest_parts[i])); then
      echo "2"
      return 0
    fi
  done

  echo "0"
  return 0
}

#######################################
# Checking if the CtrlPanel needs to be updated
# Globals:
#   PANEL_VER
#   PANEL_LATEST_VER
# Outputs:
#   0 if versions match
#   1 if latest version is newer than the current one
#   2 if current version is newer than the latest one
#######################################
update_needed_checker() {
  is_update_needed=$(version_compare "$PANEL_VER" "$PANEL_LATEST_VER")
  # shellcheck disable=SC2034
  readonly is_update_needed
}

#######################################
# Installing dependencies
# Globals:
#   cli_mode
# Arguments:
#   NULL for full installation, true for minimal install
#######################################
install_deps() {
  local minimal="$1"

  if [[ -z "$cli_mode" ]]; then
    logo
  fi

  echo " Adding \"add-apt-repository\" command and additional dependencies"
  sudo apt -y install software-properties-common curl apt-transport-https ca-certificates gnupg

  echo " Adding PHP repository"
  LC_ALL=C.UTF-8 sudo add-apt-repository -y ppa:ondrej/php

  if [[ ! -f "/usr/share/keyrings/redis-archive-keyring.gpg" ]]; then
    echo " Adding Redis repository"
    curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" \
      | sudo tee /etc/apt/sources.list.d/redis.list
  fi

  if [[ -z "$minimal" ]]; then
    echo " Adding MariaDB repository"
    curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
  elif [[ -n "$minimal" && "$minimal" != "true" ]]; then
    error_out " ERROR: Invalid argument $minimal for install_deps function. \
Please, report to developers!"
    exit 1
  fi

  echo " Running \"apt update\""
  sudo apt update

  echo " Installing dependencies"
  if [[ "$minimal" ]]; then
    sudo apt -y install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} redis-server tar unzip git
  elif [[ -z "$minimal" ]]; then
    sudo apt -y install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} mariadb-server nginx redis-server tar unzip git
  else
    error_out " ERROR: Invalid argument $minimal for install_deps function. \
Please, report to developers!"
    exit 1
  fi

  echo " Installing Composer"
  curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

  echo " Installing Composer dependencies to the CtrlPanel"
  sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
}

#######################################
# Update CtrlPanel
# Globals:
#   DEFAULT_DIR
#   cpgg_dir
#   cli_mode
#######################################
update() {
  if [[ -z "$cli_mode" ]]; then
    logo
  fi

  echo " Enabling maintenance mode"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan down

  if ! sudo git config --global --get-all safe.directory | grep -q -w "${cpgg_dir:-$DEFAULT_DIR}"; then
    echo " Adding CtrlPanel directory to the git save.directory list"
    sudo git config --global --add safe.directory "${cpgg_dir:-$DEFAULT_DIR}"
  fi

  echo " Downloading file updates"
  sudo git stash
  sudo git pull

  echo " Installing Composer dependencies"
  sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

  echo " Migrating database updates"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan migrate --seed --force

  echo " Clearing the cache"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan view:clear
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan config:clear

  echo " Setting permissions"
  sudo chown -R www-data:www-data "${cpgg_dir:-$DEFAULT_DIR}"
  sudo chmod -R 755 "${cpgg_dir:-$DEFAULT_DIR}"

  echo " Restarting Queue Workers"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan queue:restart

  echo " Disabling maintenance mode"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan up
}
