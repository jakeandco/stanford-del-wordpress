# Jake and Co WordPress Base

This is our standard WordPress installation with:

- commonly used plugins
- Composer 
- Docker (using DDEV)
- a base WP Theme
- a few common settings


## Get the code.
1. Clone this repository to your system.

## Set up your local environment.

### Update your WordPress configuration.
The Docker configuration should have added the WordPress core to your project directory. Be sure to update your .env file, following the structure defined in `.env.example` and `.env.local.example`.

### DDEV
DDEV is a much simpler way to get the project up and running.
After updating your wordpress configuration, all you should need to do to get a
docker instance going would be to run `ddev start`.

### Docker

#### Start Docker.
1. If you don't have it already, download [Docker for Mac](https://www.docker.com/docker-mac).
2. Install it.
3. `cd` to the root of this repository, and run:
    - `docker-compose up -d`.
4. Visit http://localhost:8080/ and you'll see the WordPress site running.
5. If you want to see the output of the Docker containers and monitor for errors, run:
    - `docker-compose logs -f`.

#### Restart or stop Docker.
1. If you ever want to stop and restart your containers, run:
    - `docker-compose stop`
    - `docker-compose up -d`
2. When you're done working, just run `docker-compose stop` to stop Docker for this site.


#### Import your database (optional)
If you're working on an existing site, you'll need to connect to the database container and import the database. Sequel Pro will be the easiest tool for that.

1. If you don't have it already, download [Sequel Pro](https://www.sequelpro.com/).
2. Add a new favorite with these details (update as needed for your system):
    - **Host:** 127.0.0.1
    - **Username:** root
    - **Password:** j4keandc0d3v
    - **Port:** 8081
3. Import the database dump (need to obtain this from a staging or production server - beyond the scope of these instructions).

### Start theming.

1. Run `composer install`.
2. `cd` to the theme directory (`/web/app/themes/limerock`)
3. Update the theme directory name and the stylesheet to the name of the project.
4. Run `composer install`.
5. Run `npm install`.
6. Run `npm run watch` for styles & js build.
8. If working in Wordpress, activate theme and plugins

### Happy coding!
