#!/bin/bash
#
# The auxiliary file for CtrlHelper script.
# Contains the CLI-GUI parts of the interface

#######################################
# Logo to display in the CLI-GUI
#######################################
logo() {
  if [[ -z "${cli_mode}" ]]; then
    clear
  fi
  echo -e "${BT_CY}    ________       ______                   __            ${NC}"
  echo -e "${BT_CY}   / ____/ /______/ / __ \____ _____  ___  / /____ _____ _${NC}"
  echo -e "${BT_CY}  / /   / __/ ___/ / /_/ / __ \`/ __ \/ _ \/ // __ \`/ __ \`/${NC}"
  echo -e "${T_CY} / /___/ /_/ /  / / ____/ /_/ / / / /  __/ // /_/ / /_/ / ${NC}"
  echo -e "${T_CY} \____/\__/_/  /_/_/    \__,_/_/ /_/\___/_(_)__, /\__, /  ${NC}"
  echo -e "${T_CY}                                           /____//____/   ${NC}"
  echo ""
}

#######################################
# Displaying the current version of the script and CtrlPanel under the logo
# Globals:
#   SCRIPT_VER
#   PANEL_VER
#######################################
logo_version() {
  echo -e " ${BT_YE}Script    version:${NC}${BT_WH} ${SCRIPT_VER}${NC}"
  echo -e " ${BT_YE}CtrlPanel version:${NC}${BT_WH} ${PANEL_VER}${NC}"
  echo ""
}

#######################################
# Message to the user about whether they need to update, or if they are
# already using latest version
# Globals:
#   is_update_needed
#######################################
logo_message() {
  # shellcheck disable=SC2154
  if [[ ${is_update_needed} == 0 ]]; then
    echo -e " ${CHECK} ${BT_GR}You are using the latest version! \
No update required.${NC}"
    echo ""
  elif [[ ${is_update_needed} == 1 ]]; then
    echo -e " ${WARN} ${BT_RE}New version available! You can update right now \
by selecting ${BT_YE}${TU}Update${NC} ${BT_RE}option.${NC}"
    echo ""
  elif [[ ${is_update_needed} == 2 ]]; then
    echo -e " ${CHECK} ${BT_GR}You are using a newer version! Most likely you \
have a development version installed.${NC}"
    echo ""
  fi
}

#######################################
# Action confirmation dialog
# Arguments:
#   First line of the message
#   Second line of the message
#   Action that will be performed upon confirmation
#   Exit action
#   Unknown choice validation response
#   Previous choice if $unknown_choice is true
#######################################
confirm_dialog() {
  local choice
  local message_line1="$1"
  local message_line2="$2"
  local action="$3"
  local exit_action="$4"
  local unknown_choice="$5"
  local previous_choice="$6"

  logo
  echo -e " ${message_line1}"
  if [[ -n "${message_line2}" ]]; then
    echo -e " ${message_line2}"
  fi
  echo ""
  echo -e " ${BT_WH}Continue? (Y/n)${NC}"
  if [[ "${unknown_choice}" == "true" ]]; then
    echo -e " ${T_RE}Unknown choice ${BT_YE}${TU}${previous_choice}${NC}"
  fi
  read -rp " > " choice

  case "${choice}" in
  y | Y) ${action} ;;
  n | N) ${exit_action} ;;
  *)
    confirm_dialog \
    "${message_line1}" \
    "${message_line2}" \
    "${action}" \
    "${exit_action}" \
    "true" \
    "${choice}"
    ;;
  esac
}

#######################################
# Main menu in CLI-GUI mode
#######################################
main_menu() {
  local choice=""

  logo
  logo_version
  logo_message
  echo -e " ${BB_CY} ${T_BL}Select an option: ${NC}"
  echo -e "   ${BT_WH}1. Install dependencies${NC}"
  echo -e "   ${BT_WH}2. Update${NC}"
  echo -e "   ${BT_WH}3. Info & Help${NC}"
  echo -e "   ${BT_WH}q. Quit${NC}"
  echo ""
  read -rp " > " choice

  case ${choice} in
  1) install_menu ;;
  2)
    confirm_dialog "${B_RE}${BT_WH} This action cannot be undone, create \
backup of the database before updating! ${NC}" \
    "${B_RE}${BT_WH} It will also remove all installed themes and addons. ${NC}" \
    "update" \
    "main_menu"
  ;;
  3) info_help_menu ;;
  q) restore_terminal ;;
  *) main_menu ;;
  esac
}

