#!/bin/bash

# System vars | DO NOT TOUCH!!!
SCRIPT_VER="0.0.1"
DEFAULT_DIR="/var/www/controlpanel/"
CPGG_DIR=""
DIR_NULL=""

# Logo for CLI-GUI
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

# Function for restoring terminal content after the script is completed or closed
restore_terminal() {
    tput rmcup
    if [[ -n $1 ]]; then
        $1
    fi
    exit
}

# Restoring terminal content after ^C
trap restore_terminal SIGINT

# Save terminal
tput smcup

# Setting root CtrlPanel directory using the --cpgg-dir=/path/to/folder parameter
while [[ $# -gt 0 ]]; do
    case "$1" in
    --cpgg-dir=*)
        CPGG_DIR="${1#*=}"
        shift

        # Check if directory exists
        if [ ! -d "$CPGG_DIR" ]; then
            CPGGDIR_SET_ERR="echo $CPGG_DIR directory does not exist. Try again"
            restore_terminal "$CPGGDIR_SET_ERR"
        fi
        if [ ! -f "$CPGG_DIR/config/app.php" ]; then
            NOT_CPGG_ROOT_ERR="echo $CPGG_DIR is not a root CtrlPanel directory. Try again"
            restore_terminal "$NOT_CPGG_ROOT_ERR"
        fi
        ;;
    *)
        shift
        ;;
    esac
done

# Set root CtrlPanel directory using CLI-GUI
## If $DEFAULT_DIR doesn't exists and $CPGG_DIR var isn't specified
if [ ! -d "$DEFAULT_DIR" ] && [ -z "$CPGG_DIR" ]; then
    while true; do
        # If $CPGG_DIR var isn't specified, show "Default not exists"
        if [ -z "$CPGG_DIR" ]; then
            logo
            echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
        fi
        # If $DIR_NULL is true, show "Cannot be empty"
        if [ "$DIR_NULL" == "true" ]; then
            logo
            echo " You have not specified a directory, it cannot be empty!"
        fi
        # Reading directory specified by the user
        read -rp " > " CPGG_DIR

        # If $CPGG_DIR var isn't specified set $DIR_NULL to true
        if [ -z "$CPGG_DIR" ]; then
            DIR_NULL="true"
            continue
        fi

        # If $CPGG_DIR exists set $DIR_NULL to null and continue script
        if [ -d "$CPGG_DIR" ]; then
            DIR_NULL=""
            break
        # If $CPGG_DIR doesn't exists, show logo with "Directory does not exist" message
        else
            logo
            echo " $CPGG_DIR directory does not exist. Try again"
            DIR_NULL=""
        fi
    done
fi

# Restoring terminal after succes
restore_terminal