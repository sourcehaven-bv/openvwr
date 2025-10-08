#!/bin/bash
set -e

# Static Website Build Script
# This script is called by the CMS to build the static website using Hugo.
# It decouples the CMS from the specific static site generator implementation.

CONTENT_PATH="${1}"
BASE_URL="${2}"
THEME="${3:-rijkshuisstijl}"

# Validate required parameters
if [ -z "${CONTENT_PATH}" ] || [ -z "${BASE_URL}" ]; then
    echo "Error: Missing required parameters"
    echo "Usage: $0 <content-path> <base-url> [theme]"
    exit 1
fi

# Get the directory where this script is located (the Hugo project root)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Destination is determined by the build script, not the CMS
# This can be configured via environment variable or hardcoded
DESTINATION_PATH="${HUGO_OUTPUT_DIR:-${SCRIPT_DIR}/public}"

echo "Building static website with Hugo..."
echo "  Content path: ${CONTENT_PATH}"
echo "  Destination: ${DESTINATION_PATH}"
echo "  Base URL: ${BASE_URL}"
echo "  Theme: ${THEME}"
echo "  Hugo project: ${SCRIPT_DIR}"

# Run Hugo build from the Hugo project directory
cd "${SCRIPT_DIR}"

hugo -c "${CONTENT_PATH}" \
     -d "${DESTINATION_PATH}" \
     -b "${BASE_URL}" \
     -t "${THEME}" \
     --cleanDestinationDir

echo "Static website built successfully"

# Optional: Add post-build steps here
# Examples:
# - Optimization (minification, image compression)
# - Deployment (rsync, S3 sync, etc.)
# - Cache invalidation
# - Health checks
# - Custom hooks

# If you need to run additional commands after the build, add them here
# For example:
# if [ -n "${POST_BUILD_COMMAND}" ]; then
#     echo "Running post-build command: ${POST_BUILD_COMMAND}"
#     eval "${POST_BUILD_COMMAND}"
# fi

exit 0
