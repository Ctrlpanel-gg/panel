#!/bin/bash
#
# This script is designed to facilitate the tasks of
# installing dependencies and updating CtrlPanel via CLI
#
# Made with love by MrWeez
# Contact me
#   GitHub: https://github.com/MrWeez
#   Discord: @mrweez_
#   Email: contact@mrweez.dev

# shellcheck disable=SC2034
readonly SCRIPT_VER="0.5.12-dev"
readonly DEFAULT_DIR="/var/www/ctrlpanel"

#######################################
# Return an error message in STDERR
# Arguments:
#   Error message
#######################################
error_out() {
  echo "$*" >&2
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

  if [[ -z "$cli_mode" ]]; then
    if [[ ! -d "$DEFAULT_DIR" ]] && [[ -z "$cpgg_dir" ]]; then
      while true; do
        # Message that the user will see by default, if he specifies a
        # non-existent directory or not root CtrlPanel directory
        if [[ -z "$is_exists" ]] && [[ -z "$is_cpgg_root" ]] || [[ "$is_null" == "true" ]]; then
          echo " Default directory wasn't found. Specify directory where your \
CtrlPanel is installed (e.g. /var/www/ctrlpanel)"
        elif [[ $is_exists == false ]]; then
          echo " Directory $cpgg_dir doesn't exist. Specify directory where \
your CtrlPanel is installed (e.g. /var/www/ctrlpanel)"
        elif [[ $is_cpgg_root == false ]]; then
          echo " $cpgg_dir is not a root CtrlPanel directory. Specify \
directory where your CtrlPanel is installed (e.g. /var/www/ctrlpanel)"
        fi

        read -rep " > " cpgg_dir

        # Deleting / at the end of the specified directory
        cpgg_dir="${cpgg_dir%/}"

        # Resetting values before validation
        is_null=""
        is_exists=""
        is_cpgg_root=""

        # Validation of directory specified by user
        if [[ "$cpgg_dir" == "" ]]; then
          is_null="true"
        elif [[ ! -d "$cpgg_dir" ]]; then
          is_exists="false"
        elif [[ ! -f "$cpgg_dir/config/app.php" ]]; then
          is_cpgg_root="false"
        else
          break
        fi

      done
    fi
  else
    # Error if default directory is not found and CtrlPanel root directory is
    # not specified when using the CLI mode
    if [[ ! -d "$DEFAULT_DIR" ]] && [[ -z "$cpgg_dir" ]]; then
      error_out " ERROR: Default directory wasn't found. Specify directory \
where your CtrlPanel is installed using --cpgg-dir argument"
      exit 1
    fi
  fi
}

# Handling startup arguments
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

    if [[ "$cpgg_dir" == "" ]]; then
      error_out " ERROR: Argument --cpgg-dir can't be empty!"
      exit 1
    elif [[ ! -d "$cpgg_dir" ]]; then
      error_out " ERROR: Directory $cpgg_dir doesn't exist."
      exit 1
    elif [[ ! -f "$cpgg_dir/config/app.php" ]]; then
      error_out " ERROR: $cpgg_dir is not a root CtrlPanel \
directory."
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
    if [[ -n "$update" ]]; then
      error_out " ERROR: You can't use --install with --update \
argument"
      exit 1
    fi

    install="${1#*=}"
    shift

    if [[ "$install" == "" ]]; then
      error_out " ERROR: Argument --install can't be empty!"
      exit 1
    elif [[ "$install" != "full" && "$install" != "min" ]]; then
      error_out " ERROR: Invalid option $install for --install \
argument. Valid values are only full or min"
      exit 1
    fi
    ;;
  --update)
    if [[ -n "$install" ]]; then
      error_out " ERROR: You can't use --update with --install \
argument"
      exit 1
    fi

    update="true"
    shift
    ;;
  --help)
    echo " Usage: $0 [options]"
    echo " Options:"
    echo "   --cli                   Use CLI mode. It does not have CLI-GUI \
interface, and all actions are specified using action arguments"
    echo "   --cpgg-dir=<dir>        Allows you to specify the root directory \
of the CtrlPanel"
    echo "   --force                 Performs an action without confirmation \
(applicable for --install and --update arguments)"
    echo "   --install=<full|min>    Perform installation. Valid values are \
full or min"
    echo "   --update                Perform an update"
    echo "   --help                  Display this help message"
    exit 0
    ;;
  *)
    error_out " ERROR: Argument $1 not exists. \
Use --help to display all available arguments"
    exit 1
    ;;
  esac
done

set_cpgg_dir

# shellcheck source=/dev/null
source "${cpgg_dir:-$DEFAULT_DIR}/bin/ctrlhelper_sub_scripts/menus.sh" \
  || {
    error_out " ERROR: Source files could not be added! Are you sure you are \
using script for version 0.10 or above of CtrlPanel? Please try to run the \
script again, if the error persists, create support forum post on \
CtrlPanel's Discord server!"
    exit 1
}
# shellcheck source=/dev/null
source "${cpgg_dir:-$DEFAULT_DIR}/bin/ctrlhelper_sub_scripts/functions.sh" \
    || {
    error_out " ERROR: Source files could not be added! Are you sure you are \
using script for version 0.10 or above of CtrlPanel? Please try to run the \
script again, if the error persists, create support forum post on \
CtrlPanel's Discord server!"
    exit 1
}

cd "${cpgg_dir:-$DEFAULT_DIR}" \
  || {
    error_out " ERROR: An error occurred while trying to switch to the working \
directory. Please try to run the script again, if the error persists, \
create support forum post on CtrlPanel's Discord server!"
    exit 1
}

if [[ -z "$cli_mode" ]]; then
  save_terminal
fi

if [[ -z "$cli_mode" ]]; then
  get_version
  update_needed_checker

  main_menu

  restore_terminal
else
  if [[ "$install" == "full" ]]; then
    if [[ "$force" == "true" ]]; then
      install_deps
    else
      confirm_dialog "This action will install all the necessary dependencies \
such as PHP, Redis, MariaDB and others, as well as install composer \
files." "You will still have to create MySQL user and configure nginx \
yourself." "install_deps"
    fi
  elif [[ "$install" == "min" ]]; then
    if [[ "$force" == "true" ]]; then
      install_deps "true"
    else
      confirm_dialog "This action will install all the necessary dependencies \
such as PHP, Redis, Composer and others, as well as install composer \
files." "" "install_deps \"true\""
    fi
  elif [[ "$update" == "true" ]]; then
    if [[ "$force" == "true" ]]; then
      update
    else
      confirm_dialog "This action cannot be undone, create backup of the \
database before updating! It will also remove all installed themes \
and addons." "" "update"
    fi
  else
    echo " ERROR: You have not specified the action you want to do! \
Use --help to display all available arguments"
    exit 1
  fi
fi
