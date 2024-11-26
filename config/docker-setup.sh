#!/bin/bash

# Detect operating system and distribution
detect_os() {
    local os_type
    local os_dist=""

    case "$(uname -s)" in
        Linux*)
            os_type="linux"
            # Detect Linux distribution
            if [ -f "/etc/os-release" ]; then
                if grep -qi "ubuntu" /etc/os-release; then
                    os_dist="ubuntu"
                elif grep -qi "rhel\|centos\|fedora" /etc/os-release; then
                    os_dist="redhat"
                fi
            fi
            echo "${os_type}:${os_dist}"
            ;;
        Darwin*)    
            echo "macos:"
            ;;
        CYGWIN*|MINGW*|MSYS*) 
            echo "windows:"
            ;;
        *)          
            echo "unknown:"
            ;;
    esac
}

# Get user and group IDs based on OS
get_user_info() {
    local os=$1
    local dist=$2
    local user_id
    local group_id

    case "$os" in
        "linux")
            user_id=$(id -u)
            group_id=$(id -g)
            # Special handling for different distributions
            case "$dist" in
                "ubuntu")
                    # Ubuntu typically uses 33 for www-data
                    echo "Using Ubuntu-specific www-data (33:33)"
                    ;;
                "redhat")
                    # RHEL/CentOS typically uses 48 for apache
                    echo "Using RHEL-specific apache (48:48)"
                    ;;
            esac
            ;;
        "macos"|"windows")
            user_id=$(id -u)
            group_id=$(id -g)
            ;;
        *)
            echo "Unsupported operating system"
            exit 1
            ;;
    esac

    echo "$user_id:$group_id"
}

# Set up environment variables for Docker
setup_env() {
    local os=$1
    local dist=$2
    local ids=$3

    # Export user and group IDs
    export USER_ID=$(echo "$ids" | cut -d: -f1)
    export GROUP_ID=$(echo "$ids" | cut -d: -f2)

    # Create .env file for docker-compose
    cat > .env << EOF
USER_ID=$USER_ID
GROUP_ID=$GROUP_ID
LINUX_DIST=${dist}
EOF

    # Platform-specific settings
    case "$os" in
        "linux")
            echo "DOCKER_BUILDKIT=1" >> .env
            case "$dist" in
                "ubuntu")
                    echo "WEB_USER=www-data" >> .env
                    echo "WEB_GROUP=www-data" >> .env
                    ;;
                "redhat")
                    echo "WEB_USER=apache" >> .env
                    echo "WEB_GROUP=apache" >> .env
                    ;;
                *)
                    echo "WEB_USER=www-data" >> .env
                    echo "WEB_GROUP=www-data" >> .env
                    ;;
            esac
            ;;
        "macos")
            echo "DOCKER_BUILDKIT=1" >> .env
            echo "WEB_USER=www-data" >> .env
            echo "WEB_GROUP=www-data" >> .env
            ;;
        "windows")
            echo "COMPOSE_CONVERT_WINDOWS_PATHS=1" >> .env
            echo "WEB_USER=www-data" >> .env
            echo "WEB_GROUP=www-data" >> .env
            ;;
    esac
}

# Install required packages based on distribution
install_requirements() {
    local os=$1
    local dist=$2

    case "$os" in
        "linux")
            case "$dist" in
                "ubuntu")
                    echo "Installing Ubuntu requirements..."
                    sudo apt-get update
                    sudo apt-get install -y docker.io docker-compose
                    ;;
                "redhat")
                    echo "Installing RHEL requirements..."
                    sudo yum install -y docker docker-compose
                    sudo systemctl start docker
                    sudo systemctl enable docker
                    ;;
            esac
            ;;
        "macos")
            echo "Please ensure Docker Desktop for Mac is installed"
            ;;
        "windows")
            echo "Please ensure Docker Desktop for Windows is installed"
            ;;
    esac
}

# Main execution
OS_INFO=$(detect_os)
OS=$(echo $OS_INFO | cut -d: -f1)
DIST=$(echo $OS_INFO | cut -d: -f2)

echo "Detected OS: $OS"
[ -n "$DIST" ] && echo "Detected Distribution: $DIST"

# Install requirements
install_requirements "$OS" "$DIST"

USER_INFO=$(get_user_info "$OS" "$DIST")
echo "Using user:group = $USER_INFO"

setup_env "$OS" "$DIST" "$USER_INFO"

# Build and run containers
echo "Building Docker containers..."
docker-compose down --volumes
docker-compose build --no-cache

echo "Starting Docker containers..."
docker-compose up -d

echo "Setup complete!"
