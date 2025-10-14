#!/usr/bin/env bash
set -e
if [[ -n "$PANTHEON_ENVIRONMENT" ]]
  then
    echo "Current Environment: Pantheon"

    # ENSURE NODE & NVM IS INSTALLED ON PANTHEON ENV
    if which node > /dev/null
      then
        echo "node is installed, skipping..."
      else
        echo "node is not installed, installing..."
        # Download and install nvm:

        curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
        # in lieu of restarting the shell
        \. "$HOME/.nvm/nvm.sh"
        # Download and install Node.js:
        nvm install 24
        # Verify the Node.js version:
        node -v # Should print "v24.9.0".
        # Verify npm version:
        npm -v # Should print "11.6.0".
    fi
  else
    echo "Current Environment: Local"

    # REMOVE PANTHEON PLUGINS FOR LOCAL DEV

    git update-index --assume-unchanged ./web/app/mu-plugins/bedrock-autoloader.php
    git update-index --assume-unchanged ./web/app/mu-plugins/filters.php

    rm -rf ./web/app/mu-plugins/pantheon-mu-plugin
    rm -rf ./web/app/mu-plugins/bedrock-disallow-indexing
    rm -rf ./web/app/plugins/pantheon-advanced-page-cache
    rm -rf ./web/app/plugins/wp-native-php-sessions
    rm -f ./web/app/mu-plugins/bedrock-autoloader.php
    rm -f ./web/app/mu-plugins/filters.php

    # END REMOVAL OF PANTHEON PLUGINS
fi

echo "Running composer install in theme directory..."
cd ./web/app/themes/limerock
composer install
npm ci
npm run build
