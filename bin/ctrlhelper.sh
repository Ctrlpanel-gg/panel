#!/bin/bash
#
# This script is designed to facilitate the tasks of
# installing dependencies and updating CtrlPanel via CLI

# shellcheck disable=SC2034
readonly SCRIPT_VER="0.1.0"
readonly DEFAULT_DIR="/var/www/ctrlpanel"

#######################################
# Colors
#######################################
readonly T_BL="\033[30m" # Blue text
readonly T_RE="\033[31m" # Red text
readonly T_CY="\033[36m" # Cyan text
readonly T_WH="\033[37m" # White text

readonly BT_RE="\033[91m" # Bright red text
readonly BT_GR="\033[92m" # Bright green text
readonly BT_YE="\033[93m" # Bright yellow text
readonly BT_BLU="\033[94m" # Bright blue text
readonly BT_CY="\033[96m" # Bright cyan text
readonly BT_WH="\033[97m" # Bright white text

readonly B_RE="\033[41m" # Red background

readonly BB_GR="\033[102m" # Green bright background
readonly BB_YE="\033[103m" # Yellow bright background
readonly BB_BLU="\033[104m" # Blue bright background
readonly BB_CY="\033[106m" # Cyan bright background

readonly NC="\033[0m" # Reset
readonly TB="\033[1m" # Bold
readonly TU="\033[4m" # Underline

#######################################
# Visual blocks
#######################################
readonly CHECK="${BT_YE}${TB}(${BT_GR}✓${BT_YE})${NC}"
readonly WARN="${BT_YE}${TB}(${BT_RE}!!${BT_YE})${NC}"
readonly INFO="${BB_BLU}${BT_WH} INFO ${NC}"
readonly ERROR="${B_RE}${BT_WH} ERROR ${NC}"

#######################################
# Return an error message in STDERR
# Arguments:
#   Error message
#######################################
error_out() {
  echo -e " ${ERROR} ${T_RE}$*${NC}" >&2
}

#######################################
# Function to ensure the script is run as root without sudo
# Globals:
#   EUID
#   SUDO_USER
#######################################
check_run_as_root_only() {
  if [ "$EUID" -ne 0 ]; then
    error_out "This script must be run as root"
    exit 1
  fi

  # Check if sudo was used directly to execute the script
  if [ -n "$SUDO_COMMAND" ] && [ "$SUDO_COMMAND" != "/usr/bin/su" ]; then
    error_out "Do not use sudo to run this script. Log in as root and run it directly"
    exit 1
  fi
}

#######################################
# Set the directory where CtrlPanel is installed
# Globals:
#   DEFAULT_DIR
#   cpgg_dir
#######################################
set_cpgg_dir() {
  local is_exists=""
  local is_cpgg_root=""
  local is_null=""

  if [[ -z "${cli_mode}" ]]; then
    if [[ ! -d "${DEFAULT_DIR}" ]] && [[ -z "${cpgg_dir}" ]]; then
      while true; do
        # Message that the user will see by default, if he specifies a
        # non-existent directory or not root CtrlPanel directory
        echo ""
        if [[ -z "${is_exists}" ]] && [[ -z "${is_cpgg_root}" ]] || [[ "${is_null}" == "true" ]]; then
          echo -e " ${T_CY}Default directory wasn't found. Specify directory \
where your CtrlPanel is installed (e.g. /var/www/ctrlpanel)${NC}"
        elif [[ ${is_exists} == false ]]; then
          echo -e " ${T_CY}Directory ${BT_YE}${cpgg_dir}${T_CY} doesn't exist. \
Specify directory where your CtrlPanel is installed \
(e.g. /var/www/ctrlpanel)${NC}"
        elif [[ ${is_cpgg_root} == false ]]; then
          echo -e " ${BT_YE}${cpgg_dir}${T_CY} is not a root CtrlPanel \
directory. Specify directory where your CtrlPanel is installed \
(e.g. /var/www/ctrlpanel)${NC}"
        fi

        read -rep " > " cpgg_dir

        # Deleting / at the end of the specified directory
        cpgg_dir="${cpgg_dir%/}"

        # Resetting validation values before validation
        is_null=""
        is_exists=""
        is_cpgg_root=""

        # Validation of directory specified by user
        if [[ "${cpgg_dir}" == "" ]]; then
          is_null="true"
        elif [[ ! -d "${cpgg_dir}" ]]; then
          is_exists="false"
        elif [[ ! -f "${cpgg_dir}/config/app.php" ]]; then
          is_cpgg_root="false"
        else
          break
        fi

      done
    fi
  else
    # Error if default directory is not found and CtrlPanel root directory is
    # not specified when using the CLI mode
    if [[ ! -d "${DEFAULT_DIR}" ]] && [[ -z "${cpgg_dir}" ]]; then
      error_out "Default directory wasn't found. Specify directory where your \
CtrlPanel is installed using ${BT_YE}--cpgg-dir${T_RE} argument"
      exit 1
    fi
  fi
}

check_run_as_root_only

