#!/bin/bash

# System vars | DO NOT TOUCH!!!
readonly SCRIPT_VER="0.0.1"
readonly DEFAULT_DIR="/var/www/controlpanel"
cpgg_dir=""
force="false"

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

    if [ -z "$cli_mode" ]; then
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

                # Remove / at the end
                cpgg_dir="${cpgg_dir%/}"

                # Set all validation vars to null
                is_null=""
                is_exists=""
                is_cpgg_root=""

                # Validation of the entered directory
                if [ "$cpgg_dir" == "" ]; then
                    is_null="true"
                elif [ ! -d "$cpgg_dir" ]; then
                    is_exists="false"
                elif [ ! -f "$cpgg_dir/config/app.php" ]; then
                    is_cpgg_root="false"
                else
                    break
                fi

            done
        fi
    else
        if [ ! -d "$DEFAULT_DIR" ] && [ -z "$cpgg_dir" ]; then
            # If user uses --cli flag, default directory doesn't exists and user did not specify the directory using --cpgg-dir then return an error and stop script
            echo " ERROR: Default directory wasn't found. Specify directory where your CtrlPanel is installed using --cpgg-dir argument"
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
    PANEL_LATEST_VER=$(
        curl -s https://api.github.com/repos/ctrlpanel-gg/panel/tags |
        sed -n 's/.*"name": "\([^"]*\)".*/\1/p' |
        head -n 1
    )
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

# Confirm dialog menu in CLI mode
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
    y | Y)
        $function
        ;;
    n | N)
        exit 0
        ;;
    *)
        echo " ERROR: Unknown choice $choice"
        echo ""
        confirm_dialog "$message_line1" "$message_line2" "$function"
        ;;
    esac
}

# === ACTIONS SECTION START ===

# Install dependencies function
install_deps() {
    local minimal="$1"

    if [[ -z "$cli_mode" ]]; then
        logo
    fi

    echo " Adding \"add-apt-repository\" command and additional dependencies"
    sudo apt -y install software-properties-common curl apt-transport-https ca-certificates gnupg

    echo " Adding PHP repository"
    LC_ALL=C.UTF-8 sudo add-apt-repository -y ppa:ondrej/php

    if [[ ! -f "/usr/share/keyrings/redis-archive-keyring.gpg" ]]; then
        echo " Adding Redis repository"
        curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
        echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/redis.list
    fi

    if [[ -z "$minimal" ]]; then
        echo " Adding MariaDB repository"
        curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
    elif [[ -n "$minimal" && "$minimal" != "true" ]]; then
        echo " ERROR: Invalid argument $minimal for install_deps function. Please, report to developers!"
        exit 1
    fi

    echo " Running \"apt update\""
    sudo apt update

    echo " Installing dependencies"
    if [[ "$minimal" ]]; then
        sudo apt -y install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} redis-server tar unzip git
    elif [[ -z "$minimal" ]]; then
        sudo apt -y install php8.3 php8.3-{cli,gd,mysql,pdo,mbstring,tokenizer,bcmath,xml,fpm,curl,zip,intl,redis} mariadb-server nginx redis-server tar unzip git
    else
        echo " ERROR: Invalid argument $minimal for install_deps function. Please, report to developers!"
        exit 1
    fi

    echo " Installing Composer"
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

    echo " Installing Composer dependencies to the CtrlPanel"
    sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
}

# Update CtrlPanel function
update() {
    if [ -z "$cli_mode" ]; then
        logo
    fi

    echo " Enabling maintenance mode"
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan down

    if ! sudo git config --global --get-all safe.directory | grep -q -w "${cpgg_dir:-$DEFAULT_DIR}"; then
        echo " Adding CtrlPanel directory to the git save.directory list"
        sudo git config --global --add safe.directory "${cpgg_dir:-$DEFAULT_DIR}"
    fi

    echo " Downloading file updates"
    sudo git stash
    sudo git pull

    echo " Installing Composer dependencies"
    sudo COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

    echo " Migrating database updates"
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan migrate --seed --force

    echo " Clearing the cache"
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan view:clear
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan config:clear

    echo " Setting permissions"
    sudo chown -R www-data:www-data "${cpgg_dir:-$DEFAULT_DIR}"
    sudo chmod -R 755 "${cpgg_dir:-$DEFAULT_DIR}"

    echo " Restarting Queue Workers"
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan queue:restart

    echo " Disabling maintenance mode"
    sudo php "${cpgg_dir:-$DEFAULT_DIR}"/artisan up
}

