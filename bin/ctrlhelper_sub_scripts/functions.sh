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
# Return an info message in STDOUT
# Arguments:
#   Info message
#######################################
info_out() {
  echo -e " ${BB_BLU}${BT_WH} INFO ${NC} ${BT_WH}$*${NC}"
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
  PANEL_VER=$(
    grep -oP "'version' => '\K[^']+" "${cpgg_dir:-$DEFAULT_DIR}/config/app.php"
    )
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
  readonly current_version
  local latest_version="$2"
  readonly latest_version

  # Break down versions into components
  IFS='.' read -r -a current_parts <<<"${current_version}"
  IFS='.' read -r -a latest_parts <<<"${latest_version}"

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
  is_update_needed=$(version_compare "${PANEL_VER}" "${PANEL_LATEST_VER}")
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
  # Removing double quotes at the beginning and end
  minimal=${minimal#\"}
  minimal=${minimal%\"}
  readonly minimal

  logo

  info_out "Adding \"add-apt-repository\" command and additional dependencies"
  sudo apt -y -qq install software-properties-common curl apt-transport-https ca-certificates gnupg lsb-release

  if [[ ! -f "/etc/apt/trusted.gpg.d/deb.sury.org-php.gpg" ]]; then
    info_out "Adding PHP repository keyring"
    sudo curl -sSLo /etc/apt/trusted.gpg.d/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
  fi

  if [[ ! -f "/etc/apt/sources.list.d/deb.sury.org-php.list" ]]; then
    info_out "Adding PHP repository"
    sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/deb.sury.org-php.list'
  fi

  if [[ ! -f "/usr/share/keyrings/redis-archive-keyring.gpg" ]]; then
    info_out "Adding Redis repository"
    curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" \
      | sudo tee /etc/apt/sources.list.d/redis.list
  fi

  if [[ -z "${minimal}" ]]; then
    info_out "Adding MariaDB repository"
    curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
  elif [[ -n "${minimal}" && "${minimal}" != "true" ]]; then
    error_out "Invalid argument ${minimal} for install_deps function. Please, report to developers!"
    exit 1
  fi

  info_out "Running \"apt update\""
  sudo apt update

  info_out "Installing dependencies"
  if [[ "${minimal}" ]]; then
    sudo apt -y -qq install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} redis-server tar unzip git
  elif [[ -z "${minimal}" ]]; then
    sudo apt -y -qq install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} mariadb-server nginx redis-server tar unzip git
  else
    error_out "Invalid argument ${minimal} for install_deps function. Please, report to developers!"
    exit 1
  fi

  info_out "Installing Composer"
  curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

  info_out "Installing Composer dependencies to the CtrlPanel"
  sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

  if [[ -z "${cli_mode}" ]]; then
    echo ""
    echo -e " ${BB_GR}${T_BL} Done! ${NC} ${BT_GR}Installation finished. Press any key to exit${NC}"
    read -rsn 1 -p " "
  fi
}

#######################################
# Update CtrlPanel
# Globals:
#   DEFAULT_DIR
#   cpgg_dir
#   cli_mode
#######################################
update() {
  logo

  info_out "Enabling maintenance mode"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan down

  if ! sudo git config --global --get-all safe.directory | grep -q -w "${cpgg_dir:-$DEFAULT_DIR}"; then
    info_out "Adding CtrlPanel directory to the git save.directory list"
    sudo git config --global --add safe.directory "${cpgg_dir:-$DEFAULT_DIR}"
  fi

  info_out "Downloading file updates"
  sudo git stash
  sudo git pull

  info_out "Installing Composer dependencies"
  sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

  info_out "Migrating database updates"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan migrate --seed --force

  info_out "Clearing the cache"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan view:clear
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan config:clear

  info_out "Setting permissions"
  sudo chown -R www-data:www-data "${cpgg_dir:-$DEFAULT_DIR}"
  sudo chmod -R 755 "${cpgg_dir:-$DEFAULT_DIR}"

  info_out "Restarting Queue Workers"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan queue:restart

  info_out "Disabling maintenance mode"
  sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan up

  if [[ -z "${cli_mode}" ]]; then
    echo ""
    echo -e " ${BB_GR}${T_BL} Done! ${NC} ${BT_GR}Update finished. Press any key to exit${NC}"
    read -rsn 1 -p " "
  fi
}