#!/bin/bash

# System vars | DO NOT TOUCH!!!
readonly SCRIPT_VER="0.0.1"
readonly DEFAULT_DIR="/var/www/controlpanel/"
cpgg_dir=""
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
            echo " $cpgg_dir directory doesn't exist. Try again"
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

set_cpgg_dir() {

    local is_exists=""
    local is_cpgg_root=""
    local is_null=""

    if [ "$cli_mode" == "false" ]; then
        # Set root CtrlPanel directory using CLI-GUI
        ## If $DEFAULT_DIR doesn't exists and $cpgg_dir var isn't specified
        if [ ! -d "$DEFAULT_DIR" ] && [ -z "$cpgg_dir" ]; then
            while true; do
                logo
                # If $is_exists and $is_cpgg_root = null or $is_null = true show "Default wasn't found"
                if [ -z "$is_exists" ] && [ -z "$is_cpgg_root" ] || [ "$is_null" == "true" ]; then
                    echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
                # If $is_exists = false show "Directory not exist"
                elif [[ $is_exists == false ]]; then
                    echo " $cpgg_dir directory doesn't exist. Try again"
                # If $is_cpgg_root = false show "Not a root CtrlPanel"
                elif [[ $is_cpgg_root == false ]]; then
                    echo " $cpgg_dir is not a root CtrlPanel directory. Try again"
                fi

                # Read specified directory
                read -rp " > " cpgg_dir

                # Set all validation vars to null
                is_null=""
                is_exists=""
                is_cpgg_root=""

                if [ "$cpgg_dir" != "" ]; then
                    if [ ! -d "$cpgg_dir" ]; then
                        is_null=""
                        is_exists="false"
                    else
                        if [ ! -f "$cpgg_dir/config/app.php" ]; then
                            is_null=""
                            is_cpgg_root="false"
                        else
                            break
                        fi
                    fi
                else
                    is_null="true"
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
}

set_cpgg_dir

# Getting curent CtrlPanel version
PANEL_VER=$(grep -oP "'version' => '\K[^']+" "${cpgg_dir:-$DEFAULT_DIR}/config/app.php")
readonly PANEL_VER
# Getting latest CtrlPanel version
PANEL_LATEST_VER=$(curl -s https://api.github.com/repos/ctrlpanel-gg/panel/tags | jq -r '.[0].name')
readonly PANEL_LATEST_VER

# Comparing current and latest versions
## 0 => Latest version is installed
## 1 => Update available
## 2 => Version above the latest one is installed
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
            echo "1" # Update needed
            return 0
        elif ((current_parts[i] > latest_parts[i])); then
            echo "2" # A newer version is installed
            return 0
        fi
    done

    echo "0" # Latest version is installed
    return 0 
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
    elif [[ $is_update_needed == 2 ]]; then
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
