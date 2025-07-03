#!/bin/bash
#
# The auxiliary file for CtrlHelper script.
# Contains important functions for the main script to work

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
  echo -e " ${INFO} ${BT_WH}$*${NC}"
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

  # Add zeros to the shorter version (e.g. 1.0 => 1.0.0)
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
# 
#######################################
check_distro() {
  local choice
  local unknown_choice="$1"
  local previous_choice="$2"
  distro=$(lsb_release -is)
  distro="${distro,,}"

  if [[ "${distro}" != "debian" && "${distro}" != "ubuntu" ]]; then
    logo

    error_out "Your OS is not supported! You can continue the installation in compatibility mode, but in this case it is not guaranteed that all packages will be installed successfully"
    echo -e ""
    echo -e " So that we can add support for your OS, please let us know the information below"
    echo -e "   Detected OS: ${distro}"
    echo -e "   Detected OS (full): $(lsb_release -sd)"
    echo ""
    echo -e " ${BB_CY} ${T_BL}Select an option: ${NC}"
    echo -e "   ${BT_WH}1. Continue in Debian compatibility mode${NC}"
    echo -e "   ${BT_WH}2. Continue in Ubuntu compatibility mode${NC}"
    echo -e "   ${BT_WH}q. Quit${NC}"
    echo ""
    if [[ "${unknown_choice}" == "true" ]]; then
      echo -e " ${T_RE}Unknown choice ${BT_YE}${TU}${previous_choice}${NC}"
    fi
    read -rp " > " choice

    case ${choice} in
    1) distro="debian" ;;
    2) distro="ubuntu" ;;
    q) restore_terminal ;;
    *) check_distro "true" "${choice}" ;;
    esac
  fi
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
  apt -y -qq install software-properties-common curl apt-transport-https ca-certificates gnupg lsb-release

  check_distro

  if [[ "$distro" == "debian" ]]; then
    if [[ ! -f "/usr/share/keyrings/deb.sury.org-php.gpg" ]]; then
      info_out "Adding PHP repository keyring"
      curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
    fi

    if [[ ! -f "/etc/apt/sources.list.d/deb.sury.org-php.list" ]]; then
      info_out "Adding PHP repository"
      sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/deb.sury.org-php.list'
    fi
  elif [[ "$distro" == "ubuntu" ]]; then
    info_out "Adding PHP repository"
    LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
  fi

  if [[ ! -f "/usr/share/keyrings/redis-archive-keyring.gpg" ]]; then
    info_out "Adding Redis repository"
    curl -fsSL https://packages.redis.io/gpg | gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
    echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" \
      | tee /etc/apt/sources.list.d/redis.list
  fi

  if [[ "$distro" == "ubuntu" && "$(grep '^VERSION_ID=' /etc/os-release | cut -d'=' -f2 | tr -d '"')" != "24.04"  ]]; then
    if [[ -z "${minimal}" ]]; then
      info_out "Adding MariaDB repository"
      curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | bash
    elif [[ -n "${minimal}" && "${minimal}" != "true" ]]; then
      error_out "Invalid argument ${minimal} for install_deps function. Please, report to developers!"
      exit 1
    fi
  fi

  info_out "Running \"apt update\""
  apt update

  info_out "Installing dependencies"
  if [[ "${minimal}" ]]; then
    apt -y -qq install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} redis-server git
  elif [[ -z "${minimal}" ]]; then
    apt -y -qq install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} mariadb-server nginx redis-server git
  else
    error_out "Invalid argument ${minimal} for install_deps function. Please, report to developers!"
    exit 1
  fi

  info_out "Installing Composer"
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

  info_out "Installing Composer dependencies to the CtrlPanel"
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

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
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan down

  if ! git config --global --get-all safe.directory | grep -q -w "${cpgg_dir:-$DEFAULT_DIR}"; then
    info_out "Adding CtrlPanel directory to the git save.directory list"
    git config --global --add safe.directory "${cpgg_dir:-$DEFAULT_DIR}"
  fi

  info_out "Downloading file updates"
  git stash
  git pull

  info_out "Installing Composer dependencies"
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

  info_out "Migrating database updates"
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan migrate --seed --force

  info_out "Clearing the cache"
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan view:clear
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan config:clear

  info_out "Setting permissions"
  chown -R www-data:www-data "${cpgg_dir:-$DEFAULT_DIR}"
  chmod -R 755 "${cpgg_dir:-$DEFAULT_DIR}"

  info_out "Restarting Queue Workers"
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan queue:restart

  info_out "Disabling maintenance mode"
  php "${cpgg_dir:-$DEFAULT_DIR}"/artisan up

  if [[ -z "${cli_mode}" ]]; then
    echo ""
    echo -e " ${BB_GR}${T_BL} Done! ${NC} ${BT_GR}Update finished. Press any key to exit${NC}"
    read -rsn 1 -p " "
  fi
}