#######################################
# Install dependencies menu in CLI-GUI mode
#######################################
install_menu() {
  local choice=""

  logo
  echo -e " ${BT_YE}This action will install all the necessary dependencies \
such as PHP, Redis, MariaDB and others, as well as install composer files.${NC}"
  echo -e " ${BT_YE}You will still need to create MySQL user/database and \
configure nginx yourself.${NC}"
  echo ""
  echo -e " ${BB_CY} ${T_BL}Select an option: ${NC}"
  echo -e "   ${BT_WH}1. Full install${NC}"
  echo -e "   ${BT_WH}2. Minimal install (Not include MariaDB and nginx)${NC}"
  echo -e "   ${BT_WH}b. Back to main menu${NC}"
  echo ""
  read -rp " > " choice

  case "${choice}" in
  1)
    confirm_dialog \
    "${BT_YE}You are going to install full set of dependencies, below is a \
list of all packages that will be installed" \
    "${T_WH}software-properties-common curl apt-transport-https \
ca-certificates gnupg lsb-release php8.3 \
php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} \
mariadb-server nginx redis-server git${NC}" \
    "install_deps" \
    "install_menu"
    ;;
  2)
    confirm_dialog \
    "${BT_YE}You are going to install minimal set of dependencies, below is a \
list of all packages that will be installed${NC}" \
    "${T_WH}software-properties-common curl apt-transport-https \
ca-certificates gnupg lsb-release php8.3 \
php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} \
redis-server git${NC}" \
    "install_deps \"true\"" \
    "install_menu"
    ;;
  b) main_menu ;;
  *) install_menu ;;
  esac
}

#######################################
# Info & Help menu in CLI-GUI mode
#######################################
info_help_menu() {
  local choice=""

  logo
  logo_version
  echo -e " ${BB_BLU}${BT_WH} Info ${NC}"
  echo -e "   ${BT_WH}This script is designed to simplify the installation of \
dependencies and updating the CtrlPanel.${NC}"
  echo -e "   ${BT_WH}It can be executed both in CLI-GUI interface mode and in \
CLI mode with an action argument specified${NC}"
  echo ""
  echo -e " ${BB_CY}${T_BL} Help ${NC}"
  echo -e "   ${BT_WH}Usage: ${T_CY}$0 ${BT_CY}[options]${NC}"
  echo -e "   ${BT_WH}Options:${NC}"
  echo -e "     ${BT_CY}--cli                   \
${BT_WH}Use CLI mode. It does not have CLI-GUI interface, and all actions are \
specified using action arguments${NC}"
  echo -e "     ${BT_CY}--cpgg-dir=<dir>        \
${BT_WH}Allows you to specify the root directory of the CtrlPanel${NC}"
  echo -e "     ${BT_CY}--force                 \
${BT_WH}Performs an action without confirmation (applicable for --install and \
--update arguments in CLI mode)${NC}"
  echo -e "     ${BT_CY}--install=<full|min>    \
${BT_WH}Perform installation. Valid values are full or min${NC}"
  echo -e "     ${BT_CY}--update                \
${BT_WH}Perform an update${NC}"
  echo -e "     ${BT_CY}--help                  \
${BT_WH}Display help message${NC}"
  echo ""
  echo -e " ${BB_YE}${T_BL} Credits ${NC}"
  echo -e "     ${BT_WH}${TB}Made by MrWeez and Contributors with \
${NC}${BT_RE}♥${NC}"
  echo ""
  echo " Press any key to return to the main menu"

  read -rsn 1 -p " " choice
  case "${choice}" in
  *) main_menu ;;
  esac
}