#######################################
# Handling startup arguments
#######################################
while [[ $# -gt 0 ]]; do
  case "$1" in
  --cli)
    cli_mode="true"
    shift
    ;;
  --cpgg-dir=*)
    cpgg_dir="${1#*=}"
    cpgg_dir="${cpgg_dir%/}"
    shift

    if [[ "${cpgg_dir}" == "" ]]; then
      error_out "Argument ${BT_YE}--cpgg-dir${T_RE} can't be empty!"
      exit 1
    elif [[ ! -d "${cpgg_dir}" ]]; then
      error_out "Directory ${BT_YE}${cpgg_dir}${T_RE} doesn't exist."
      exit 1
    elif [[ ! -f "${cpgg_dir}/config/app.php" ]]; then
      error_out "${BT_YE}${cpgg_dir}${T_RE} is not a root CtrlPanel directory."
      exit 1
    else
      continue
    fi
    ;;
  --force)
    force="true"
    shift
    ;;
  --install=*)
    if [[ -n "${update}" ]]; then
      error_out "You can't use ${BT_YE}--install${T_RE} with \
${BT_YE}--update${T_RE} argument"
      exit 1
    fi

    install="${1#*=}"
    shift

    if [[ "${install}" == "" ]]; then
      error_out "Argument ${BT_YE}--install${T_RE} can't be empty!"
      exit 1
    elif [[ "${install}" != "full" && "${install}" != "min" ]]; then
      error_out "Invalid option ${BT_YE}${install}${T_RE} for \
${BT_YE}--install${T_RE} argument. Valid values are only \
${BT_YE}${TU}full${NC}${T_RE} or ${BT_YE}${TU}min"
      exit 1
    fi
    ;;
  --update)
    if [[ -n "${install}" ]]; then
      error_out "You can't use ${BT_YE}--update${T_RE} with \
${BT_YE}--install${T_RE} argument"
      exit 1
    fi

    update="true"
    shift
    ;;
  --help)
    echo -e " ${BT_WH}Usage: ${T_CY}$0 ${BT_CY}[options]${NC}"
    echo -e " ${BT_WH}Options:${NC}"
    echo -e "   ${BT_CY}--cli                   \
${BT_WH}Use CLI mode. It does not have CLI-GUI interface, and all actions are \
specified using action arguments${NC}"
    echo -e "   ${BT_CY}--cpgg-dir=<dir>        \
${BT_WH}Allows you to specify the root directory of the CtrlPanel${NC}"
    echo -e "   ${BT_CY}--force                 \
${BT_WH}Performs an action without confirmation (applicable for --install and \
--update arguments in CLI mode)${NC}"
    echo -e "   ${BT_CY}--install=<full|min>    \
${BT_WH}Perform installation. Valid values are full or min${NC}"
    echo -e "   ${BT_CY}--update                \
${BT_WH}Perform an update${NC}"
    echo -e "   ${BT_CY}--help                  \
${BT_WH}Display this help message${NC}"
    exit 0
    ;;
  *)
    error_out "Argument ${BT_YE}$1${T_RE} not exists. Use \
${BT_YE}--help${T_RE} to display all available arguments"
    exit 1
    ;;
  esac
done

set_cpgg_dir

# shellcheck source=/dev/null
source "${cpgg_dir:-$DEFAULT_DIR}/bin/ctrlhelper_sub_scripts/menus.sh" \
  || {
    error_out "Source files could not be added! Are you sure you are using \
script for version 1.0 or above of CtrlPanel? Please try to run the script \
again, if the error persists, create support forum post on CtrlPanel's \
Discord server!"
    exit 1
}
# shellcheck source=/dev/null
source "${cpgg_dir:-$DEFAULT_DIR}/bin/ctrlhelper_sub_scripts/functions.sh" \
    || {
    error_out "Source files could not be added! Are you sure you are using \
script for version 1.0 or above of CtrlPanel? Please try to run the script \
again, if the error persists, create support forum post on CtrlPanel's \
Discord server!"
    exit 1
}

cd "${cpgg_dir:-$DEFAULT_DIR}" \
  || {
    error_out "An error occurred while trying to switch to the working \
directory. Please try to run the script again, if the error persists, create \
support forum post on CtrlPanel's Discord server!"
    exit 1
}

if [[ -z "${cli_mode}" ]]; then
  save_terminal

  get_version
  update_needed_checker

  main_menu

  restore_terminal
else
  if [[ "${install}" == "full" ]]; then
    if [[ "${force}" == "true" ]]; then
      install_deps
    else
      confirm_dialog \
      "${BT_YE}This action will install all the necessary dependencies such as \
PHP, Redis, MariaDB and others, as well as install composer files.
 You will still have to create MySQL user and configure nginx yourself.

 ${BT_GR}Below is a list of all packages that will be installed${NE}" \
      "${T_WH}software-properties-common curl apt-transport-https \
ca-certificates gnupg lsb-release php8.3 \
php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} \
mariadb-server nginx redis-server git${NC}" \
      "install_deps" \
      "exit 0"
    fi
  elif [[ "${install}" == "min" ]]; then
    if [[ "${force}" == "true" ]]; then
      install_deps "true"
    else
      confirm_dialog \
      "${BT_YE}This action will install all the necessary dependencies such as \
PHP, Redis, Composer and others, as well as install composer files.

 ${BT_GR}Below is a list of all packages that will be installed${NE}" \
      "${T_WH}software-properties-common curl apt-transport-https \
ca-certificates gnupg lsb-release php8.3 \
php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} \
redis-server git${NC}" \
      "install_deps \"true\"" \
      "exit 0"
    fi
  elif [[ "${update}" == "true" ]]; then
    if [[ "${force}" == "true" ]]; then
      update
    else
      confirm_dialog \
      "${B_RE}${BT_WH} This action cannot be undone, create backup of the \
database before updating! ${NC}" \
      "${B_RE}${BT_WH} It will also remove all installed themes and addons. ${NC}" \
      "update" \
      "exit 0"
    fi
  else
    error_out \
    "You have not specified the action you want to do! Use \
${BT_YE}--help${T_RE} to display all available arguments"
    exit 1
  fi
fi
