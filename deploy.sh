#!/bin/bash

# Local path to the files
LOCAL_PATH="/Users/pgregg/git/auto-image-to-post/"

# Remote path to the files
REMOTE_PATH="u2258-ldfsciwlr2yh@stage:/home/u2258-ldfsciwlr2yh/www/stage.d3clarity.com/public_html/wp-content/plugins/auto-image-to-post/"

# Sync local files to remote server, excluding items listed in .rsyncignore
rsync -avz --delete --exclude-from '.rsyncignore' $LOCAL_PATH $REMOTE_PATH

# Print completion message
echo "Deployment to stage completed successfully."
