# 🐳 Docker Development Environment

This development environment utilizes standalone Docker with necessary tools pre-configured.

## Included Services
- Redis
- MySQL
- Tools like phpMyAdmin

## Not Included Services (for the moment)
- Pterodactyl
- Wings

Feel free to modify the Docker Compose file to remove any services you already have.

## Setting Up the Testing Environment

1. **Manual Setup:** As mentioned in the standalone Docker guide, you will need to manually set up some components.
2. **Custom Configuration:** Modify the CtrlPanel Docker Compose configuration to use your current project as a base. If you point it to an empty folder, it will clone the repository, and you won't see your changes immediately.

For detailed setup instructions, refer to the standalone Docker documentation.

⚠ Caution: These instructions have not been finished. Therefore, there may be inaccuracies, instability, or non-functional aspects. Proceed with care.
