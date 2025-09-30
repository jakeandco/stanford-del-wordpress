#!/bin/bash
set -e

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

echo "Running composer install in theme directory..."
cd ./web/app/themes/limerock
composer install
npm install
npm run build