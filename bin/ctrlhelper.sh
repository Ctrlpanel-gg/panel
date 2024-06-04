#!/bin/bash

# System vars | DO NOT TOUCH!!!
readonly SCRIPT_VER="0.0.1"
readonly DEFAULT_DIR="/var/www/controlpanel/"
cpgg_dir=""
dir_null=""
cli_mode="false"

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
    exit
}

# Setting root CtrlPanel directory using the --cpgg-dir=/path/to/folder parameter
while [[ $# -gt 0 ]]; do
    case "$1" in
    --cli)
        cli_mode="true"
        shift
        ;;
    --cpgg-dir=*)
        cpgg_dir="${1#*=}"
        shift

        # Check if --cpgg-dir not empty
        if [ -z "$cpgg_dir" ]; then
            echo " Argument --cpgg-dir can't be empty!"
            exit 1
        fi
        # Check if directory exists
        if [ ! -d "$cpgg_dir" ]; then
            echo " $cpgg_dir directory does not exist. Try again"
            exit 1
        fi
        if [ ! -f "$cpgg_dir/config/app.php" ]; then
            echo " $cpgg_dir is not a root CtrlPanel directory. Try again"
            exit 1
        fi
        ;;
    *)
        shift
        ;;
    esac
done

# Do terminal actions only if $cli_mode = false
if [ "$cli_mode" == "false" ]; then
    # Restoring terminal content after ^C
    trap restore_terminal SIGINT

    # Save terminal
    tput smcup
fi

if [ "$cli_mode" == "false" ]; then
    # Set root CtrlPanel directory using CLI-GUI
    ## If $DEFAULT_DIR doesn't exists and $cpgg_dir var isn't specified
    if [ ! -d "$DEFAULT_DIR" ] && [ -z "$cpgg_dir" ]; then
        while true; do
            # If $cpgg_dir var isn't specified, show "Default not exists"
            if [ -z "$cpgg_dir" ]; then
                logo
                echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
            fi
            # If $dir_null is true, show "Cannot be empty"
            if [ "$dir_null" == "true" ]; then
                logo
                echo " You have not specified a directory, it cannot be empty!"
            fi
            # Reading directory specified by the user
            read -rp " > " cpgg_dir

            # If $cpgg_dir var isn't specified set $dir_null to true
            if [ -z "$cpgg_dir" ]; then
                dir_null="true"
                continue
            fi

            # If $cpgg_dir exists set $dir_null to null and continue script
            if [ -d "$cpgg_dir" ]; then
                dir_null=""
                break
            # If $cpgg_dir doesn't exists, show logo with "Directory does not exist" message
            else
                logo
                echo " $cpgg_dir directory does not exist. Try again"
                dir_null=""
            fi
        done
    fi
else
    # Set root CtrlPanel directory using in CLI mode
    ## If $DEFAULT_DIR doesn't exists and $cpgg_dir var isn't specified
    if [ ! -d "$DEFAULT_DIR" ] && [ -z "$cpgg_dir" ]; then
        echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed using --cpgg-dir=/path/to/cpgg argument"
        exit 1
    fi
fi

# Getting curent CtrlPanel version
PANEL_VER=$(grep -oP "'version' => '\K[^']+" "${cpgg_dir:-$DEFAULT_DIR}/config/app.php")
readonly PANEL_VER
# Getting latest CtrlPanel version
PANEL_LATEST_VER=$(curl -s https://api.github.com/repos/ctrlpanel-gg/panel/tags | jq -r '.[0].name')
readonly PANEL_LATEST_VER

# Comparing current and latest versions
## -1 => Version above the latest one is installed
##  0 => Latest version is installed
##  1 => Update available
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
        if ((${current_parts[i]} < ${latest_parts[i]})); then
            echo "1"
            return 1 # Update needed
        elif ((${current_parts[i]} > ${latest_parts[i]})); then
            echo "-1"
            return -1 # A newer version is installed
        fi
    done

    echo "0"
    return 0 # Latest version is installed
}

is_update_needed=$(version_compare "$PANEL_VER" "$PANEL_LATEST_VER")
readonly is_update_needed

# Logo with versions for CLI-GUI
logo_version() {
    echo " Script    version: $SCRIPT_VER"
    echo " CtrlPanel version: $PANEL_VER"
    echo ""
}

# Message about available Update
logo_message() {
    if [[ $is_update_needed == 1 ]]; then
        echo " New version available! You can update right now by selecting \"Update\" option."
        echo ""
    elif [[ $is_update_needed == -1 ]]; then
        echo " You are using a newer version! Most likely you have a development branch installed."
        echo ""
    fi
}

if [ "$cli_mode" == "false" ]; then
    # Main menu
    main_menu() {
        logo
        logo_version
        logo_message
        echo " Select an option:"
        echo " 1. Install dependencies"
        echo " 2. Update"
        echo " 3. Uninstall"
        echo " 4. Info & Help"
        echo " 0. Exit"
        echo ""
        read -rp " > " main_menu_choice

        case $main_menu_choice in
        1)
            menu_1
            ;;
        2)
            menu_2
            ;;
        3)
            menu_3
            ;;
        4)
            menu_4
            ;;
        0)
            restore_terminal
            ;;
        *)
            main_menu
            ;;
        esac
    }

    menu_1() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    menu_2() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    menu_3() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    menu_4() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    main_menu
fi
# Restoring terminal after succes if $cli_mode = false
if [ "$cli_mode" == "false" ]; then
    restore_terminal
fi