#!/bin/bash

# System vars | DO NOT TOUCH!!!
readonly SCRIPT_VER="0.0.1"
readonly DEFAULT_DIR="/var/www/controlpanel/"
cpgg_dir=""
cli_mode="false"

# CtrlPanel logo
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

# Saving terminal content
save_terminal() {
    # Restoring terminal content after ^C
    trap restore_terminal SIGINT

    # Save terminal
    tput smcup
}

# Restoring terminal content after the script is completed or closed
restore_terminal() {
    tput rmcup
    exit
}

# Specifying CtrlPanel directory if it differs from the default one
set_cpgg_dir() {
    local is_exists=""
    local is_cpgg_root=""
    local is_null=""

    if [ "$cli_mode" == "false" ]; then
        if [ ! -d "$DEFAULT_DIR" ] && [ -z "$cpgg_dir" ]; then
            while true; do
                logo

                # Different message depending on the validation response
                if [ -z "$is_exists" ] && [ -z "$is_cpgg_root" ] || [ "$is_null" == "true" ]; then
                    echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
                elif [[ $is_exists == false ]]; then
                    echo " Directory $cpgg_dir doesn't exist. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
                elif [[ $is_cpgg_root == false ]]; then
                    echo " $cpgg_dir is not a root CtrlPanel directory. Specify directory where your CtrlPanel is installed (e.g. /var/www/controlpanel)"
                fi

                # Reading specified directory
                read -rep " > " cpgg_dir

                # Set all validation vars to null
                is_null=""
                is_exists=""
                is_cpgg_root=""

                # Validation of the entered directory
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
        if [ -z "$cpgg_dir" ]; then
            # If user uses --cli flag, default directory doesn't exists and user did not specify the directory using --cpgg-dir then return an error and stop script
            echo " Default directory wasn't found. Specify directory where your CtrlPanel is installed using --cpgg-dir argument"
            exit 1
        fi
    fi
}

# Getting current and latest version of CtrlPanel
get_version() {
    # Curent CtrlPanel version
    PANEL_VER=$(grep -oP "'version' => '\K[^']+" "${cpgg_dir:-$DEFAULT_DIR}/config/app.php")
    readonly PANEL_VER
    # Latest CtrlPanel version
    PANEL_LATEST_VER=$(curl -s https://api.github.com/repos/ctrlpanel-gg/panel/tags | jq -r '.[0].name')
    readonly PANEL_LATEST_VER
}

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

# Checking if an update is needed
## 0 => Latest version is installed
## 1 => Update available
## 2 => Version above the latest one is installed
update_needed_checker() {
    is_update_needed=$(version_compare "$PANEL_VER" "$PANEL_LATEST_VER")
    readonly is_update_needed
}

# Version block for logo
logo_version() {
    echo " Script    version: $SCRIPT_VER"
    echo " CtrlPanel version: $PANEL_VER"
    echo ""
}

# Message about update status for logo
logo_message() {
    if [[ $is_update_needed == 0 ]]; then
        echo " You are using the latest version! No update required."
        echo ""
    elif [[ $is_update_needed == 1 ]]; then
        echo " New version available! You can update right now by selecting \"Update\" option."
        echo ""
    elif [[ $is_update_needed == 2 ]]; then
        echo " You are using a newer version! Most likely you have a development branch installed."
        echo ""
    fi
}

# Handling arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
    --cli)
        cli_mode="true"
        shift
        ;;
    --cpgg-dir=*)
        cpgg_dir="${1#*=}"
        shift

        # Validation of specified directory
        if [ "$cpgg_dir" != "" ]; then
            if [ ! -d "$cpgg_dir" ]; then
                echo " ERROR: Directory $cpgg_dir doesn't exist."
                exit 1
            else
                if [ ! -f "$cpgg_dir/config/app.php" ]; then
                    echo " ERROR: $cpgg_dir is not a root CtrlPanel directory."
                    exit 1
                else
                    continue
                fi
            fi
        else
            echo " ERROR: Argument --cpgg-dir can't be empty!"
            exit 1
        fi
        ;;
    *)
        echo "ERROR: Argument $1 not exists. Use --help to display help menu"
        exit 1
        ;;
    esac
done

# Save terminal only if $cli_mode = false
if [ "$cli_mode" == "false" ]; then
    save_terminal
fi

# Calling function to specify a directory
set_cpgg_dir

# Moving to the CtrlPanel directory
cd "${cpgg_dir:-$DEFAULT_DIR}" || { echo " ERROR: An error occurred while trying to switch to the working directory. Please try to run the script again, if the error persists, create support forum post on CtrlPanel's Discord server!"; exit 1; }

# Main functions
if [ "$cli_mode" == "false" ]; then

    get_version
    update_needed_checker

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

    # Install dependencies menu
    menu_1() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    # Update menu
    menu_2() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    # Uninstall menu
    menu_3() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    # Info & Help menu
    menu_4() {
        logo
        echo " In dev"
        sleep 3
        main_menu
    }

    # Launch main munu
    main_menu

    # Restoring terminal after script end
    restore_terminal
else
    # Temporary function. In the future, all necessary actions in CLI mode will be performed here
    echo " CLI mode commands"
fi
