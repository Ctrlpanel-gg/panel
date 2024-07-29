#!/bin/bash
#
# The auxiliary file for CtrlHelper script.
# Contains the CLI-GUI parts of the interface
#
# Made with love by MrWeez
# Contact me
#   GitHub: https://github.com/MrWeez
#   Discord: @mrweez_
#   Email: contact@mrweez.dev

#######################################
# Logo to display in the CLI-GUI
#######################################
logo() {
  clear
  echo "    ________       ______                   __            "
  echo "   / ____/ /______/ / __ \____ _____  ___  / /____ _____ _"
  echo "  / /   / __/ ___/ / /_/ / __ \`/ __ \/ _ \/ // __ \`/ __ \`/"
  echo " / /___/ /_/ /  / / ____/ /_/ / / / /  __/ // /_/ / /_/ / "
  echo " \____/\__/_/  /_/_/    \__,_/_/ /_/\___/_(_)__, /\__, /  "
  echo "                                           /____//____/   "
  echo ""
}

#######################################
# Displaying the current version of the script and CtrlPanel under the logo
# Globals:
#   SCRIPT_VER
#   PANEL_VER
#######################################
logo_version() {
  echo " Script    version: $SCRIPT_VER"
  echo " CtrlPanel version: $PANEL_VER"
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
  if [[ $is_update_needed == 0 ]]; then
    echo " You are using the latest version! No update required."
    echo ""
  elif [[ $is_update_needed == 1 ]]; then
    echo " New version available! You can update right now by selecting 
    \"Update\" option."
    echo ""
  elif [[ $is_update_needed == 2 ]]; then
    echo " You are using a newer version! Most likely you have a development 
    branch installed."
    echo ""
  fi
}

#######################################
# Action confirmation dialog
# Arguments:
#   First line of the message
#   Second line of the message
#   Action that will be performed upon confirmation
#######################################
confirm_dialog() {
  local message_line1="$1"
  local message_line2="$2"
  local function="$3"

  echo " $message_line1"
  if [[ -n "$message_line2" ]]; then
    echo " $message_line2"
  fi
  echo " Do you want to continue? (Y/n)"
  read -rp " > " choice

  case "$choice" in
  y | Y) $function ;;
  n | N) exit 0 ;;
  *)
    echo " ERROR: Unknown choice $choice"
    echo ""
    confirm_dialog "$message_line1" "$message_line2" "$function"
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
  echo " Select an option:"
  echo " 1. Install dependencies"
  echo " 2. Update"
  echo " 3. Info & Help"
  echo " 0. Exit"
  echo ""
  read -rp " > " choice

  case $choice in
  1) install_menu ;;
  2) update_menu ;;
  3) info_help_menu ;;
  0) restore_terminal ;;
  *) main_menu ;;
  esac
}

#######################################
# Install dependencies menu in CLI-GUI mode
#######################################
install_menu() {
  local choice=""

  logo
  echo " This action will install all the necessary dependencies such as PHP, 
  Redis, MariaDB and others, as well as install composer files."
  echo " You will still have to create MySQL user and configure nginx 
  yourself."
  echo ""
  echo " Select the installation option:"
  echo " 1. Full install"
  echo " 2. Minimal install (Not include MariaDB and nginx)"
  echo " 0. Exit to main menu"
  read -rp " > " choice

  case "$choice" in
  1) install_deps ;;
  2) install_deps "true" ;;
  0) main_menu ;;
  *) install_menu ;;
  esac
}

#######################################
# Update menu in CLI-GUI mode
#######################################
update_menu() {
  local choice=""

  logo
  echo " This action cannot be undone, create backup of the database before 
  updating! It will also remove all installed themes and addons."
  echo " Do you want to continue? (Y/n)"
  read -rp " > " choice

  case "$choice" in
  y | Y) update ;;
  n | N) main_menu ;;
  *) update_menu ;;
  esac
}

#######################################
# Info & Help menu in CLI-GUI mode
#######################################
info_help_menu() {
  logo
  echo " In dev"
  sleep 3
  main_menu
}