# === ACTIONS SECTION END ===

# Handling arguments
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

        # Validation of specified directory
        if [ "$cpgg_dir" == "" ]; then
            echo " ERROR: Argument --cpgg-dir can't be empty!"
            exit 1
        elif [ ! -d "$cpgg_dir" ]; then
            echo " ERROR: Directory $cpgg_dir doesn't exist."
            exit 1
        elif [ ! -f "$cpgg_dir/config/app.php" ]; then
            echo " ERROR: $cpgg_dir is not a root CtrlPanel directory."
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
            echo " ERROR: You can't use --install with --update argument"
            exit 1
        fi

        install="${1#*=}"
        shift

        if [[ "$install" == "" ]]; then
            echo " ERROR: Argument --install can't be empty!"
            exit 1
        elif [[ "$install" != "full" && "$install" != "min" ]]; then
            echo " ERROR: Invalid option $install for --install argument. Valid values are only full or min"
            exit 1
        fi
        ;;
    --update)
        if [[ -n "$install" ]]; then
            echo " ERROR: You can't use --update with --install argument"
            exit 1
        fi

        update="true"
        shift
        ;;
    *)
        echo " ERROR: Argument $1 not exists. Use --help to display all available arguments"
        exit 1
        ;;
    esac
done

# Save terminal only if $cli_mode = false
if [ -z "$cli_mode" ]; then
    save_terminal
fi

# Calling function to specify a directory
set_cpgg_dir

# Moving to the CtrlPanel directory
cd "${cpgg_dir:-$DEFAULT_DIR}" || {
    echo " ERROR: An error occurred while trying to switch to the working directory. Please try to run the script again, if the error persists, create support forum post on CtrlPanel's Discord server!"
    exit 1
}

# Main functions
if [ -z "$cli_mode" ]; then

    get_version
    update_needed_checker

    # Main menu
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
        1)
            menu_1
            ;;
        2)
            menu_2
            ;;
        3)
            menu_3
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
        local choice=""

        logo
        echo " This action will install all the necessary dependencies such as PHP, Redis, MariaDB and others, as well as install composer files."
        echo " You will still have to create MySQL user and configure nginx yourself."
        echo ""
        echo " Select the installation option:"
        echo " 1. Full install"
        echo " 2. Minimal install (Not include MariaDB and nginx)"
        echo " 0. Exit to main menu"
        read -rp " > " choice

        case "$choice" in
        1)
            install_deps
            ;;
        2)
            install_deps "true"
            ;;
        0)
            main_menu
            ;;
        *)
            menu_1
            ;;
        esac
    }

    # Update menu
    menu_2() {
        local choice=""

        logo
        echo " This action cannot be undone, create backup of the database before updating! It will also remove all installed themes and addons."
        echo " Do you want to continue? (Y/n)"
        read -rp " > " choice

        case "$choice" in
        y | Y)
            update
            ;;
        n | N)
            main_menu
            ;;
        *)
            menu_2
            ;;
        esac
    }

    # Info & Help menu
    menu_3() {
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
    if [[ "$install" == "full" ]]; then
        if [[ "$force" == "true" ]]; then
            install_deps
        else
            confirm_dialog "This action will install all the necessary dependencies such as PHP, Redis, MariaDB and others, as well as install composer files." "You will still have to create MySQL user and configure nginx yourself." "install_deps"
        fi
    elif [[ "$install" == "min" ]]; then
        if [[ "$force" == "true" ]]; then
            install_deps "true"
        else
            confirm_dialog "This action will install all the necessary dependencies such as PHP, Redis, Composer and others, as well as install composer files." "" "install_deps \"true\""
        fi
    elif [[ "$update" == "true" ]]; then
        if [[ "$force" == "true" ]]; then
            update
        else
            confirm_dialog "This action cannot be undone, create backup of the database before updating! It will also remove all installed themes and addons." "" "update"
        fi
    else
        echo " ERROR: You have not specified the action you want to do! Use --help to display all available arguments"
        exit 1
    fi
fi
