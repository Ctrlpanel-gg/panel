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